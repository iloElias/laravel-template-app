#!/bin/bash

echo "CI  Starting Laravel production server."
php artisan serve --host=0.0.0.0 --port=8000 --no-reload &

echo "CI  Starting Laravel Reverb server."
php artisan reverb:start --host=0.0.0.0 --port=6001 &

wait -n
