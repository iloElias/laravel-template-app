#!/bin/bash
set -e

echo "CI  Caching configuration for production."
php artisan config:cache
php artisan route:cache
php artisan view:cache
