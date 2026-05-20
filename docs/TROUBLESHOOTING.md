# 🔧 Troubleshooting

## ❌ Erro: "Class 'Redis' not found"

### Descrição

```
Class "Redis" not found
at vendor/laravel/framework/src/Illuminate/Redis/Connectors/PhpRedisConnector.php:80
```

### Causa

O Laravel tentou usar Redis antes da extensão `phpredis` estar disponível, ou o cache de configuração foi criado sem a extensão.

---

## 🏠 Solução para Ambiente Local

### 1. Instalar extensão phpredis

**Ubuntu/Debian:**

```bash
sudo apt install php8.3-redis
```

**macOS (Homebrew):**

```bash
pecl install redis
# Adicionar 'extension=redis.so' no php.ini
```

### 2. Verificar instalação

```bash
php -m | grep redis
```

### 3. Limpar cache do Laravel

```bash
php artisan config:clear
php artisan cache:clear
```

### 4. Reiniciar servidor

```bash
bash script/serve.sh
```

---

## 🐳 Solução para Ambiente Docker/Dokploy

### Verificação

O [Dockerfile.php.dev](../Dockerfile.php.dev) **já instala** phpredis nas linhas 23-24:

```dockerfile
RUN pecl install redis \
    && docker-php-ext-enable redis
```

### Se o erro ocorrer em produção:

#### Opção 1: Rebuild da Imagem Docker

No Dokploy, force um rebuild completo da imagem:

```bash
# Via interface do Dokploy
Settings → Rebuild → Force Rebuild (sem cache)
```

#### Opção 2: Limpar Cache Manualmente

Conecte ao container e execute:

```bash
# Dokploy CLI ou Docker exec
docker exec -it <container-name> bash
php artisan config:clear
php artisan cache:clear
```

#### Opção 3: Garantir Ordem de Inicialização

O script [script/cache.sh](../script/cache.sh) foi atualizado para **sempre limpar cache antes de criar novo**:

```bash
#!/bin/bash
set -e

echo "CI  Clearing previous cache"
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

echo "CI  Caching configuration for production"
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Isso garante que o cache seja sempre criado com a extensão Redis disponível.

---

## 🔍 Diagnóstico

### Verificar se Redis está instalado

```bash
php -r "var_dump(extension_loaded('redis'));"
# Deve retornar: bool(true)
```

### Verificar se a classe existe

```bash
php -r "var_dump(class_exists('Redis'));"
# Deve retornar: bool(true)
```

### Verificar conexão com Redis Server

```bash
php artisan tinker
>>> Redis::ping();
# Deve retornar: "PONG"
```

---

## 🚀 Prevenção

### Deploy no Dokploy

1. **Container Redis**: Garantir que o serviço Redis está rodando
2. **Variáveis de ambiente**: Verificar `.env` ou variáveis do Dokploy:

    ```env
    REDIS_CLIENT=phpredis
    REDIS_HOST=redis        # Nome do container/serviço
    REDIS_PORT=6379
    CACHE_STORE=redis
    ```

3. **Dockerfile**: Confirmar que phpredis está instalado
4. **CI Script**: O [ci.sh](../ci.sh) executa cache.sh que agora limpa antes de cachear

### Ordem de Execução (ci.sh)

```
1. cache.sh    → Limpa cache antigo + Cria novo cache
2. migration.sh → Executa migrações
3. seed.sh     → Popula banco de dados
4. queue.sh    → Inicia workers (background)
5. serve.sh    → Inicia servidores Laravel + Reverb
```

---

## 📚 Arquivos Relacionados

- [Dockerfile.php.dev](../Dockerfile.php.dev) - Instalação phpredis
- [script/cache.sh](../script/cache.sh) - Script de cache com limpeza
- [.env.docker](../.env.docker) - Configuração Redis para Docker
- [config/cache.php](../config/cache.php) - Configuração de cache Laravel
- [config/database.php](../config/database.php) - Configuração Redis
