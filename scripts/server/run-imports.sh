#!/usr/bin/env bash
# Import data BSD City hanya kalau file data berubah (marker sha256 per file di storage/app/import-markers).
#
# Urutan tetap: bsd-city-data.json → update-2 → update-3 → update-4.
# Rantai import ulang: kalau bsd-city-data.json di-import ulang (hash berubah), marker semua file update
# dihapus, jadi semua update ikut di-import ulang dengan urutan yang sama (data utama menimpa nilai update).
# Import pertama bsd-city-data.json memakai --fresh; sebelum import selalu backup database.
#
# Pemakaian (dari folder aplikasi): bash scripts/server/run-imports.sh
# Variabel untuk test/simulasi: ARTISAN (bawaan "php artisan"), MARK_DIR, DATA_DIR.
set -uo pipefail

ARTISAN="${ARTISAN:-php artisan}"
MARK_DIR="${MARK_DIR:-storage/app/import-markers}"
DATA_DIR="${DATA_DIR:-docs/data}"

DATA_FILE="$DATA_DIR/bsd-city-data.json"
# Urutan penting: update dijalankan setelah data utama, berurutan.
UPDATE_FILES=(
    "$DATA_DIR/bsd-city-update-2.json"
    "$DATA_DIR/bsd-city-update-3.json"
    "$DATA_DIR/bsd-city-update-4.json"
)

mkdir -p "$MARK_DIR"

marker() { echo "$MARK_DIR/$(basename "$1").sha256"; }

# import_if_changed FILE COMMAND FLAGS_IMPORT_PERTAMA → 0 = dilewati, 10 = di-import, 1 = gagal.
import_if_changed() {
    local file="$1" cmd="$2" first_flags="$3" hash mark flags=""

    if [ ! -f "$file" ]; then
        echo "Lewati: $file tidak ditemukan"
        return 0
    fi

    hash=$(sha256sum "$file" | cut -d' ' -f1)
    mark=$(marker "$file")

    if [ -f "$mark" ] && [ "$(cat "$mark")" = "$hash" ]; then
        echo "Tidak berubah, lewati: $file"
        return 0
    fi

    [ -f "$mark" ] || flags="$first_flags"

    echo "Backup database sebelum import $file"
    if ! $ARTISAN backup:run --only-db --disable-notifications; then
        if echo "$flags" | grep -q -- "--fresh"; then
            echo "Backup gagal. Import --fresh dibatalkan supaya data tidak hilang."
            return 1
        fi
        echo "Peringatan: backup gagal, import (tanpa hapus data) tetap dilanjutkan"
    fi

    echo "Import $file $flags"
    # shellcheck disable=SC2086 # $flags boleh kosong atau satu flag.
    if ! $ARTISAN "$cmd" "$file" $flags --force --no-interaction; then
        echo "Import $file GAGAL (marker tidak diperbarui, akan dicoba lagi di deploy berikutnya)."
        return 1
    fi

    echo "$hash" > "$mark"

    return 10
}

import_if_changed "$DATA_FILE" import:bsd-data --fresh
rc=$?
[ "$rc" = 1 ] && exit 1

if [ "$rc" = 10 ]; then
    echo "Data utama di-import ulang: marker update dihapus supaya update-2, update-3, update-4 ikut di-import ulang."
    for file in "${UPDATE_FILES[@]}"; do
        rm -f "$(marker "$file")"
    done
fi

for file in "${UPDATE_FILES[@]}"; do
    import_if_changed "$file" import:bsd-update ""
    [ "$?" = 1 ] && exit 1
done

exit 0
