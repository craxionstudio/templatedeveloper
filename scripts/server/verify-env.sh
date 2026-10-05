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
STATE="storage/framework/env-baseline"
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

if [ "$OK" = "0" ]; then
    if [ "$BASE_RC" = "0" ] && [ -n "$BACKUP" ] && [ -f "$BACKUP" ]; then
        echo "ROLLBACK: website tidak bisa diakses normal setelah .env diubah (curl exit $RC, status $CODE)."
        echo "          Kemungkinan redirect loop (HTTPS di belakang proxy) atau APP_URL salah. .env dikembalikan dari $BACKUP."
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
