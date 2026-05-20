#!/bin/bash
set -e

echo "Running database migrations"
php artisan migrate --force --database=pgsql_direct
