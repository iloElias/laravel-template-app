# Laravel API Template

API RESTful em Laravel 12 para plataformas de aluguel de espaços esportivos — modelo similar ao Airbnb, com suporte a equipamentos, pagamentos via Stripe (cartão + Pix), repasses automáticos para anfitriões via Stripe Connect e assinaturas mensais.

---

## Índice

- [Visão Geral](#visão-geral)
- [Arquitetura de Serviços](#arquitetura-de-serviços)
- [Pré-requisitos](#pré-requisitos)
- [Início Rápido (Docker)](#início-rápido-docker)
- [Variáveis de Ambiente](#variáveis-de-ambiente)
- [Autenticação e 2FA](#autenticação-e-2fa)
- [Pagamentos com Stripe](#pagamentos-com-stripe)
- [Object Storage (RustFS)](#object-storage-rustfs)
- [Filas e Workers](#filas-e-workers)
- [Rotas Principais](#rotas-principais)
- [Desenvolvimento Local](#desenvolvimento-local)
- [CI/CD e Monitoramento](#cicd-e-monitoramento)
- [🔧 Troubleshooting](#-troubleshooting)
- [Licenças](#licenças-de-terceiros-relevantes)

**📚 Documentação adicional:**

- [STRIPE.md](STRIPE.md) — Integração completa Stripe (pagamentos, Connect, webhooks)
- [CLICKHOUSE.md](CLICKHOUSE.md) — Configuração de analytics e proxy reverso

---

## Visão Geral

| Camada         | Tecnologia                                   |
| -------------- | -------------------------------------------- |
| Framework      | Laravel 12 / PHP 8.2                         |
| Banco de dados | PostgreSQL 16 + PgBouncer (pool de conexões) |
| Cache          | Redis 7 (phpredis)                           |
| Fila           | RabbitMQ 3                                   |
| Analytics      | ClickHouse (HTTP API porta 8123)             |
| Object storage | **RustFS** (S3-compatible, Apache 2.0)       |
| WebSocket      | Laravel Reverb                               |
| Pagamentos     | Stripe (cartão + Pix + Connect Marketplace)  |
| Monitoramento  | Sentry                                       |
| Auth social    | Google OAuth 2.0                             |

---

## Arquitetura de Serviços

```
┌─────────────────────────────────────────────────────────────────┐
│ Cliente (iOS / Android / Web)                                   │
└──────────────────────────┬──────────────────────────────────────┘
                           │ HTTPS / WSS
┌──────────────────────────▼──────────────────────────────────────┐
│ Nginx (reverse proxy)                                           │
└──────────────────────────┬──────────────────────────────────────┘
                           │
┌──────────────────────────▼──────────────────────────────────────┐
│ PHP-FPM (Laravel 12)                                            │
│  ├─ HTTP API (routes/api.php)                                   │
│  ├─ WebSocket server (Reverb — porta 6001)                      │
│  └─ Queue workers (default | payments | analytics)              │
└─┬──────┬──────┬──────┬──────┬──────┬──────────────────────────┘
  │      │      │      │      │      │
  ▼      ▼      ▼      ▼      ▼      ▼
 PG    PgBnc  Redis  AMQP  Click  RustFS
```

### Fluxo de pagamento (booking)

```
Cliente paga → Stripe PaymentIntent (cartão/Pix)
    └─ application_fee_amount → plataforma retém taxa
    └─ transfer_data.destination → repasse ao host (Stripe Connect Express)

Stripe dispara webhook → POST /api/webhook/stripe
    └─ ProcessStripeWebhook (fila: payments)
        └─ payment_intent.succeeded → marca booking como pago
        └─ customer.subscription.* → sincroniza assinatura do host
        └─ account.updated → atualiza status de onboarding do host
```

---

## Pré-requisitos

- Docker ≥ 24 e Docker Compose ≥ 2.20
- PHP 8.2+ e Composer (apenas para desenvolvimento local sem Docker)

---

## Início Rápido (Docker)

```bash
# 1. Copiar variáveis de ambiente
cp .env.docker .env

# 2. Preencher as credenciais obrigatórias no .env (veja seção abaixo)

# 3. Subir todos os serviços
docker compose up -d

# 4. Criar o bucket no RustFS (primeira vez)
#    Acesse http://localhost:9101 → login → criar bucket "laravel"
```

Serviços disponíveis após `docker compose up`:

| Serviço             | URL local              |
| ------------------- | ---------------------- |
| API (Nginx)         | http://localhost       |
| RabbitMQ Management | http://localhost:15672 |
| ClickHouse HTTP     | http://localhost:8123  |
| RustFS S3 API       | http://localhost:9100  |
| RustFS Console      | http://localhost:9101  |
| Reverb WebSocket    | ws://localhost:6001    |

---

## Variáveis de Ambiente

### Banco de Dados

| Variável         | Descrição                              | Onde obter                                          |
| ---------------- | -------------------------------------- | --------------------------------------------------- |
| `DB_HOST`        | Host do PgBouncer                      | `template-pgbouncer` (Docker) / `localhost` (local) |
| `DB_PORT`        | Porta do PgBouncer                     | `5432` (Docker) / `6432` (local com Docker exposto) |
| `DB_DIRECT_HOST` | Host direto do PostgreSQL (migrations) | `template-postgres` (Docker) / `localhost` (local)  |
| `DB_DIRECT_PORT` | Porta direta                           | `5432`                                              |
| `DB_DATABASE`    | Nome do banco                          | `laravel`                                           |
| `DB_USERNAME`    | Usuário                                | `postgres`                                          |
| `DB_PASSWORD`    | Senha                                  | Definir localmente                                  |

> **Nota sobre PgBouncer:** O `POOL_MODE=transaction` é incompatível com advisory locks do PostgreSQL. Por isso, as migrations rodam via `pgsql_direct` (conexão que bypassa o PgBouncer). A configuração `PDO::ATTR_EMULATE_PREPARES=true` na conexão `pgsql` garante compatibilidade com o pool.

### Cache (Redis)

| Variável         | Descrição                                       |
| ---------------- | ----------------------------------------------- |
| `REDIS_HOST`     | `template-redis` (Docker) / `127.0.0.1` (local) |
| `REDIS_PORT`     | `6379`                                          |
| `REDIS_PASSWORD` | Senha (pode ser vazio em dev)                   |

### Fila (RabbitMQ)

| Variável            | Descrição                                          |
| ------------------- | -------------------------------------------------- |
| `RABBITMQ_HOST`     | `template-rabbitmq` (Docker) / `localhost` (local) |
| `RABBITMQ_PORT`     | `5672`                                             |
| `RABBITMQ_USER`     | `admin`                                            |
| `RABBITMQ_PASSWORD` | Definir localmente                                 |

Filas utilizadas:

- `default` — jobs gerais
- `payments` — processamento de webhooks Stripe (5 tentativas, backoff 30s)
- `analytics` — registro de eventos no ClickHouse

### Analytics (ClickHouse)

| Variável              | Descrição                                               |
| --------------------- | ------------------------------------------------------- |
| `CLICKHOUSE_PROTOCOL` | `http` (dev) / `https` (prod com proxy reverso)         |
| `CLICKHOUSE_HOST`     | `template-clickhouse` (Docker) / `olap.mesf.app` (prod) |
| `CLICKHOUSE_PORT`     | `8123` (HTTP API) / `443` (HTTPS via proxy)             |
| `CLICKHOUSE_DATABASE` | `default`                                               |
| `CLICKHOUSE_USERNAME` | `default`                                               |
| `CLICKHOUSE_PASSWORD` | Vazio por padrão                                        |

**⚠️ Configuração de DNS/Proxy:** Para usar `clickhouse.mesf.app` em produção, configure o proxy reverso (Nginx/Cloudflare/Dokploy) para apontar para a **porta 8123** (HTTP API), **NÃO 9000** (Native TCP). Veja [CLICKHOUSE.md](CLICKHOUSE.md) para detalhes completos.

**Criação de tabelas:** Execute os arquivos SQL em `database/clickhouse/` manualmente via HTTP API ou interface web. Veja [CLICKHOUSE.md](CLICKHOUSE.md) para instruções detalhadas.

### Object Storage (RustFS)

| Variável                      | Descrição                                                                |
| ----------------------------- | ------------------------------------------------------------------------ |
| `AWS_ACCESS_KEY_ID`           | Chave de acesso — mesmo valor de `RUSTFS_ACCESS_KEY` no container        |
| `AWS_SECRET_ACCESS_KEY`       | Chave secreta — mesmo valor de `RUSTFS_SECRET_KEY` no container          |
| `AWS_BUCKET`                  | Nome do bucket (crie via console em http://localhost:9101)               |
| `AWS_ENDPOINT`                | `http://template-rustfs:9000` (Docker) / `http://localhost:9100` (local) |
| `AWS_USE_PATH_STYLE_ENDPOINT` | `true` (obrigatório para S3-compatible self-hosted)                      |

> **Por que RustFS e não MinIO?** MinIO é licenciado sob AGPL v3, que exige abertura do código-fonte de qualquer aplicação que o distribua. O RustFS usa Apache 2.0 — sem restrições para uso comercial.

### WebSocket (Reverb)

| Variável            | Descrição        |
| ------------------- | ---------------- |
| `REVERB_APP_ID`     | ID da app Reverb |
| `REVERB_APP_KEY`    | Chave pública    |
| `REVERB_APP_SECRET` | Chave secreta    |
| `REVERB_HOST`       | `localhost`      |
| `REVERB_PORT`       | `6001`           |

### Monitoramento (Sentry)

| Variável     | Onde obter                                                              |
| ------------ | ----------------------------------------------------------------------- |
| `SENTRY_DSN` | [sentry.io](https://sentry.io) → Projeto → Settings → Client Keys (DSN) |

### IP real do cliente (Cloudflare)

Para registrar o IP real do cliente atras da Cloudflare, mantenha as regras abaixo:

1. Manter dominio com proxy da Cloudflare habilitado.
2. Restringir acesso ao origin para aceitar trafego apenas dos ranges de IP da Cloudflare (firewall/security group).
3. Nao expor origin publicamente. Se exposto, headers como `CF-Connecting-IP` podem ser forjados por cliente direto.

Configuracao:

- `TRUSTED_PROXY_CIDRS` (opcional): lista separada por virgula com CIDRs confiaveis.
- Sem `TRUSTED_PROXY_CIDRS`, o sistema usa os ranges oficiais da Cloudflare por padrao (definidos em `config/access.php`).

Regra de resolucao de IP implementada:

1. Se `REMOTE_ADDR` for proxy confiavel, usar `CF-Connecting-IP` valido.
2. Se ausente, usar primeiro IP valido em `X-Forwarded-For`.
3. Fallback: `REMOTE_ADDR`.
4. Valores invalidos sao descartados, com normalizacao IPv4/IPv6.

Campos de log em `system.access_request_log`:

- `client_ip`: IP real resolvido pelas regras acima.
- `proxy_ip`: IP que conectou no origin.
- `cf_ray`: identificador da Cloudflare para correlacao.
- `method`, `path`, `user_agent`, `created_at`.

Teste rapido:

1. Acesse pelo site (rede residencial/4G) e valide `client_ip` com seu IP publico real.
2. Envie request direta ao origin com `CF-Connecting-IP` falso; backend deve ignorar e usar `REMOTE_ADDR`.
3. Confirme `proxy_ip` com IP de borda Cloudflare e correlacione incidente por `cf_ray` + `client_ip`.

### Stripe

| Variável                                  | Descrição                                     | Onde obter                                                              |
| ----------------------------------------- | --------------------------------------------- | ----------------------------------------------------------------------- |
| `STRIPE_KEY`                              | Chave pública (`pk_live_*` / `pk_test_*`)     | Dashboard → Developers → API Keys                                       |
| `STRIPE_SECRET`                           | Chave privada (`sk_live_*` / `sk_test_*`)     | Dashboard → Developers → API Keys                                       |
| `STRIPE_WEBHOOK_SECRET`                   | Segredo do webhook da plataforma              | Dashboard → Developers → Webhooks → endpoint `/api/webhook/stripe`      |
| `STRIPE_CONNECT_WEBHOOK_SECRET`           | Segredo do webhook Connect                    | Dashboard → Connect → Webhooks → endpoint `/api/webhook/stripe/connect` |
| `STRIPE_PLAN_BASIC_PRICE_ID`              | ID do preço do plano básico mensal            | Dashboard → Products → criar produto recorrente                         |
| `STRIPE_PLAN_PREMIUM_PRICE_ID`            | ID do preço do plano premium mensal           | Dashboard → Products → criar produto recorrente                         |
| `STRIPE_DEFAULT_PLATFORM_FEE_PERCENT`     | Taxa % por booking (modelo porcentagem)       | `.env` — padrão `10`                                                    |
| `STRIPE_SUBSCRIPTION_BOOKING_FEE_PERCENT` | Taxa % por booking quando host tem assinatura | `.env` — padrão `0`                                                     |

---

## Autenticação e 2FA

### Fluxo de login

```
POST /api/auth/sign-in
│
├─ email_verified = false  → código 2FA obrigatório (primeiro acesso)
├─ email_two_factor_auth = true  → código 2FA obrigatório (configuração do usuário)
└─ demais casos  → sessão autenticada imediatamente (sem código)
```

### Google OAuth

```
POST /api/auth/google-auth
│
├─ Google confirma email_verified = true  → sessão autenticada (sem código)
└─ Google NÃO confirma verificação  → código 2FA enviado por e-mail
```

### Sessões e tokens

- Cada login cria uma `Session` com `auth_code_id` se 2FA for necessário
- Após verificação do código, `Session.authenticated = true` é definido
- Token Bearer é gerado via `TokenFactory` e retornado ao cliente

---

## Pagamentos com Stripe

A plataforma utiliza Stripe como gateway de pagamento, com suporte a:

- ✅ **Cartão de crédito** (processamento imediato)
- ✅ **Pix** (QR code com expiração de 24h)
- ✅ **Stripe Connect** (repasses automáticos para hosts)
- ✅ **Assinaturas mensais** (modelo alternativo de cobrança)

### Modelos de cobrança

| Modelo          | Descrição                                                     | Taxa por booking   |
| --------------- | ------------------------------------------------------------- | ------------------ |
| **Porcentagem** | Host não paga mensalidade; plataforma retém % de cada booking | 10% (configurável) |
| **Assinatura**  | Host paga mensalidade fixa; taxa reduzida ou zero             | 0% (configurável)  |

### Arquivos de serviço

| Arquivo                                              | Responsabilidade                           |
| ---------------------------------------------------- | ------------------------------------------ |
| `app/Services/Payment/StripeService.php`             | Customer, PaymentIntent, Connect, webhooks |
| `app/Services/Payment/SubscriptionService.php`       | Assinaturas mensais, Billing Portal        |
| `app/Services/Payment/MarketplaceService.php`        | Booking com taxa de plataforma             |
| `app/Http/Controllers/Payment/WebhookController.php` | Recebe webhooks Stripe                     |
| `app/Jobs/Payment/ProcessStripeWebhook.php`          | Processa eventos assíncronos               |

### Documentação completa

**📖 Para informações detalhadas sobre configuração, onboarding de hosts, fluxos de pagamento, webhooks e troubleshooting, consulte:**

**→ [STRIPE.md](STRIPE.md)**

---

## Object Storage (RustFS)

O RustFS é um servidor de armazenamento de objetos 100% compatível com a API S3 da AWS, desenvolvido em Rust sob licença **Apache 2.0**.

### Configuração

A aplicação usa o driver `s3` do Laravel Flysystem. Nenhuma mudança de código é necessária — apenas as variáveis de ambiente apontam para o RustFS em vez da AWS:

```env
FILESYSTEM_DISK=s3
AWS_ENDPOINT=http://localhost:9100   # local (Docker expõe porta 9100)
AWS_USE_PATH_STYLE_ENDPOINT=true     # obrigatório para self-hosted S3
```

### Console web

Após `docker compose up`, acesse **http://localhost:9101**:

- Usuário: `AWS_ACCESS_KEY_ID` definido no `.env`
- Senha: `AWS_SECRET_ACCESS_KEY` definido no `.env`
- Crie o bucket com o nome definido em `AWS_BUCKET` (padrão: `laravel`)

### Produção (multi-volume)

Em produção, remova `RUSTFS_UNSAFE_BYPASS_DISK_CHECK=true` do `docker-compose.yml` e configure múltiplos volumes conforme a [documentação oficial](https://docs.rustfs.com/).

---

## Filas e Workers

### Queues configuradas

| Fila        | Responsável                  | Jobs                                                                  |
| ----------- | ---------------------------- | --------------------------------------------------------------------- |
| `default`   | Workers gerais               | `SendMail`, `SendSms`, `ValidateGooglePlacesJob`, `LogRequestHistory` |
| `payments`  | Worker dedicado a pagamentos | `ProcessStripeWebhook`                                                |
| `analytics` | Worker de analytics          | `LogErrorEvent`                                                       |

### Scripts auxiliares

```bash
script/queue.sh      # Inicia workers
script/migration.sh  # Executa migrations PostgreSQL via pgsql_direct
script/schedule.sh   # Inicia o scheduler
script/serve.sh      # Inicia PHP dev server + Reverb
script/cache.sh      # Limpa caches
script/seed.sh       # Executa seeders (condicional por ambiente)
```

**Deploy automatizado (`ci.sh`):**

```bash
# Executado automaticamente no deploy (Dokploy/CI)
1. cache.sh       → Limpa caches de configuração/rotas/views
2. migration.sh   → Executa migrations no PostgreSQL
3. seed.sh        → Executa seeders (condicional por APP_ENV)
4. queue.sh       → Inicia workers em background
5. serve.sh       → Inicia servidor PHP + Reverb
```

**Seeders condicionais:**

- `local`/`development` → Executa seeders de desenvolvimento (usuários de teste)
- `staging`/`production` → Não executa seeders (banco já populado)

---

## Rotas Principais

### Autenticação (`/api/auth`)

| Método | Rota                       | Descrição                                |
| ------ | -------------------------- | ---------------------------------------- |
| `GET`  | `/api/auth/fingerprint`    | Gera fingerprint do dispositivo          |
| `POST` | `/api/auth/sign-in`        | Login com e-mail e senha                 |
| `POST` | `/api/auth/sign-up`        | Cadastro de novo usuário                 |
| `POST` | `/api/auth/reset-password` | Reset de senha                           |
| `POST` | `/api/auth/google-auth`    | Login com Google (token ID)              |
| `POST` | `/api/auth/google-auth/v2` | Login com Google (token de acesso)       |
| `GET`  | `/api/auth`                | Verificar código 2FA e autenticar sessão |
| `GET`  | `/api/auth/resend-code`    | Reenviar código 2FA                      |

### Webhooks Stripe (`/api/webhook`)

> Rotas sem autenticação — verificadas por assinatura HMAC (`Stripe-Signature`).

| Método | Rota                          | Descrição                                                  |
| ------ | ----------------------------- | ---------------------------------------------------------- |
| `POST` | `/api/webhook/stripe`         | Eventos da conta principal (assinaturas, pagamentos)       |
| `POST` | `/api/webhook/stripe/connect` | Eventos das contas Connect (onboarding de hosts, repasses) |

### Health Check (`/api/health`)

| Método | Rota                   | Descrição                    |
| ------ | ---------------------- | ---------------------------- |
| `GET`  | `/api/health/api`      | Status da aplicação          |
| `GET`  | `/api/health/database` | Conectividade com PostgreSQL |
| `GET`  | `/api/health/cache`    | Conectividade com Redis      |
| `GET`  | `/api/health/queue`    | Conectividade com RabbitMQ   |
| `GET`  | `/api/health/redis`    | Ping Redis                   |

---

## Desenvolvimento Local

```bash
# Dependências PHP
composer install

# Dependências JS (Vite)
npm install

# Variáveis de ambiente
cp .env.example .env
php artisan key:generate

# Subir infra via Docker (sem a aplicação PHP)
docker compose up -d template-postgres template-pgbouncer template-redis \
                     template-rabbitmq template-clickhouse template-rustfs

# Migrations
bash script/migration.sh

# Servidor de desenvolvimento + Reverb
bash script/serve.sh

# Workers
bash script/queue.sh
```

### Testando webhooks Stripe localmente

Use o [Stripe CLI](https://stripe.com/docs/stripe-cli):

```bash
# Instalar Stripe CLI
# https://docs.stripe.com/stripe-cli#install

# Redirecionar webhooks para localhost
stripe listen --forward-to localhost:8000/api/webhook/stripe

# Em outro terminal, disparar eventos de teste
stripe trigger payment_intent.succeeded
stripe trigger customer.subscription.updated
```

---

## CI/CD e Monitoramento

### Workflows GitHub Actions

| Workflow         | Quando roda             | Bloqueia deploy? | Descrição                                                        |
| ---------------- | ----------------------- | ---------------- | ---------------------------------------------------------------- |
| **CI Laravel**   | Push/PR na `master`     | ✅ Sim           | Testes unitários, feature e análise estática (PHPStan)           |
| **Health Check** | A cada 6 horas / Manual | ❌ Não           | Testa conectividade com PostgreSQL, Redis, RabbitMQ e ClickHouse |

### Health Check não-bloqueante

O workflow de Health Check testa as conexões com os bancos de dados mas **nunca bloqueia o deploy**:

```yaml
# .github/workflows/health-check.yml
continue-on-error: true # Nunca falha o workflow
```

**Quando roda:**

- 🕐 Automaticamente a cada 6 horas
- 🚀 Após deploy bem-sucedido
- 🔧 Manualmente via GitHub Actions

**O que testa:**

- PostgreSQL → conexão PDO
- Redis → comando PING
- RabbitMQ → estabelecimento de conexão AMQP
- ClickHouse → endpoint `/ping`

**Execução manual:**

```bash
# Via GitHub Actions UI
Actions → Health Check → Run workflow

# Ou via gh CLI
gh workflow run health-check.yml
```

**Resultados:**

- ✅ Todos os serviços saudáveis → log verde
- ⚠️ Um ou mais serviços com falha → log de aviso (não bloqueia)

### Rotas de health check

Você também pode testar manualmente via HTTP:

```bash
# API status
curl http://localhost/api/health/api

# PostgreSQL
curl http://localhost/api/health/database

# Redis
curl http://localhost/api/health/redis

# RabbitMQ
curl http://localhost/api/health/queue

# Cache (Redis)
curl http://localhost/api/health/cache
```

---

## 🔧 Troubleshooting

### Problemas comuns e soluções

Para guias detalhados de resolução de problemas, consulte:

- **[TROUBLESHOOTING.md](docs/TROUBLESHOOTING.md)** — Diagnóstico e correção de erros

**Problemas frequentes:**

- ❌ **"Class 'Redis' not found"**  
  → Extensão phpredis não instalada ou cache corrompido  
  → Ver [TROUBLESHOOTING.md - Redis](docs/TROUBLESHOOTING.md#%EF%B8%8F-erro-class-redis-not-found)

- ❌ **Erro de conexão com PgBouncer**  
  → Usar nome do container (`pgbouncer`) não DNS externo  
  → Verificar AUTH_TYPE=scram-sha-256 no PgBouncer

- ❌ **ClickHouse "wrong password type"**  
  → Porta incorreta: usar 8123 (HTTP) não 9000 (Native TCP)  
  → Ver [CLICKHOUSE.md](docs/CLICKHOUSE.md)

- ❌ **Queue workers não processam jobs**  
  → Verificar se script/queue.sh está rodando  
  → Checar logs: `docker logs <container> | grep queue`

**Scripts de deploy:**

```bash
# Ordem de execução no ci.sh
./script/cache.sh       # Limpa cache antigo + cria novo
./script/migration.sh   # Migrações PostgreSQL
./script/seed.sh        # Seeders (condicional por ambiente)
./script/queue.sh       # Workers RabbitMQ (background)
./script/serve.sh       # Laravel + Reverb
```

---

## Licenças de terceiros relevantes

| Dependência       | Licença            | Observação                |
| ----------------- | ------------------ | ------------------------- |
| Laravel           | MIT                |                           |
| stripe/stripe-php | MIT                |                           |
| RustFS            | **Apache 2.0**     | Substitui MinIO (AGPL v3) |
| Redis             | BSD 3-Clause       |                           |
| RabbitMQ          | MPL 2.0            |                           |
| ClickHouse        | Apache 2.0         |                           |
| PostgreSQL        | PostgreSQL License | permissiva                |
