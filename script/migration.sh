#!/bin/bash
set -e

echo "Running database migrations"
php artisan migrate --force --database=pgsql_direct

echo "Running ClickHouse migrations"
php artisan clickhouse:migrate
