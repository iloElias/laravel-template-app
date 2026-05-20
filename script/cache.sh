#!/bin/bash
set -e

# Limpa apenas caches baseados em arquivo (não requer conexão com Redis)
echo "CI  Clearing file-based Laravel caches"
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan event:clear

echo "CI  Caching configuration for production"
php artisan config:cache
php artisan route:cache
php artisan view:cache
