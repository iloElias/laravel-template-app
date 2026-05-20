#!/bin/bash

echo "CI  Starting Laravel production server"
php artisan serve &

echo "CI  Starting Laravel Reverb server"
php artisan reverb:start &

wait -n
