#!/bin/bash
set -e

echo "CI  Clearing all Laravel caches"
php artisan optimize:clear

echo "CI  Caching configuration for production"
php artisan config:cache
php artisan route:cache
php artisan view:cache
