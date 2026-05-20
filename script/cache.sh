#!/bin/bash
set -e

echo "Caching configuration for production"
php artisan config:cache
php artisan route:cache
php artisan view:cache
