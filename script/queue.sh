#!/bin/bash

# cron: */30 * * * *
# command: bash /app/script/queue.sh
# O worker roda por no máximo 29 minutos (--max-time=1740) e encerra antes

echo "CI  Killing existing Laravel queue workers"
pkill -f "artisan queue:work" || true

echo "CI  Starting Laravel queue worker"
cd /app && php artisan queue:work --tries=3 --max-time=1740 &

exit 0
