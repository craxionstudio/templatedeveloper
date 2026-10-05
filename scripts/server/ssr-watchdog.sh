#!/usr/bin/env bash
# Penjaga SSR tanpa root (dipakai kalau Supervisor tidak tersedia): kalau server SSR aplikasi ini
# tidak menjawab, jalankan lagi di background (lepas dari sesi SSH/cron). Aman dijalankan berulang.
#
# Pemakaian (dari folder aplikasi): bash scripts/server/ssr-watchdog.sh
set -uo pipefail

APP_DIR="$(pwd)"
LOCK="$APP_DIR/storage/framework/cache/ssr-watchdog.lock"
URL="$(grep -E '^INERTIA_SSR_URL=' .env 2>/dev/null | tail -n1 | cut -d= -f2- | tr -d '"'"'"' ')"
URL="${URL:-http://127.0.0.1:13714}"

healthy() {
    curl -fsS -m 3 "$URL/health" > /dev/null 2>&1
}

exec 9> "$LOCK"
flock -n 9 || exit 0

if healthy; then
    exit 0
fi

mkdir -p storage/logs
echo "[$(date '+%Y-%m-%d %H:%M:%S')] SSR tidak menjawab di $URL, menjalankan ulang" >> storage/logs/ssr.log
setsid nohup php artisan inertia:start-ssr >> storage/logs/ssr.log 2>&1 < /dev/null 9>&- &

for _ in $(seq 1 20); do
    sleep 1
    healthy && exit 0
done

echo "[$(date '+%Y-%m-%d %H:%M:%S')] SSR belum menjawab setelah 20 detik" >> storage/logs/ssr.log
exit 1
