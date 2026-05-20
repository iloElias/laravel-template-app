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
