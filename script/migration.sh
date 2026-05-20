#!/bin/bash
set -e

echo "CI  Running database migrations."
php artisan migrate --force --database=pgsql_direct
