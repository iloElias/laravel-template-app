#!/bin/bash
set -e

bash /app/script/cache.sh

bash /app/script/migration.sh

bash /app/script/seed.sh

nohup bash /app/script/queue.sh > /dev/null 2>&1 &
disown

exec bash /app/script/serve.sh
