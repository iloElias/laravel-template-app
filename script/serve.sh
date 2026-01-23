#!/bin/bash

echo "Starting Laravel development server with Reverb"

php artisan serve --host=0.0.0.0 --port=80 &

# php artisan reverb:start --host=0.0.0.0 --port=6001 &

wait -n
