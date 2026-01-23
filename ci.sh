#!/bin/bash
set -e

echo "Running database migrations"
php artisan migrate --force

echo "Starting Laravel queue worker in background"
nohup php artisan queue:work > /dev/null 2>&1 &
disown

# echo "Starting Laravel scheduler loop in background"
# nohup bash -c 'while true; do php artisan schedule:run >> /dev/null 2>&1; sleep 60; done' &
# disown

echo "Starting Laravel production server"
php artisan serve --host=0.0.0.0 --port=80
