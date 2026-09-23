# Website Developer Perumahan — Arunika Land (nama sementara)

Website resmi developer perumahan (satu kawasan), fokus lead generation (form + WhatsApp) dan
kepercayaan terhadap developer. Dibangun sesuai `docs/BRIEF.md`:
Laravel 13 + Filament 5 (admin/CMS di `/admin`) + Inertia.js v3 + React + TypeScript +
Tailwind CSS v4, dengan **Inertia SSR** supaya konten, link, dan (nanti) JSON-LD sudah ada di HTML
awal yang dibaca crawler Google.

Referensi desain ada di `docs/design/` (desktop 1440 + mobile 390, HTML statis + screenshot).

## Status Milestone

| #   | Milestone                                                                                                       | Status     |
| --- | --------------------------------------------------------------------------------------------------------------- | ---------- |
| 1   | Setup: Laravel + React starter kit (Inertia v3, TS) + SSR + Filament 5 + token + font self-host + layout global | ✅ Selesai |
| 2   | Model & admin (migrasi, seeder dummy, Filament resource, settings per halaman)                                  | Belum      |
| 3   | Halaman publik sesuai desain                                                                                    | Belum      |
| 4   | Lead & tracking                                                                                                 | Belum      |
| 5   | Technical SEO                                                                                                   | Belum      |
| 6   | Performa                                                                                                        | Belum      |
| 7   | QA                                                                                                              | Belum      |

## Requirement

- PHP 8.3+ (ekstensi: pdo_sqlite, mbstring, intl, gd/zip untuk Filament) dan Composer
- Node.js 22+ dan npm
- Production: MySQL 8, Redis (opsional, fallback `database`), Supervisor

## Instalasi Pertama Kali

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
php artisan storage:link
```

Seeder saat ini membuat:

- User admin: **admin@example.com** / **password** (ganti setelah login pertama)

Data konten (cluster, fasilitas, artikel, settings per halaman) menyusul di Milestone 2.

> Default database pakai **SQLite** (`database/database.sqlite`) supaya gampang dijalankan
> lokal. Untuk production, `.env.example` sudah berisi blok **MySQL** + Redis yang tinggal
> di-uncomment (lihat bagian Deploy).

## Menjalankan di Lokal (Development)

Cukup satu perintah (server Laravel + queue + log + Vite jalan bersamaan):

```bash
composer dev
```

Atau manual di 2 terminal:

**Terminal 1 — Laravel:**

```bash
php artisan serve
```

**Terminal 2 — Vite (build & hot-reload React/Tailwind):**

```bash
npm run dev
```

Buka:

- Website publik: http://127.0.0.1:8000
- Admin panel (Filament): http://127.0.0.1:8000/admin — login pakai `admin@example.com` / `password`

Saat `npm run dev` jalan, **SSR sudah aktif otomatis**: plugin `@inertiajs/vite` menyediakan
endpoint SSR di dev server Vite, jadi tidak perlu proses SSR terpisah selama development.

## Menjalankan Mode Production-like (dengan SSR)

Simulasi paling mendekati production:

```bash
npm run build                    # build aset client + bundle SSR (bootstrap/ssr/app.js)
php artisan inertia:start-ssr &  # proses Node SSR di background (port 13714)
php artisan serve
```

Cek SSR bekerja:

- `php artisan inertia:check-ssr` → "Inertia SSR server is running."
- `curl -s http://127.0.0.1:8000/ | grep "<h1"` harus mengembalikan judul hero. Header,
  menu drawer mobile, dan footer juga harus ada di HTML, bukan cuma `<div id="app">` kosong.

Untuk mematikan proses SSR: `php artisan inertia:stop-ssr`.

## Testing & Pengecekan Kode

```bash
php artisan test        # Pest
composer lint           # Pint (format PHP)
npm run types:check     # TypeScript
npm run check           # lint + format frontend (Vite+ / oxlint / oxfmt)
```

`composer ci:check` menjalankan semuanya (dipakai GitHub Actions di `.github/workflows/tests.yml`).

## Mengisi Konten

**Aturan utama (brief 7A):** tidak ada teks, gambar, atau link yang di-hardcode di komponen React.

Status Milestone 1:

- Data layout global (nama brand, menu, hotline, CTA header, kolom footer, sosial, alamat,
  disclaimer, label aksesibilitas) dibaca dari `config/site.php` lewat
  `app/Support/SiteLayout.php`, lalu dikirim ke React sebagai shared prop `site`.
- Isi sementara hero Beranda ada di `config/content.php`.
- Di Milestone 2 kedua file ini pindah ke **Pengaturan Global**, **Menu Navigasi**, dan
  **Beranda** di admin (`spatie/laravel-settings`), tanpa mengubah komponen React.

Teks dalam kurung siku `[...]` adalah data dummy yang **wajib diganti** dengan data asli.
Nomor WhatsApp masih kosong. Selama kosong, tombol WA/"Hubungi Marketing" diarahkan ke
`/kontak`. Hotline placeholder tampil sebagai teks tanpa link `tel:`.

## Desain

- Token warna, radius, dan breakpoint ada di `resources/css/app.css` (`@theme`), dipakai
  sebagai utility Tailwind: `bg-ground`, `bg-sand`, `text-ink`, `bg-forest`, `text-body`,
  `text-caption`, `border-line`, `bg-terracotta`, `hover:bg-terracotta-hover`, `text-peach`,
  `rounded-card`, `rounded-section`, `font-display`, `container-site`, dst.
- Font **Fraunces** (judul) + **Plus Jakarta Sans** (body) di-self-host dari `resources/fonts/`
  (variable font, subset latin, lisensi OFL). `laravel-vite-plugin` membuat `@font-face`
  dengan `font-display: swap`, preload kedua file, dan fallback berukuran metrik yang sama
  (lewat `fontaine`) supaya layout tidak bergeser. Tidak ada request ke Google Fonts.
- Layout global (`resources/js/layouts/site-layout.tsx`): header desktop (≥ 1280px) dengan
  menu, hotline, dan CTA; header mobile/tablet dengan ikon WA + hamburger → drawer menu.
  Drawer tetap ada di HTML SSR (link bisa di-crawl), ditutup dengan `inert`, dan
  mendukung tombol Esc, klik overlay, dan pengembalian fokus.
- Halaman Beranda baru berisi Hero untuk menguji layout. Section lain dibangun di Milestone 3.

## Struktur Penting

- `routes/web.php` — route halaman publik
- `app/Http/Controllers/` — controller yang mengirim data halaman ke Inertia
- `app/Http/Middleware/HandleInertiaRequests.php` — shared prop `site` (layout global)
- `app/Support/SiteLayout.php` — susun data header/footer/drawer + normalisasi nomor WA (62…)
- `config/site.php`, `config/content.php` — isi awal layout & Beranda (sementara, lihat di atas)
- `resources/js/app.tsx` — entry Inertia, sekaligus entry SSR (lewat `@inertiajs/vite`)
- `resources/js/pages/` — halaman React
- `resources/js/layouts/`, `resources/js/components/site/` — layout global & komponen
- `resources/css/app.css` — token desain Tailwind v4
- `resources/fonts/` — file font self-host
- `app/Providers/Filament/AdminPanelProvider.php` — panel admin `/admin`
- `app/Filament/` — resource & halaman admin (mulai Milestone 2)
- `docs/` — brief & referensi desain

## Deploy ke VPS (ringkas)

1. `composer install --no-dev --optimize-autoloader`
2. `npm ci && npm run build`
3. `.env` production: `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://…`, lalu aktifkan
   blok MySQL (`DB_CONNECTION=mysql` + kredensial) dan Redis (`CACHE_STORE`, `SESSION_DRIVER`,
   `QUEUE_CONNECTION`) yang sudah disiapkan di `.env.example`
4. `php artisan migrate --force`
5. `php artisan storage:link && php artisan optimize && php artisan filament:optimize`
6. Jalankan proses SSR dan queue worker tetap hidup pakai **Supervisor**. Keduanya proses
   terpisah dari PHP-FPM, jangan lupa masuk checklist deployment. Contoh
   `/etc/supervisor/conf.d/arunika.conf`:

    ```ini
    [program:arunika-ssr]
    command=php /var/www/arunika/artisan inertia:start-ssr
    autostart=true
    autorestart=true
    user=www-data
    redirect_stderr=true
    stdout_logfile=/var/www/arunika/storage/logs/ssr.log

    [program:arunika-queue]
    command=php /var/www/arunika/artisan queue:work --sleep=3 --tries=3 --max-time=3600
    autostart=true
    autorestart=true
    user=www-data
    redirect_stderr=true
    stdout_logfile=/var/www/arunika/storage/logs/queue.log
    ```

    Setelah deploy ulang: `php artisan inertia:stop-ssr` (Supervisor menyalakannya lagi dengan
    bundle baru) dan `php artisan queue:restart`.

7. Scheduler: cron `* * * * * cd /var/www/arunika && php artisan schedule:run >> /dev/null 2>&1`
8. Setup SSL (Let's Encrypt) + HTTPS redirect.
