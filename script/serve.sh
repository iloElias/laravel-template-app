#!/bin/bash

echo "Starting Laravel development server"

# Carrega o php.ini customizado do projeto (/app/php.ini) sem perder o scan dir
# original do PHP (extensões pdo, mbstring, etc. continuam carregadas).
# _PHP_DEFAULT_SCAN_DIR=$(php -r 'echo PHP_CONFIG_FILE_SCAN_DIR;')
# export PHP_INI_SCAN_DIR="/app${_PHP_DEFAULT_SCAN_DIR:+:${_PHP_DEFAULT_SCAN_DIR}}"
# unset _PHP_DEFAULT_SCAN_DIR

php artisan serve --host=0.0.0.0 --port="${PORT:-8000}" &

php artisan reverb:start --host=0.0.0.0 --port=6001 &

wait -n
