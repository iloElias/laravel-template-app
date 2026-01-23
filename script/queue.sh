#!/bin/bash
set -e

echo "Starting Laravel queue worker in background"
nohup php artisan queue:work > /dev/null 2>&1 &
disown
