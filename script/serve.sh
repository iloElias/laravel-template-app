#!/bin/bash

echo "CI  Starting Laravel production server"
php artisan serve --no-reload &

echo "CI  Starting Laravel Reverb server"
php artisan reverb:start &

wait -n
