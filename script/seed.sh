#!/bin/bash
set -e

echo "Running database seeder"
php artisan db:seed --force