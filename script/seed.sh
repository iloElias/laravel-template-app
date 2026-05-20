#!/bin/bash
set -e

echo "CI  Running database seeders for environment: ${APP_ENV:-local}."
php artisan db:seed --force

