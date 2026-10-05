#!/usr/bin/env bash
# Cek website dari luar (lewat APP_URL, seperti pengunjung) sebelum & sesudah .env diubah ke production.
#
#   bash scripts/server/verify-env.sh baseline            -> catat apakah APP_URL bisa dijangkau dari server
#   bash scripts/server/verify-env.sh verify <backup-env> -> cek lagi; kalau sebelumnya terjangkau tapi sekarang
#        redirect loop / error, .env dikembalikan dari backup (rollback) lalu cache dibangun ulang.
#
# Nilai cookie tidak pernah ditampilkan (hanya nama & atribut).
set -uo pipefail

MODE="${1:-verify}"
BACKUP="${2:-}"
STATE="storage/framework/cache/env-baseline"
URL="$(grep -E '^APP_URL=' .env | tail -n1 | cut -d= -f2- | tr -d '"'"'"' \r')"
URL="${URL%/}"

probe() {
    curl -sS -o /dev/null -L --max-redirs 5 --max-time 20 -w '%{http_code} %{num_redirects} %{url_effective}' "$URL/" 2> /dev/null
}

if [ "$MODE" = "baseline" ]; then
    OUT="$(probe)"
    RC=$?
    echo "$RC $OUT" > "$STATE"
    echo "Sebelum perubahan .env: $URL/ -> $OUT (curl exit $RC)"
    exit 0
fi

BASE_RC="$(cut -d' ' -f1 "$STATE" 2> /dev/null || echo 99)"
OUT="$(probe)"
RC=$?
CODE="$(printf '%s' "$OUT" | cut -d' ' -f1)"
echo "Sesudah perubahan .env: $URL/ -> $OUT (curl exit $RC)"

OK=0
if [ "$RC" = "0" ] && [ "$CODE" -ge 200 ] 2> /dev/null && [ "$CODE" -lt 400 ]; then OK=1; fi

# Login admin: halaman login 200, cookie session ber-flag Secure, dan session terbawa ke request berikutnya
# (cookie dikirim balik lewat HTTPS = tidak mental ke login karena CSRF/session hilang).
ADMIN_OK=1
if [ "$OK" = "1" ]; then
    JAR="$(mktemp)"
    ADMIN_HEADERS="$(curl -sS -D - -o /dev/null -c "$JAR" -L --max-redirs 5 --max-time 20 "$URL/admin/login" 2> /dev/null | tr -d '\r')"
    ADMIN_CODE="$(printf '%s\n' "$ADMIN_HEADERS" | grep -E '^HTTP/' | tail -n1 | cut -d' ' -f2)"
    ADMIN_SECURE="$(printf '%s\n' "$ADMIN_HEADERS" | grep -iE '^set-cookie: *[a-z0-9_-]*session=' | grep -ciE ';\s*secure' || true)"
    ADMIN_AGAIN="$(curl -sS -o /dev/null -b "$JAR" -L --max-redirs 5 --max-time 20 -w '%{http_code}' "$URL/admin/login" 2> /dev/null)"
    rm -f "$JAR"
    echo "Login admin dari luar: $URL/admin/login -> $ADMIN_CODE, cookie session Secure: $([ "${ADMIN_SECURE:-0}" -gt 0 ] && echo ya || echo TIDAK), request kedua dengan cookie -> $ADMIN_AGAIN"
    if [ "$ADMIN_CODE" != "200" ] || [ "$ADMIN_AGAIN" != "200" ]; then ADMIN_OK=0; OK=0; fi
fi

diagnose() {
    echo "--- Diagnosis (sebelum rollback) ---"
    echo "User deploy: $(id -un); user PHP-FPM: $(ps -o user= -C "$(ps -eo comm | grep -m1 -E '^php-fpm' || echo php-fpm)" 2> /dev/null | sort -u | tr '\n' ' ')"
    for P in storage storage/logs storage/logs/laravel.log storage/framework storage/framework/cache storage/framework/cache/data storage/framework/sessions storage/framework/views bootstrap/cache bootstrap/cache/config.php storage/app/private; do
        [ -e "$P" ] && stat -c '%U:%G %a %n' "$P"
    done
    echo "--- Error terakhir di log Laravel (baris pertama saja) ---"
    grep -hE '^\[[0-9-]+ [0-9:]+\] [a-z]+\.(ERROR|CRITICAL|ALERT|EMERGENCY):' storage/logs/laravel*.log 2> /dev/null | tail -n 3 | cut -c1-400
}

if [ "$OK" = "0" ]; then
    diagnose
    if [ "$BASE_RC" = "0" ] && [ -n "$BACKUP" ] && [ -f "$BACKUP" ]; then
        echo "ROLLBACK: website atau login admin tidak bisa diakses normal setelah .env diubah (curl exit $RC, status $CODE, login admin OK: $ADMIN_OK)."
        echo "          Kemungkinan redirect loop (HTTPS di belakang proxy), cookie Secure tanpa HTTPS, atau APP_URL salah. .env dikembalikan dari $BACKUP."
        cp -p "$BACKUP" .env
        php artisan optimize:clear > /dev/null
        php artisan optimize > /dev/null
        exit 2
    fi
    echo "PERINGATAN: $URL/ tidak bisa dicek dari server (sebelum perubahan pun tidak terjangkau: curl exit $BASE_RC). Tidak ada rollback."
    exit 1
fi

echo "--- Header beranda dari luar ($URL/) ---"
curl -sS -I -L --max-redirs 5 --max-time 20 "$URL/" 2> /dev/null \
    | tr -d '\r' \
    | grep -iE '^(HTTP/|location:|x-robots-tag:|strict-transport-security:|x-page-cache:|set-cookie:)' \
    | sed -E 's/^(set-cookie: *)([^=]+)=[^;]*/\1\2=[disembunyikan]/I'
echo "--- robots.txt dari luar ---"
curl -sS -L --max-redirs 5 --max-time 20 "$URL/robots.txt" 2> /dev/null | head -n 10
exit 0
