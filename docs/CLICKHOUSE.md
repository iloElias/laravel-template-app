# ClickHouse — Analytics Database

Documentação completa sobre a integração com ClickHouse para analytics de requests e logs de erro.

---

## 📋 Visão Geral

| Item                   | Valor                                      |
| ---------------------- | ------------------------------------------ |
| **Versão**             | latest (compatível com 23.x+)              |
| **Protocolo HTTP API** | Porta **8123** (não 9000!)                 |
| **Protocolo Native**   | Porta 9000 (apenas para clickhouse-client) |
| **Engine**             | MergeTree                                  |
| **Compressão**         | LZ4 (padrão)                               |
| **DNS API**            | olap.mesf.app (após configuração do proxy) |
| **DNS Interface**      | clickhouse.mesf.app (web UI)               |

---

## 🔌 Portas do ClickHouse

| Porta    | Protocolo  | Uso                                                          |
| -------- | ---------- | ------------------------------------------------------------ |
| **8123** | HTTP API   | ✅ **Usar na aplicação Laravel** (logs, queries, migrations) |
| 9000     | Native TCP | ❌ Apenas para `clickhouse-client` CLI (não HTTP)            |
| 8443     | HTTPS API  | ✅ Opcional se tiver SSL configurado                         |
| 9440     | Native TLS | ❌ Apenas para cliente nativo com TLS                        |

**⚠️ Problema comum:** Apontar proxy reverso para porta **9000** resulta no erro:

```
Port 9000 is for clickhouse-client program
You must use port 8123 for HTTP.
```

---

## 🛠️ Configuração do Proxy Reverso

### Nginx (Recomendado)

```nginx
# /etc/nginx/sites-available/clickhouse-api
upstream clickhouse_http {
    server 127.0.0.1:8123;  # ← HTTP API (NÃO 9000!)
    keepalive 64;
}

server {
    listen 80;
    server_name olap.mesf.app;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name olap.mesf.app;

    # Certificados SSL (Let's Encrypt)
    ssl_certificate /etc/letsencrypt/live/olap.mesf.app/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/olap.mesf.app/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;

    # Logs
    access_log /var/log/nginx/clickhouse-access.log;
    error_log /var/log/nginx/clickhouse-error.log;

    # Proxy para ClickHouse HTTP API
    location / {
        proxy_pass http://clickhouse_http;
        proxy_http_version 1.1;

        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;

        # ClickHouse pode ter queries longas
        proxy_read_timeout 300s;
        proxy_connect_timeout 10s;

        # Suporte a Keep-Alive
        proxy_set_header Connection "";
    }

    # Health check endpoint
    location /ping {
        proxy_pass http://clickhouse_http/ping;
        access_log off;
    }
}
```

**Habilitar configuração:**

```bash
sudo ln -s /etc/nginx/sites-available/clickhouse-api /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### Cloudflare Tunnel

```yaml
# /etc/cloudflared/config.yml
tunnel: <TUNNEL_ID>
credentials-file: /etc/cloudflared/<TUNNEL_ID>.json

ingress:
    # API Analytics (aplicação Laravel)
    - hostname: olap.mesf.app
      service: http://localhost:8123 # ← HTTP API
      originRequest:
          connectTimeout: 10s
          noTLSVerify: false

    # Interface Web (usuários)
    - hostname: clickhouse.mesf.app
      service: http://localhost:8123

    # Fallback (obrigatório)
    - service: http_status:404
```

**Aplicar configuração:**

```bash
sudo cloudflared tunnel route dns <TUNNEL_NAME> olap.mesf.app
sudo systemctl restart cloudflared
```

---

## ✅ Validação da Configuração

### 1. Testar endpoint de health check

```bash
# Deve retornar "Ok." (HTTP 200)
curl https://olap.mesf.app/ping

# Output esperado:
Ok.
```

### 2. Executar query simples

```bash
# SELECT 1 (teste básico)
curl 'https://olap.mesf.app/?query=SELECT%201'

# Output esperado:
1
```

### 3. Testar com autenticação (se configurada)

```bash
curl -u default:senha123 'https://olap.mesf.app/?query=SELECT%20version()'

# Output esperado (exemplo):
23.11.1.1
```

### 4. Validar tabelas criadas

```bash
curl -u default: 'https://olap.mesf.app/' -d "
  SELECT
    database,
    name as table_name,
    engine,
    total_rows
  FROM system.tables
  WHERE database = 'default'
  FORMAT JSONEachRow
"

# Output esperado:
{"database":"default","table_name":"request_history","engine":"MergeTree","total_rows":"0"}
{"database":"default","table_name":"error_log","engine":"MergeTree","total_rows":"0"}
```

---

## 🔧 Configuração Laravel

### Variáveis de ambiente

**Produção (.env):**

```bash
# Usar DNS com HTTPS (após configurar proxy)
CLICKHOUSE_PROTOCOL=https
CLICKHOUSE_HOST=olap.mesf.app
CLICKHOUSE_PORT=443
CLICKHOUSE_DATABASE=default
CLICKHOUSE_USERNAME=default
CLICKHOUSE_PASSWORD=  # vazio se não configurou senha
```

**Docker (.env.docker):**

```bash
# Comunicação direta via rede Docker
CLICKHOUSE_PROTOCOL=http
CLICKHOUSE_HOST=template-clickhouse
CLICKHOUSE_PORT=8123
CLICKHOUSE_DATABASE=default
CLICKHOUSE_USERNAME=default
CLICKHOUSE_PASSWORD=
```

### Código Laravel

**config/services.php:**

```php
'clickhouse' => [
    'protocol' => env('CLICKHOUSE_PROTOCOL', 'http'),
    'host' => env('CLICKHOUSE_HOST', '127.0.0.1'),
    'port' => env('CLICKHOUSE_PORT', 8123),
    'database' => env('CLICKHOUSE_DATABASE', 'default'),
    'username' => env('CLICKHOUSE_USERNAME', 'default'),
    'password' => env('CLICKHOUSE_PASSWORD', ''),
],
```

**app/Console/Commands/ClickHouseMigrate.php:**

```php
$config = config('services.clickhouse');
$baseUrl = sprintf(
    '%s://%s:%s',
    $config['protocol'],
    $config['host'],
    $config['port']
);

$response = Http::withBasicAuth($config['username'], $config['password'])
    ->post($baseUrl, ['query' => $sql]);
```

---

## 📊 Estrutura das Tabelas

### request_history (analytics de requests)

```sql
CREATE TABLE IF NOT EXISTS request_history (
    session_id UInt64,
    route String,
    method String,
    payload Nullable(String),
    created_at DateTime
) ENGINE = MergeTree()
ORDER BY (created_at, session_id);
```

**Uso:**

```php
// app/Jobs/LogRequestHistory.php
dispatch(new LogRequestHistory([
    'session_id' => $session->id,
    'route' => $request->path(),
    'method' => $request->method(),
    'payload' => json_encode($request->all()),
    'created_at' => now()->format('Y-m-d H:i:s'),
]));
```

### error_log (logs de exceções)

```sql
CREATE TABLE IF NOT EXISTS error_log (
    url Nullable(String),
    error_message String,
    stack_trace Nullable(String),
    request_data Nullable(String),
    created_at DateTime
) ENGINE = MergeTree()
ORDER BY (created_at);
```

**Uso:**

```php
// app/Jobs/LogErrorEvent.php
dispatch(new LogErrorEvent([
    'url' => $request->fullUrl(),
    'error_message' => $exception->getMessage(),
    'stack_trace' => $exception->getTraceAsString(),
    'request_data' => json_encode($request->all()),
    'created_at' => now()->format('Y-m-d H:i:s'),
]));
```

---

## 🚀 Criação Manual das Tabelas

### Opção 1: Script Helper (Recomendado)

```bash
# Definir senha
export CLICKHOUSE_PASSWORD=sua_senha_aqui

# Executar script
bash database/clickhouse/create_tables.sh

# Ou para ambiente específico:
CLICKHOUSE_URL=https://clickhouse.mesf.app \
CLICKHOUSE_USER=default \
CLICKHOUSE_PASSWORD=sua_senha \
bash database/clickhouse/create_tables.sh
```

O script automaticamente:

- ✅ Lê todos os arquivos `.sql` em `database/clickhouse/`
- ✅ Executa cada um via HTTP API
- ✅ Mostra status de sucesso/erro
- ✅ Verifica código HTTP de resposta

### Opção 2: Interface Web

1. Acesse `https://clickhouse.mesf.app/play`
2. Faça login com as credenciais:
    ```
    User: default
    Password: <sua_senha>
    ```
3. Cole e execute cada arquivo SQL de `database/clickhouse/`:

**error_log.sql:**

```sql
CREATE TABLE IF NOT EXISTS error_log (
    url Nullable(String),
    error_message String,
    stack_trace Nullable(String),
    request_data Nullable(String),
    created_at DateTime DEFAULT now()
) ENGINE = MergeTree()
ORDER BY (created_at);
```

**request_history.sql:**

```sql
CREATE TABLE IF NOT EXISTS request_history (
    session_id UInt64,
    route String,
    method String,
    payload Nullable(String),
    created_at DateTime DEFAULT now()
) ENGINE = MergeTree()
ORDER BY (created_at, session_id);
```

### Opção 3: Via curl (HTTP API)

```bash
# Definir variáveis
CLICKHOUSE_URL="https://clickhouse.mesf.app"
CLICKHOUSE_USER="default"
CLICKHOUSE_PASSWORD="sua_senha_aqui"

# Criar tabela error_log
curl -X POST "$CLICKHOUSE_URL/" \
  -u "$CLICKHOUSE_USER:$CLICKHOUSE_PASSWORD" \
  -d "CREATE TABLE IF NOT EXISTS error_log (
    url Nullable(String),
    error_message String,
    stack_trace Nullable(String),
    request_data Nullable(String),
    created_at DateTime DEFAULT now()
) ENGINE = MergeTree()
ORDER BY (created_at)"

# Criar tabela request_history
curl -X POST "$CLICKHOUSE_URL/" \
  -u "$CLICKHOUSE_USER:$CLICKHOUSE_PASSWORD" \
  -d "CREATE TABLE IF NOT EXISTS request_history (
    session_id UInt64,
    route String,
    method String,
    payload Nullable(String),
    created_at DateTime DEFAULT now()
) ENGINE = MergeTree()
ORDER BY (created_at, session_id)"
```

### Verificar tabelas criadas

```bash
# Listar tabelas
curl "$CLICKHOUSE_URL/?query=SHOW%20TABLES" \
  -u "$CLICKHOUSE_USER:$CLICKHOUSE_PASSWORD"

# Ver estrutura detalhada
curl "$CLICKHOUSE_URL/" \
  -u "$CLICKHOUSE_USER:$CLICKHOUSE_PASSWORD" \
  -d "SELECT name, engine, total_rows
      FROM system.tables
      WHERE database = 'default'
      FORMAT Pretty"
```

---

## 🐛 Troubleshooting

### Erro: "Port 9000 is for clickhouse-client program"

**Causa:** Proxy reverso está apontando para porta **9000** (Native TCP) em vez de **8123** (HTTP API).

**Solução:** Corrigir configuração do Nginx/Cloudflare para usar porta **8123**.

### Timeout ao conectar

**Possíveis causas:**

1. Firewall bloqueando porta 8123
2. ClickHouse não está rodando
3. DNS não aponta para o servidor correto

**Diagnóstico:**

```bash
# 1. ClickHouse está rodando?
sudo systemctl status clickhouse-server

# 2. Porta 8123 está aberta?
sudo netstat -tlnp | grep 8123

# 3. Firewall permite?
sudo ufw status | grep 8123

# 4. DNS resolve corretamente?
dig olap.mesf.app +short
```

### Query muito lenta

**Otimizações:**

```sql
-- 1. Adicionar índice skip
ALTER TABLE request_history ADD INDEX idx_route route TYPE set(0) GRANULARITY 4;

-- 2. Particionar por data (recommended para logs)
CREATE TABLE request_history_partitioned (
    session_id UInt64,
    route String,
    method String,
    payload Nullable(String),
    created_at DateTime
) ENGINE = MergeTree()
PARTITION BY toYYYYMM(created_at)  -- Particiona por mês
ORDER BY (created_at, session_id);

-- 3. TTL (deletar logs antigos automaticamente)
ALTER TABLE error_log MODIFY TTL created_at + INTERVAL 90 DAY;
```

### Espaço em disco crescendo

```bash
# Ver tamanho das tabelas
curl 'https://olap.mesf.app/' -d "
  SELECT
    database,
    table,
    formatReadableSize(sum(bytes)) AS size,
    sum(rows) AS rows
  FROM system.parts
  WHERE active
  GROUP BY database, table
  ORDER BY sum(bytes) DESC
  FORMAT PrettyCompact
"

# Deletar dados antigos
curl 'https://olap.mesf.app/' -d "
  ALTER TABLE request_history DELETE WHERE created_at < now() - INTERVAL 60 DAY
"

# Forçar merge de partes pequenas
curl 'https://olap.mesf.app/' -d "OPTIMIZE TABLE request_history FINAL"
```

---

## 📚 Referências

- [ClickHouse HTTP Interface](https://clickhouse.com/docs/en/interfaces/http)
- [ClickHouse Ports](https://clickhouse.com/docs/en/guides/sre/network-ports)
- [MergeTree Engine](https://clickhouse.com/docs/en/engines/table-engines/mergetree-family/mergetree)
- [ClickHouse + Nginx](https://clickhouse.com/docs/en/integrations/nginx)
