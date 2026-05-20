#!/bin/bash
set -e

echo "Running database seeders for environment: ${APP_ENV:-local}"
php artisan db:seed --force

echo "✓ Seeders completed successfully"
