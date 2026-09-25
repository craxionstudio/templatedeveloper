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
| 2   | Model & admin (migrasi, seeder dummy, Filament resource, settings per halaman)                                  | ✅ Selesai |
| 3   | Halaman publik sesuai desain                                                                                    | ✅ Selesai |
| 4   | Lead & tracking                                                                                                 | ✅ Selesai |
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

Seeder membuat 3 akun admin (password semua **password**, ganti setelah login pertama):

| Email                 | Role         | Akses                                                                            |
| --------------------- | ------------ | -------------------------------------------------------------------------------- |
| admin@example.com     | Super Admin  | Semua menu, termasuk User & Role                                                 |
| konten@example.com    | Admin Konten | Properti, Konten, Artikel, Pengaturan Halaman, Pengaturan Global, Menu, Redirect |
| marketing@example.com | Marketing    | Lead (baca, ubah status, ekspor; tidak bisa hapus) & Newsletter                  |

Data dummy (ikuti desain, teks dalam `[...]` wajib diganti):

- 1 Profil Lokasi (Kota Arunika, Serpong) dan 1 Profil Developer
- 3 kawasan: Arunika Garden (Vega Garden, Lyra Residence, Orion Park), Arunika Hills
  (Kirana Hills, Nara Village), Arunika Lakeside (Sora Terrace, Sora Terrace II)
- 2 cluster mandiri: Kalea Townhouse, Hana Residence — total 9 cluster dan 20 tipe rumah
- 9 fasilitas (6 kategori), 4 pengembangan mendatang, 9 artikel di 5 kategori, 2 promo
- Isi awal semua Pengaturan Halaman dibuat oleh migrasi settings (`database/settings/`),
  sumbernya `database/settings/defaults/*.php`

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
Kalau field teks di settings dikosongkan, halaman memakai isi awal dari
`database/settings/defaults/` (tidak pernah menampilkan string kosong).

Login ke `/admin`, lalu isi lewat menu:

- **Properti**
    - **Kawasan** — nama, ringkasan kartu, deskripsi, luas, foto hero + galeri, fasilitas kawasan,
      brosur, peta, tab SEO. Di bawah form ada daftar cluster di kawasan itu (masukkan cluster
      mandiri ke kawasan, atau "Jadikan mandiri"). Jumlah cluster & harga mulai dihitung otomatis.
      Kawasan tampil di publik hanya kalau punya minimal 1 cluster yang dipublikasikan.
    - **Cluster** — kawasan (kosong = cluster mandiri), jenis bangunan, tipe properti, badge,
      status, deskripsi, harga (booking fee & catatan), spesifikasi material, galeri, video/360°,
      brosur & pricelist, marketing, promo, tab SEO. Di bawah form: **Tipe rumah** (1 sampai n,
      urutkan dengan drag). Rentang harga/LT/KT dan cicilan mulai di kartu cluster dihitung dari
      tipe yang dipublikasikan. Slug `kawasan` ditolak karena bentrok dengan `/properti/kawasan`.
    - **Profil Lokasi** — kota mandiri: deskripsi, foto aerial, peta, poin keunggulan wilayah.
- **Konten** — Promo (banner Beranda / "Promo rumah ini" di Detail Rumah, kedaluwarsa otomatis
  tidak tampil), Fasilitas + Kategori, Pengembangan Mendatang, Profil Developer.
- **Artikel** — Artikel (rich text disanitasi, waktu baca otomatis, highlight, tab SEO),
  Kategori, Tag, Penulis.
- **Pengaturan Halaman** — satu menu per halaman: Beranda, Properti (dipakai `/properti` dan
  `/properti/kawasan`), Detail Kawasan, Detail Rumah, Fasilitas, Artikel, Detail Artikel,
  Tentang Kami, Kontak, Terima Kasih, Kebijakan Privasi. Tiap halaman: satu tab per section
  (toggle "Tampilkan section"), CTA, dan tab SEO dengan preview Google. Tombol "Lihat halaman"
  di kanan atas.
- **Marketing** — Lead (ubah status & penanggung jawab, filter tanggal/status/cluster/UTM,
  ekspor CSV/XLSX sesuai filter), Newsletter.
- **Sistem** — Pengaturan Global (identitas, kontak & nomor WA, header, footer, CTA global,
  mobile, tracking, label umum, SEO default), Menu Navigasi, Redirect, User & Role.

Aturan admin yang berlaku di semua resource:

- **Alt text wajib** setiap kali mengunggah gambar; nama file otomatis diubah jadi slug dari alt text.
- **Slug otomatis** dari judul (bisa diedit). Kalau slug diubah, redirect 301 dari URL lama dibuat
  otomatis (lihat menu Redirect).
- Kolom **Properti di footer** otomatis berisi kawasan yang tampil di publik.

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
- Halaman publik (desain `docs/design/`, desktop 1440 & mobile 390):

    | URL                                                                            | Halaman (React)                                         | Desain                                      |
    | ------------------------------------------------------------------------------ | ------------------------------------------------------- | ------------------------------------------- |
    | `/`                                                                            | `Pages/Home.tsx` (Listing Produk memakai kartu cluster) | 01                                          |
    | `/properti` (+ filter `?kawasan=`, `tipe`, `kamar`, `harga`, `status`, `urut`) | `Pages/Properti/Index.tsx`                              | 02a                                         |
    | `/properti/kawasan`                                                            | `Pages/Properti/Kawasan.tsx`                            | 02b                                         |
    | `/properti/kawasan/{slug}`                                                     | `Pages/Kawasan/Show.tsx`                                | 02c                                         |
    | `/properti/{slug}` (+ `?tipe=`)                                                | `Pages/Cluster/Show.tsx`                                | 03                                          |
    | `/fasilitas`                                                                   | `Pages/Fasilitas/Index.tsx`                             | 04                                          |
    | `/artikel`, `/artikel/kategori/{slug}`                                         | `Pages/Artikel/Index.tsx`                               | 05                                          |
    | `/artikel/{slug}`                                                              | `Pages/Artikel/Show.tsx`                                | 06                                          |
    | `/tentang-kami`, `/kontak`, `/kebijakan-privasi`, `/terima-kasih`              | `About`, `Contact`, `Privacy`, `ThankYou`               | tanpa desain, memakai pola section yang ada |
    | URL tidak dikenal                                                              | `Pages/Errors/NotFound.tsx` (status 404 asli, SSR)      | —                                           |

- Foto yang belum diunggah tampil sebagai placeholder bergaris dengan alt text (`components/site/picture.tsx`).
- Listing, filter, kategori, pagination, dan toggle Cluster/Kawasan adalah link biasa (SSR, bisa di-crawl).
  "Muat lagi" di mobile memakai `Inertia::scroll()`; listing yang difilter diberi `noindex`.

## Lead & Tracking

- **Form lead** (Detail Rumah sidebar/inline, modal "Jadwalkan Kunjungan/Survey", halaman Kontak) → `POST /lead`
  → redirect `/terima-kasih`. Nomor WA dinormalisasi ke `62…` (`App\Support\Phone`), persetujuan
  Kebijakan Privasi wajib. Newsletter → `POST /newsletter` (email saja, tanpa duplikat).
- **Anti-spam:** honeypot (field `website`, bot dijawab "sukses" tanpa disimpan) + rate limit per IP
  (lead 5/menit & 30/hari, newsletter 5/menit & 20/hari) + Cloudflare Turnstile. Turnstile aktif hanya
  kalau site key **dan** secret key diisi; kosong (lokal/dev) = dilewati.
- **Atribusi:** UTM, `fbclid`, `gclid`, landing page pertama, dan referrer ditangkap di kunjungan pertama
  ke cookie `arunika_attribution` (30 hari) lalu disalin ke lead. Landing page & referrer = kunjungan
  pertama; UTM & click ID diperbarui kalau pengunjung datang lagi lewat kampanye baru.
- **Notifikasi:** email ke satu/lebih alamat + webhook opsional (POST JSON), keduanya lewat queue.
  Pengaturan Global → Notifikasi lead.
- **Analytics:** GTM **atau** GA4 langsung + Meta Pixel, ID dari Pengaturan Global → Tracking & verifikasi.
  Script dimuat setelah halaman selesai dimuat & browser idle; antrean event dibuat lebih dulu supaya
  tidak ada event yang hilang. Event: `generate_lead` (di `/terima-kasih`, dengan cluster, tipe, posisi
  form, `event_id`), `click_whatsapp`, `click_phone`, `download_brochure`, `download_pricelist`,
  `view_listing`, `select_house_type`, serta `virtual_page_view` (GTM) / `page_view` (GA4) per navigasi.
  Pixel: `PageView`, `Lead`, `Contact`, `ViewContent`.
- **Meta Conversions API:** event `Lead` dari server (job `SendMetaLeadEvent`) dengan `event_id` yang sama
  dengan Pixel untuk deduplikasi. Nomor WA, email, dan nama di-hash SHA-256 sesuai spesifikasi Meta;
  `fbp`/`fbc`, IP, dan user agent ikut dikirim untuk pencocokan. Access token disimpan terenkripsi dan
  tidak ditampilkan ulang; token kosong = CAPI dilewati tanpa error. Uji coba: isi "Test event code".
- Konversi di `/terima-kasih` hanya dikirim sekali tepat setelah submit (flash session), jadi refresh
  tidak menghitung dua kali.

## Struktur Penting

- `routes/web.php` — route halaman publik
- `app/Http/Controllers/` — controller yang mengirim data halaman ke Inertia
- `app/Http/Controllers/LeadController.php`, `app/Http/Requests/` — simpan lead & validasi anti-spam
- `app/Services/{MetaConversions,Turnstile}.php`, `app/Jobs/`, `app/Notifications/` — CAPI, Turnstile, webhook, email lead
- `app/Support/{Attribution,Tracking,Secret,Phone}.php` — cookie UTM, ID tracking, enkripsi rahasia, normalisasi WA
- `resources/js/lib/analytics.ts`, `resources/js/components/lead/` — event analytics & form lead
- `app/Presenters/` — bentuk data kartu (cluster, kawasan, artikel, fasilitas, gambar) untuk React
- `app/Support/{PageMeta,Breadcrumbs,Cta}.php` — meta halaman, breadcrumb, dan CTA per halaman
- `app/Http/Middleware/HandleInertiaRequests.php` — shared prop `site` (layout global)
- `app/Support/SiteLayout.php` — susun data header/footer/drawer + normalisasi nomor WA (62…)
- `app/Models/` — Kawasan, Cluster, HouseType, dst. (`Cluster::refreshAggregates()` menghitung rentang kartu)
- `app/Settings/` — satu settings class per halaman (`spatie/laravel-settings`)
- `database/settings/defaults/` — isi awal & fallback semua settings halaman
- `app/Support/AdminAccess.php` — hak akses per role (dipasang lewat `Gate::before`)
- `app/Support/Rupiah.php` — format "Rp 1,6 M", "Rp 850 jt – 1,1 M"
- `resources/js/app.tsx` — entry Inertia, sekaligus entry SSR (lewat `@inertiajs/vite`)
- `resources/js/Pages/` — halaman React
- `resources/js/layouts/`, `resources/js/components/site/` — layout global & komponen
- `resources/css/app.css` — token desain Tailwind v4
- `resources/fonts/` — file font self-host
- `app/Providers/Filament/AdminPanelProvider.php` — panel admin `/admin`
- `app/Filament/Resources/` — resource admin; `app/Filament/Pages/Settings/` — settings per halaman
- `app/Filament/Forms/` — komponen form reusable (slug + redirect, gambar + alt, galeri, tab SEO, section)
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
