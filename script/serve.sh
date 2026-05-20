#!/bin/bash

echo "CI  Starting Laravel production server."
php artisan serve --port=80 --no-reload &

# echo "CI  Starting Laravel Reverb server."
# php artisan reverb:start --port=6001 &

wait -n
