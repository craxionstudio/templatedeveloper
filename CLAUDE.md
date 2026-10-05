# CLAUDE.md

Website properti BSD City: Laravel 13 + Filament 5 (admin) + Inertia v3 / React (SSR), Pest untuk test.
Dokumentasi: `README.md`, catatan perubahan di `docs/CHANGES.md`, checklist go-live di `docs/CHECKLIST-LAUNCH.md`.

## Alur kerja git (wajib, berlaku mulai 5 Okt 2026)

- **Kerjakan langsung di branch `main`.** Tidak memakai branch fitur atau Pull Request.
- **Sebelum mulai kerja:** `git pull --rebase origin main`.
- **Satu pekerjaan = satu commit** dengan pesan yang jelas (apa yang berubah dan kenapa), supaya mudah di-revert
  (`git revert <sha>`) kalau ada masalah. Jangan menggabungkan beberapa pekerjaan dalam satu commit.
- **Sebelum push:**
    1. Jalankan semua test dan pastikan **semuanya lolos** (lihat bagian Test di bawah).
    2. `git pull --rebase origin main` lagi.
    3. Baru `git push origin main`.
- **Push ke `main` otomatis men-deploy ke production** (`.github/workflows/deploy.yml`: migrate, build, import data
  bila file data berubah). Perlakukan setiap push sebagai rilis.
- **Kalau test gagal atau ada konflik saat rebase: JANGAN push.** Hentikan dan laporkan ke pemilik dulu (apa yang
  gagal / file yang konflik). Jangan memaksa (`--force`), jangan melewati atau menonaktifkan test.

## Test

Jalankan semua sebelum push (harus lolos semua):

```bash
php artisan inertia:start-ssr &   # test SSR di-skip kalau server SSR tidak jalan
composer test                     # Pint (format PHP) + seluruh test Pest (sebagai root: COMPOSER_ALLOW_SUPERUSER=1)
npm run check                     # format + lint frontend
npm run types:check               # TypeScript
npm run build                     # pastikan build production berhasil
```

Kalau ada perubahan migrasi/seeder, cek juga `php artisan migrate:fresh --seed` dari nol.
