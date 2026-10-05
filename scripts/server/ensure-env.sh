#!/usr/bin/env bash
# Pengaturan .env production yang aman (idempotent). Hanya mengubah key di bawah; key lain utuh.
#   APP_ENV=production, APP_DEBUG=false, LOG_LEVEL bukan debug (jadi warning),
#   INERTIA_DEVTOOLS_ENABLED=false, SESSION_SECURE_COOKIE=true (cookie hanya lewat HTTPS),
#   SITE_INDEXABLE=false HANYA kalau belum ada (tidak pernah diubah ke true).
# Kalau ada yang berubah: .env dibackup dulu ke .env.backup-{tanggal-jam}, lalu optimize:clear + config:cache.
# Baris terakhir output: "BACKUP=<file>" (kosong kalau tidak ada perubahan), dipakai deploy untuk rollback.
#
# Pemakaian (dari folder aplikasi): bash scripts/server/ensure-env.sh
set -euo pipefail

ENV_FILE=".env"
[ -f "$ENV_FILE" ] || { echo "File .env tidak ditemukan di $(pwd)"; exit 1; }

current() {
    # Nilai terakhir untuk key (tanpa tanda kutip); kosong kalau tidak ada.
    grep -E "^$1=" "$ENV_FILE" | tail -n1 | cut -d= -f2- | tr -d '"'"'"' \r' || true
}

declare -A WANT=()
WANT[APP_ENV]=production
WANT[APP_DEBUG]=false
WANT[INERTIA_DEVTOOLS_ENABLED]=false
WANT[SESSION_SECURE_COOKIE]=true

LOG_LEVEL_NOW="$(current LOG_LEVEL)"
if [ -z "$LOG_LEVEL_NOW" ] || [ "$LOG_LEVEL_NOW" = "debug" ]; then WANT[LOG_LEVEL]=warning; fi
grep -qE '^SITE_INDEXABLE=' "$ENV_FILE" || WANT[SITE_INDEXABLE]=false

CHANGES=()
for KEY in APP_ENV APP_DEBUG LOG_LEVEL INERTIA_DEVTOOLS_ENABLED SESSION_SECURE_COOKIE SITE_INDEXABLE; do
    [ -n "${WANT[$KEY]:-}" ] || continue
    COUNT="$(grep -cE "^$KEY=" "$ENV_FILE" || true)"
    if [ "$(current "$KEY")" != "${WANT[$KEY]}" ] || [ "$COUNT" -gt 1 ]; then
        CHANGES+=("$KEY")
    fi
done

if [ "${#CHANGES[@]}" -eq 0 ]; then
    echo ".env sudah sesuai, tidak ada perubahan."
    echo "BACKUP="
    exit 0
fi

BACKUP=".env.backup-$(date +%Y-%m-%d-%H%M%S)"
cp -p "$ENV_FILE" "$BACKUP"
chmod 600 "$BACKUP" 2> /dev/null || true

for KEY in "${CHANGES[@]}"; do
    VALUE="${WANT[$KEY]}"
    if grep -qE "^$KEY=" "$ENV_FILE"; then
        # Ubah baris pertama, buang duplikat berikutnya (tidak pernah menambah baris kedua).
        awk -v key="$KEY" -v val="$VALUE" 'BEGIN { done = 0 }
            $0 ~ "^" key "=" { if (!done) { print key "=" val; done = 1 }; next }
            { print }' "$ENV_FILE" > "$ENV_FILE.tmp"
        cat "$ENV_FILE.tmp" > "$ENV_FILE" && rm -f "$ENV_FILE.tmp"
    else
        # Pastikan file berakhir dengan newline sebelum menambah baris.
        [ -z "$(tail -c1 "$ENV_FILE")" ] || echo >> "$ENV_FILE"
        echo "$KEY=$VALUE" >> "$ENV_FILE"
    fi
    echo "Diubah: $KEY=$VALUE"
done

php artisan optimize:clear > /dev/null
php artisan config:cache > /dev/null
echo "Backup .env lama: $BACKUP"
echo "BACKUP=$BACKUP"
