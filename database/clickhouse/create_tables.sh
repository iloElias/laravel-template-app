#!/bin/bash
# Script para criar tabelas ClickHouse manualmente
# Execute: bash database/clickhouse/create_tables.sh

set -e

# Configurações (ajuste conforme seu ambiente)
CLICKHOUSE_URL="${CLICKHOUSE_URL:-https://clickhouse.mesf.app}"
CLICKHOUSE_USER="${CLICKHOUSE_USER:-default}"

if [ -z "$CLICKHOUSE_PASSWORD" ]; then
    echo "❌ CLICKHOUSE_PASSWORD não definida"
    echo "Use: export CLICKHOUSE_PASSWORD=sua_senha"
    exit 1
fi

echo "🔧 Criando tabelas no ClickHouse..."
echo "URL: $CLICKHOUSE_URL"
echo "User: $CLICKHOUSE_USER"
echo ""

# Função para executar SQL
execute_sql() {
    local file=$1
    local filename

    filename=$(basename "$file")

    echo "→ Executando $filename..."

    response=$(curl -s -w "\nHTTP_CODE:%{http_code}" \
        -X POST "$CLICKHOUSE_URL/" \
        -u "$CLICKHOUSE_USER:$CLICKHOUSE_PASSWORD" \
        --data-binary @"$file")

    http_code=$(echo "$response" | grep "HTTP_CODE:" | cut -d: -f2)
    body=$(echo "$response" | sed '/HTTP_CODE:/d')

    if [ "$http_code" = "200" ]; then
        echo "✓ $filename criada com sucesso"
    else
        echo "✗ Erro ao criar $filename (HTTP $http_code)"
        echo "Resposta: $body"
        exit 1
    fi
}

# Executar todos os arquivos .sql
script_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

for sql_file in "$script_dir"/*.sql; do
    if [ -f "$sql_file" ]; then
        execute_sql "$sql_file"
    fi
done

echo ""
echo "✅ Todas as tabelas foram criadas com sucesso!"
echo ""
echo "Verificar tabelas criadas:"
echo "curl '$CLICKHOUSE_URL/?query=SHOW%20TABLES' -u '$CLICKHOUSE_USER:$CLICKHOUSE_PASSWORD'"
