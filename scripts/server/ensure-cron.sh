#!/usr/bin/env bash
# Pasang baris cron aplikasi ini (idempotent, tanpa root): scheduler Laravel tiap menit
# (queue worker WebP, backup, sitemap) + penjaga SSR. Baris cron lain TIDAK diubah atau dihapus:
# crontab lama dibaca apa adanya lalu baris baru ditambahkan di bawahnya, hanya kalau belum ada.
#
# Pemakaian (dari folder aplikasi): bash scripts/server/ensure-cron.sh [--with-ssr-watchdog]
set -euo pipefail

APP_DIR="$(pwd)"
PHP_BIN="$(command -v php)"
CURRENT="$(crontab -l 2>/dev/null || true)"
ADD=()

has_line() {
    # Cocokkan berdasarkan folder aplikasi + perintah, bukan teks persis (path php boleh beda).
    printf '%s\n' "$CURRENT" | grep -v '^[[:space:]]*#' | grep -F "cd $APP_DIR && " | grep -qF "$1"
}

if has_line "artisan schedule:run"; then
    echo "Cron scheduler: sudah ada"
else
    ADD+=("* * * * * cd $APP_DIR && $PHP_BIN artisan schedule:run >> /dev/null 2>&1")
    echo "Cron scheduler: ditambahkan"
fi

if [ "${1:-}" = "--with-ssr-watchdog" ]; then
    if has_line "scripts/server/ssr-watchdog.sh"; then
        echo "Cron penjaga SSR: sudah ada"
    else
        # @reboot = SSR langsung jalan setelah server restart; tiap menit = hidup lagi kalau crash.
        ADD+=("@reboot cd $APP_DIR && bash scripts/server/ssr-watchdog.sh >> /dev/null 2>&1")
        ADD+=("* * * * * cd $APP_DIR && bash scripts/server/ssr-watchdog.sh >> /dev/null 2>&1")
        echo "Cron penjaga SSR: ditambahkan"
    fi
fi

if [ "${#ADD[@]}" -gt 0 ]; then
    {
        if [ -n "$CURRENT" ]; then printf '%s\n' "$CURRENT"; fi
        printf '%s\n' "${ADD[@]}"
    } | crontab -
fi

echo "--- crontab sekarang ---"
crontab -l 2>/dev/null || true
