# Serviços da API

Mapeamento de todos os serviços integrados, pendentes e de infraestrutura necessários para o funcionamento da API.

---

## Serviços com container no docker-compose

Os serviços abaixo **precisam de uma entrada própria no `docker-compose.yml`** para rodar localmente.

| Serviço    | Porta padrão | Variáveis de ambiente relevantes                                                                          | Status                        |
| ---------- | ------------ | --------------------------------------------------------------------------------------------------------- | ----------------------------- |
| Redis      | 6379         | `REDIS_HOST`, `REDIS_PORT`, `REDIS_PASSWORD`                                                              | ⚠️ Configurado, sem container |
| ClickHouse | 8123         | `CLICKHOUSE_HOST`, `CLICKHOUSE_PORT`, `CLICKHOUSE_DATABASE`, `CLICKHOUSE_USERNAME`, `CLICKHOUSE_PASSWORD` | ❌ Não implementado           |
| PgBouncer  | 5432         | Reusa `DB_HOST`, `DB_PORT` (aponta para o PgBouncer)                                                      | ❌ Não implementado           |

> **Redis** é bloqueante: sem ele, cache, rate limiting e locks de scheduler não funcionam.
> **ClickHouse** pode rodar como serviço separado ou ser um serviço gerenciado (ClickHouse Cloud).
> **PgBouncer** é opcional em desenvolvimento, mas necessário em produção com múltiplos workers de fila.

---

## Serviços sem container (externos ou no processo PHP)

| Serviço           | Tipo               | Status              | Observação                                                                                                                                                              |
| ----------------- | ------------------ | ------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| MinIO / S3        | SaaS / self-hosted | ✅ Completo         | `FILESYSTEM_DISK=s3` ativo, `FileFactory` e `PictureService` integrados                                                                                                 |
| RabbitMQ          | Externo            | ✅ Completo         | `QUEUE_CONNECTION=rabbitmq`, todos os jobs usam `ShouldQueue`                                                                                                           |
| Vonage (SMS)      | SaaS               | ✅ Completo         | `SendSms` job enfileirado, flag `SMS_SERVICE_ENABLED`                                                                                                                   |
| E-mail (SMTP)     | SaaS               | ✅ Completo         | `SendMail` job disparado nos fluxos de auth                                                                                                                             |
| Google APIs       | SaaS               | ✅ Completo         | Places, Distance Matrix e autenticação via ID Token / Access Token                                                                                                      |
| MercadoPago       | SaaS               | ⚠️ Parcial          | SDK instalado mas não usado; apenas consulta HTTP raw de status de pagamento; sem webhook nem criação de pagamentos PIX                                                 |
| Laravel Reverb    | Processo PHP       | ❌ Não implementado | Pacote instalado, `reverb:start` comentado no `serve.sh`; `ChatService` referenciado em `UserService` e `GoogleAuthService` mas inexistente; `BROADCAST_CONNECTION=log` |
| Sentry            | SaaS               | ❌ Não implementado | `SENTRY_DSN` adicionado ao `.env`; pacote `sentry/sentry-laravel` não instalado                                                                                         |
| Rate Limiting     | Middleware Laravel | ❌ Não implementado | Nenhuma rota usa `throttle`; depende do Redis estar ativo                                                                                                               |
| Laravel Telescope | Processo PHP       | ❌ Não implementado | Pacote não instalado; útil apenas em desenvolvimento                                                                                                                    |

---

## Status de implementação — ClickHouse

A arquitetura está pronta:

- `ClickhouseService` com métodos `select()` e `insert()` via HTTP
- `LogRequestHistory` job enfileira em `analytics` e insere em `request_history`
- `LogErrorEvent` job enfileira em `analytics` e insere em `error_log`
- Configuração em `config/services.php` e variáveis no `.env`

**O que falta:** container do ClickHouse rodando + tabelas criadas (não há migrations para ClickHouse, apenas para o Postgres).

---

## Status de implementação — Reverb

O pacote `laravel/reverb` está instalado e o `.env` tem as variáveis configuradas.

**O que falta:**

1. Criar `App\Services\Chat\ChatService` (referenciado em `UserService` e `GoogleAuthService`)
2. Criar eventos que implementem `ShouldBroadcast`
3. Ativar `BROADCAST_CONNECTION=reverb` no `.env`
4. Descomentar `php artisan reverb:start` no `script/serve.sh`

---

## Pendências de segurança

- **Rate Limiting:** adicionar `throttle` nas rotas públicas (`/auth/sign-in`, `/auth/sign-up`, `/auth/reset-password`)
- **Sentry:** instalar `sentry/sentry-laravel` e configurar o `SENTRY_DSN`
- **Health checks:** `HealthCheckController` não testa conexão real com Redis, RabbitMQ ou S3 — apenas retorna o nome do driver configurado
