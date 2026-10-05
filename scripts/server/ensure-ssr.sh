#!/usr/bin/env bash
# Pastikan server SSR Inertia aplikasi ini selalu jalan, otomatis hidup lagi setelah crash/reboot, dan
# memakai bundle terbaru setelah deploy. Idempotent; hanya menyentuh program/baris cron milik aplikasi ini.
#
# 1. Supervisor (kalau ada, atau bisa di-install lewat root / sudo tanpa password):
#    /etc/supervisor/conf.d/<nama>.conf, program khusus aplikasi ini, autostart + autorestart.
# 2. Tanpa root: cron @reboot + penjaga tiap menit (scripts/server/ssr-watchdog.sh).
#
# Pemakaian (dari folder aplikasi): bash scripts/server/ensure-ssr.sh
set -uo pipefail

APP_DIR="$(pwd)"
PHP_BIN="$(command -v php)"
APP_USER="$(id -un)"
PROGRAM="ssr-$(basename "$APP_DIR" | tr -c 'A-Za-z0-9_\n-' '-')"
CONF="/etc/supervisor/conf.d/$PROGRAM.conf"
URL="$(grep -E '^INERTIA_SSR_URL=' .env 2>/dev/null | tail -n1 | cut -d= -f2- | tr -d '"'"'"' ')"
URL="${URL:-http://127.0.0.1:13714}"

if [ "$(id -u)" = "0" ]; then
    SUDO=""
elif sudo -n true 2> /dev/null; then
    SUDO="sudo -n"
else
    SUDO="none"
fi

healthy() {
    curl -fsS -m 3 "$URL/health" > /dev/null 2>&1
}

PORT="$(printf '%s' "$URL" | sed -E 's#^[a-z]+://[^:/]+:?([0-9]*).*#\1#')"
PORT="${PORT:-80}"

# Proses yang mendengarkan port SSR: milik aplikasi ini kalau folder kerjanya = folder aplikasi.
# "other" = dipakai aplikasi lain (atau user lain): jangan dihentikan.
port_owner() {
    local pid
    pid="$(ss -ltnpH "sport = :$PORT" 2> /dev/null | grep -o 'pid=[0-9]*' | head -n1 | cut -d= -f2)"
    if [ -z "$pid" ]; then
        if ss -ltnH "sport = :$PORT" 2> /dev/null | grep -q .; then echo "other"; else echo "free"; fi
        return
    fi
    if [ "$(readlink "/proc/$pid/cwd" 2> /dev/null)" = "$APP_DIR" ]; then echo "us"; else echo "other"; fi
}

stop_ours() {
    if [ "$(port_owner)" = "us" ]; then
        "$PHP_BIN" artisan inertia:stop-ssr > /dev/null 2>&1
        sleep 1
    fi
}

if [ "$(port_owner)" = "other" ]; then
    echo "PERINGATAN: port $PORT dipakai proses lain (bukan aplikasi ini). SSR aplikasi ini tidak dijalankan supaya"
    echo "aplikasi lain tidak terganggu. Pakai port lain: INERTIA_SSR_URL=http://127.0.0.1:<port> di .env dan"
    echo "INERTIA_SSR_PORT=<port> saat npm run build."
    exit 1
fi

wait_healthy() {
    for _ in $(seq 1 30); do
        healthy && return 0
        sleep 1
    done
    return 1
}

METHOD=""

if [ "$SUDO" != "none" ]; then
    if ! command -v supervisorctl > /dev/null 2>&1 && command -v apt-get > /dev/null 2>&1; then
        echo "Supervisor belum ada, memasang..."
        $SUDO apt-get install -y supervisor > /dev/null 2>&1 \
            || { $SUDO apt-get update -y > /dev/null 2>&1 && $SUDO apt-get install -y supervisor > /dev/null 2>&1; } \
            || echo "Gagal memasang Supervisor"
    fi

    if command -v supervisorctl > /dev/null 2>&1; then
        ($SUDO systemctl enable --now supervisor > /dev/null 2>&1 || $SUDO service supervisor start > /dev/null 2>&1) || true
        METHOD="supervisor"
    fi
fi

if [ "$METHOD" = "supervisor" ]; then
    WANTED="[program:$PROGRAM]
; SSR Inertia untuk $APP_DIR (dibuat otomatis oleh scripts/server/ensure-ssr.sh saat deploy)
command=$PHP_BIN artisan inertia:start-ssr
directory=$APP_DIR
user=$APP_USER
autostart=true
autorestart=true
startsecs=3
stopasgroup=true
killasgroup=true
redirect_stderr=true
stdout_logfile=$APP_DIR/storage/logs/ssr.log
stdout_logfile_maxbytes=5MB
stdout_logfile_backups=2"

    if [ "$($SUDO cat "$CONF" 2> /dev/null)" != "$WANTED" ]; then
        # Proses SSR lama di luar Supervisor (mis. dari penjaga cron) dihentikan dulu supaya port bebas.
        stop_ours
        printf '%s\n' "$WANTED" | $SUDO tee "$CONF" > /dev/null
        $SUDO supervisorctl reread > /dev/null
        $SUDO supervisorctl update "$PROGRAM" > /dev/null
        echo "Konfigurasi Supervisor ditulis: $CONF"
    fi

    # Bundle SSR baru setelah deploy: restart program ini saja.
    $SUDO supervisorctl restart "$PROGRAM" > /dev/null 2>&1 || $SUDO supervisorctl start "$PROGRAM" > /dev/null 2>&1
    STATUS="$($SUDO supervisorctl status "$PROGRAM" 2>&1)"
else
    METHOD="cron-watchdog (tanpa root)"
    bash scripts/server/ensure-cron.sh --with-ssr-watchdog > /dev/null
    # Restart supaya memakai bundle baru: hentikan proses lama (port aplikasi ini), lalu jalankan penjaga.
    stop_ours
    bash scripts/server/ssr-watchdog.sh
    STATUS="$(pgrep -af '[i]nertia:start-ssr' | grep -F "$APP_DIR" || pgrep -af '[b]ootstrap/ssr/app.js' || echo 'proses tidak ditemukan')"
fi

if wait_healthy; then
    echo "SSR OK ($METHOD) di $URL"
    echo "Status: $STATUS"
    exit 0
fi

echo "SSR TIDAK menjawab di $URL ($METHOD). Status: $STATUS"
tail -n 20 storage/logs/ssr.log 2> /dev/null || true
exit 1
