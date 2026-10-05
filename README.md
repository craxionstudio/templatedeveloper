# Website Developer Perumahan — Arunika Land (nama sementara)

Website resmi developer perumahan (satu kawasan), fokus lead generation lewat WhatsApp dan
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
| 4   | WhatsApp & tracking                                                                                             | ✅ Selesai |
| 5   | Technical SEO                                                                                                   | ✅ Selesai |
| 6   | Performa                                                                                                        | ✅ Selesai |
| 7   | QA                                                                                                              | ✅ Selesai |

## Requirement

- **PHP 8.3.6 atau lebih baru** (diuji di PHP 8.3.6 bawaan Ubuntu 24.04 dan PHP 8.4). `composer.json` mengunci
  platform ke PHP 8.3.6 (`config.platform.php`), jadi `composer install` / `composer update` hanya memilih versi
  library yang jalan di 8.3. **Jangan** pakai `--ignore-platform-reqs`.
- Composer 2
- Node.js 22+ dan npm (build aset & proses SSR)
- Production: MySQL 8 (+ `mysqldump` untuk backup), Redis (opsional, fallback `database`), Supervisor

**Extension PHP wajib**

| Extension                                                                                                                   | Untuk                                                 |
| --------------------------------------------------------------------------------------------------------------------------- | ----------------------------------------------------- |
| `mbstring`, `intl`, `ctype`, `tokenizer`, `openssl`, `fileinfo`, `xml`, `dom`, `simplexml`, `xmlreader`, `xmlwriter`, `zip` | Laravel, Filament, ekspor XLSX, sitemap/RSS           |
| `pdo_mysql`                                                                                                                 | database production (MySQL 8)                         |
| `pdo_sqlite`                                                                                                                | database lokal & test                                 |
| `gd` (atau `imagick`)                                                                                                       | foto: resize + varian WebP/AVIF                       |
| `exif`                                                                                                                      | membaca orientasi foto yang diunggah                  |
| `curl`                                                                                                                      | HTTP keluar (backup, notifikasi gagal backup)         |
| `bcmath`                                                                                                                    | perhitungan angka besar (harga) oleh beberapa library |

Disarankan: `opcache` (production), `pcntl` (queue worker berhenti dengan rapi), `redis` (kalau memakai Redis).

Cek extension yang belum terpasang:

```bash
php -v
php -m
for e in bcmath ctype curl dom exif fileinfo gd intl mbstring openssl pdo_mysql pdo_sqlite simplexml tokenizer xml xmlreader xmlwriter zip; do
    php -m | grep -qix "$e" || echo "BELUM ADA: $e"
done
composer check-platform-reqs    # setelah composer install: semua harus "success"
```

Pasang di Ubuntu 24.04 (PHP 8.3 bawaan; `exif`, `fileinfo`, `ctype`, `tokenizer` sudah termasuk `php8.3-common`):

```bash
sudo apt install php8.3-cli php8.3-fpm php8.3-mbstring php8.3-xml php8.3-curl php8.3-zip php8.3-intl \
    php8.3-gd php8.3-sqlite3 php8.3-mysql php8.3-bcmath
```

> **AVIF:** GD bawaan Ubuntu untuk PHP 8.3 **tidak** mendukung AVIF. Di server seperti itu aplikasi otomatis hanya
> membuat varian **WebP** (browser tetap mendapat WebP + gambar asli). AVIF aktif sendiri kalau GD punya dukungan AVIF
> (`php -r 'var_dump(gd_info()["AVIF Support"] ?? false);'` → `true`) atau memakai Imagick yang mendukung AVIF
> (`IMAGE_DRIVER=imagick`, cek `php -r 'print_r(Imagick::queryFormats("AVIF"));'`).

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

Seeder membuat 1 akun admin (lokal: password **password**; di production password diambil dari
`SEED_ADMIN_PASSWORD` atau dibuat acak dan dicetak sekali di terminal). Ganti email & password setelah login pertama.
Hanya ada satu role, **Admin**, dengan akses ke semua menu (akun tambahan dibuat di Sistem → User):

| Email             | Role  | Akses      |
| ----------------- | ----- | ---------- |
| admin@example.com | Admin | Semua menu |

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

> **Queue — penting.** Pembuatan varian gambar (AVIF/WebP) berjalan lewat queue. `.env.example` memakai `QUEUE_CONNECTION=database`, jadi:
>
> - **Tes lokal tanpa worker:** set `QUEUE_CONNECTION=sync` di `.env` — semua job langsung dijalankan
>   saat request (upload foto jadi sedikit lebih lambat, tapi tidak ada yang tertunda).
> - **Pakai `database` atau `redis`:** jalankan worker di terminal terpisah (sudah termasuk di `composer dev`):
>
>     ```bash
>     php artisan queue:work
>     ```
>
>     Tanpa worker, job hanya menumpuk di tabel `jobs`: foto yang diunggah tetap memakai file asli
>     (tanpa AVIF/WebP).

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
    - **Bank Benefit** — daftar benefit tetap (Tanpa DP, Free BPHTB, Free Kitchen Set, Diskon, …) per kategori
      (pembayaran / bonus unit / material / diskon), ikon, urutan (drag), aktif/nonaktif, dan jumlah cluster pemakainya.
      Benefit dipilih per cluster di tab **Promo & Benefit** (teks tampil opsional, maks 40 karakter, bisa diurutkan),
      atau sekaligus untuk banyak cluster lewat bulk action di tabel Cluster. Tanpa tanggal berakhir: benefit tampil
      selama dicentang. Tampil di Detail Rumah (section "Promo & Benefit" + tombol WA), chip di kartu (maks 3 + "+N"),
      badge "Promo" (kedua, setelah badge admin seperti "Baru"), filter `/properti?benefit=tanpa-dp` (satu benefit = halaman SEO sendiri), dan urutan "Promo".
- **Konten** — Fasilitas (kategori dibuat langsung dari form), Pengembangan Mendatang, Profil Developer. (Menu Promo lama disembunyikan sejak
  Bank Benefit; datanya belum dihapus.)
- **Artikel** — Artikel (rich text disanitasi, waktu baca otomatis, highlight; tag dibuat langsung dari form),
  Kategori, Penulis.
- **Pengaturan** (4 menu) — **Beranda**; **Properti** (listing, Detail Kawasan, Detail Rumah); **Halaman Lain**
  (Tentang Kami, Artikel, Fasilitas, Kebijakan Privasi); **Pengaturan Umum** (nomor & pesan WhatsApp,
  kontak di footer + link Google Maps, logo, GA4 Measurement ID, verifikasi Search Console). Hanya judul, subjudul, dan teks yang sering
  diubah yang tampil di admin; sisanya teks tetap di kode (`database/settings/defaults/*.php`, daftar field yang
  bisa diubah ada di `editable()` tiap kelas `App\Settings`). Tidak ada toggle "Tampilkan section": section
  tersembunyi otomatis kalau datanya kosong (teks contoh `[...]` dianggap kosong).
- **Form ringkas:** slug, meta title/description, gambar share, ringkasan, alt text foto, dan tahun launching
  terisi otomatis. Field SEO/teknis ada di section **Lanjutan** (tertutup). Form Cluster hanya mewajibkan nama,
  kawasan (atau "Cluster mandiri"), dan minimal 1 foto; ada aksi **Duplikat cluster** (tabel & halaman edit).
- **Sistem** — Menu Navigasi, Redirect, User.

Aturan admin yang berlaku di semua resource:

- **Alt text wajib** setiap kali mengunggah gambar; nama file otomatis diubah jadi slug dari alt text.
- **Slug otomatis** dari judul (bisa diedit). Kalau slug diubah, redirect 301 dari URL lama dibuat
  otomatis (lihat menu Redirect).
- Kolom **Properti di footer** otomatis berisi kawasan yang tampil di publik.

### Import data asli (BSD City)

Data properti asli ada di `docs/data/bsd-city-data.json`. Yang belum lengkap tercatat di `docs/data/BELUM-LENGKAP.md`,
dan per cluster ada di admin (tab **Internal**).

```bash
# Pertama kali: hapus data dummy properti (kawasan, cluster, tipe rumah), nonaktifkan konten contoh, lalu import
php artisan import:bsd-data --fresh

# Setelah JSON diperbarui: upsert per slug, aman diulang, tidak membuat duplikat
php artisan import:bsd-data
php artisan import:bsd-data path/ke/file-lain.json

# File update (tanggal launching, promo), setelah import:bsd-data. Upsert, aman diulang.
php artisan import:bsd-update docs/data/bsd-city-update-2.json
php artisan import:bsd-update docs/data/bsd-city-update-3.json
```

- `import:bsd-update`: tanggal launching per cluster (dasar urutan "Terbaru"; cluster yang baru punya tahun
  launching diisi 1 Januari) dan promo (upsert per judul, placement Detail, relasi ke cluster lewat `cluster_slugs`,
  `sumber` jadi catatan internal). Status publikasi dari file hanya dipakai saat promo dibuat, jadi promo yang sudah
  dipublikasikan admin tidak dimatikan lagi.
- `import:bsd-update` juga membaca benefit per cluster (Bank Benefit):
  `"benefits": [{"cluster_slug": "castilo-at-terravia", "benefit_slug": "diskon", "teks_tampil": "Diskon hingga 13%"}]`.
  Upsert per (cluster, benefit). Benefit yang diubah atau dilepas di admin setelah import sebelumnya, atau ditambahkan
  admin sendiri, tidak ditimpa dan tidak dibuat ulang.
- `import:bsd-update` (update 3) membaca `"cluster_tampilan": [{"slug": "…", "tampil_sebagai": "halaman"|"daftar"}]` dan
  `"kawasan_tampilan": [{"slug": "…", "punya_halaman": true|false}]` (format peta `slug → nilai` juga diterima).
  Cluster "daftar" tidak punya halaman sendiri: hanya nama di `/properti/cluster-lainnya` (per kawasan) dan di
  section "Cluster lain di kawasan ini"; URL lamanya 301 ke `/properti/cluster-lainnya#{slug-kawasan}`. Kawasan dengan
  `punya_halaman = false` tidak punya halaman detail, tidak tampil di daftar kawasan/footer/sitemap, dan URL lamanya
  301 ke grupnya di halaman itu.

- Yang diisi: Profil Lokasi, 23 kawasan, 144 cluster (kawasan kosong = cluster mandiri), tipe rumah, SEO tiap
  kawasan/cluster, dan SEO halaman (Beranda, Properti, Kawasan, Fasilitas, Artikel, Tentang Kami, serta pola
  judul Detail Kawasan & Detail Rumah).
- `--fresh` **tidak** menghapus user, artikel, fasilitas, atau settings. Konten contoh
  (fasilitas, pengembangan mendatang, artikel, promo) hanya dinonaktifkan (`is_published = false`).
  Di production, perintah ini minta konfirmasi (lewati dengan `--force`).
- Nilai `null` di JSON dibiarkan kosong, dan saat import ulang tidak menimpa isi yang sudah dilengkapi di admin.
  Nilai yang ada di JSON menimpa isi admin.
- Checklist "perlu dilengkapi" digabung: item yang sudah dicentang di admin tetap tercentang.
- Rentang (`"1000-1788"`, `"5-6"`) disimpan angka terkecil. `"3+1"` disimpan 3. `2.5` lantai disimpan 2. Nilai
  aslinya ditulis di catatan internal tipe rumah.
- Tipe tanpa nama (harga "mulai" tingkat cluster) disimpan tanpa nama. Di website, labelnya "Harga mulai Rp …".
- Catatan internal, checklist, prioritas, dan sisa unit hanya tampil di admin, tidak di website.
- Admin → Cluster: kolom **Kelengkapan** ("Belum lengkap (N)"), filter **Belum lengkap**, dan kolom **Prioritas**
  (1–10, bisa diurutkan).

Nomor WhatsApp global wajib diisi di Pengaturan Umum. Selama di database masih kosong, tombol WA tetap link
`https://wa.me/?text=…` (pengunjung memilih kontak sendiri), dan dashboard admin + ringkasan deploy menampilkan peringatan. Hotline placeholder tampil sebagai teks tanpa link `tel:`.

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
    | `/properti/cluster-lainnya`                                                    | `Pages/Properti/Lainnya.tsx`                            | layout sama dengan /properti                |
    | `/properti/{slug}` (+ `?tipe=`)                                                | `Pages/Cluster/Show.tsx`                                | 03                                          |
    | `/fasilitas`                                                                   | `Pages/Fasilitas/Index.tsx`                             | 04                                          |
    | `/artikel`, `/artikel/kategori/{slug}`                                         | `Pages/Artikel/Index.tsx`                               | 05                                          |
    | `/artikel/{slug}`                                                              | `Pages/Artikel/Show.tsx`                                | 06                                          |
    | `/tentang-kami`, `/kebijakan-privasi`                                          | `About`, `Privacy`                                      | tanpa desain, memakai pola section yang ada |
    | `/kontak` (dihapus Okt 2026)                                                   | 301 ke beranda; menu Kontak membuka WhatsApp            | info kontak di footer semua halaman         |
    | URL tidak dikenal                                                              | `Pages/Errors/NotFound.tsx` (status 404 asli, SSR)      | —                                           |

- Foto yang belum diunggah tampil sebagai placeholder bergaris dengan alt text (`components/site/picture.tsx`).
- Listing, filter, kategori, pagination, dan toggle Cluster/Kawasan adalah link biasa (SSR, bisa di-crawl).
  "Muat lagi" di mobile memakai `Inertia::scroll()`; listing yang difilter diberi `noindex`.

## Performa

Hasil audit Lighthouse **mobile** (Lighthouse 12, simulasi 4G lambat, `APP_ENV=production`, gzip/brotli
seperti nginx, foto masih placeholder) untuk 10 halaman utama: **Performance 94–97, Accessibility 100,
Best Practices 100, SEO 100**; LCP lab 2,3–2,6 dtk, CLS 0, TBT ≤ 80 ms.

- **Gambar:** setiap koleksi gambar punya konversi **WebP** (+ **AVIF** kalau GD/Imagick server mendukung, lihat
  Requirement) lebar 480/960/1600 px (tidak pernah diperbesar,
  lewat queue). Gambar dari settings halaman dibuatkan varian yang sama di `storage/app/public/_variants`
  setelah settings disimpan. Frontend (`components/site/picture.tsx`) merender `<picture>` dengan `srcset`/`sizes`,
  `width`/`height` asli, `loading="lazy"`; gambar LCP (hero, cover, foto galeri pertama) memakai
  `fetchpriority="high"`, `loading="eager"`, dan di-preload. Selama varian belum jadi, gambar asli dipakai.
  Untuk membuat varian yang terlewat (mis. setelah impor): `php artisan images:variants`.
- **Peta:** facade (gambar/placeholder + tombol "Buka peta"), iframe Google Maps baru dimuat saat diklik.
  Video galeri berupa link (tidak ada video autoplay).
- **JavaScript:** satu chunk per halaman (import dinamis dari resolver Inertia); chunk bersama = React + Inertia.
  GA4 (gtag.js) dimuat setelah `load` + browser idle.
- **Cache halaman publik** (`App\Http\Middleware\CachePublicPages`): HTML awal hasil SSR untuk tamu di-cache
  (default aktif di production, `PAGE_CACHE_ENABLED`, TTL `PAGE_CACHE_TTL` = 3600 dtk). Dibuang otomatis setiap
  konten, media, atau settings berubah (versi konten naik), dan setiap build aset baru. Tidak di-cache: request
  Inertia (JSON), admin, pratinjau, user login, dan request yang membawa error/flash.
  Cookie per pengunjung (session, XSRF, atribusi UTM) tetap dikirim. Header `X-Page-Cache: HIT|MISS` untuk cek.
  Data layout global (header/footer) juga di-cache per versi konten. Kalau mengubah data langsung di database
  (bukan lewat admin): `php artisan cache:clear`.
- **Header:** HSTS (`max-age=31536000`) di production lewat HTTPS. Aset `/build/*` ber-hash → cache 1 tahun
  `immutable` (sudah ada aturan di `public/.htaccess` untuk Apache; nginx lihat bagian Deploy).

## Technical SEO

- **Head per halaman** (`App\Support\PageMeta` → `components/site/page-head.tsx`, ikut di HTML SSR): title
  (pola `{Judul} | {Brand}`, bisa di-override admin), description (±155 karakter), `robots`, canonical absolut,
  Open Graph + Twitter Card, dan JSON-LD. Cek tanpa JavaScript: `curl -s http://localhost:8000/properti/vega-garden`.
- **Canonical & robots:** canonical selalu tanpa query (`?tipe=`, filter, urut, pencarian), kecuali pagination
  (`?page=2` self-canonical). Filter/urut/pencarian dan pratinjau = `noindex, follow`.
  **Indeks Google diatur `SITE_INDEXABLE`** (terpisah dari `APP_ENV`, bawaan `false`): selama `false` semua halaman
  `noindex, nofollow` + header `X-Robots-Tag` dan robots.txt `Disallow: /` (domain sementara, lokal, staging).
  Ubah ke `true` di `.env` server hanya saat domain final siap; deploy tidak pernah mengubahnya ke `true`.
  Statusnya tampil di Pengaturan Umum → Google.
- **OG image:** gambar dari tab SEO > foto konten (galeri cluster, hero kawasan, cover artikel) > OG default di
  Pengaturan Global → SEO default > gambar default per tipe halaman di `public/og/*.png` (1200×630).
  Gambar bawaan dibuat ulang dengan `npm i --no-save playwright && node scripts/og-images.mjs` (teks di
  dalam script; ubah kalau nama brand berubah).
- **JSON-LD** (`App\Support\StructuredData`, spatie/schema-org): `Organization` + `WebSite` di semua halaman,
  `RealEstateAgent` (kantor pemasaran: alamat, telepon, email, jam buka) di semua halaman, `BreadcrumbList` di semua halaman selain
  Beranda, `ItemList` di kedua tampilan listing & Detail Kawasan, `Place` di Detail Kawasan, `Residence` berisi
  tiap tipe (`Product` + `SingleFamilyResidence`, luas, kamar, `Offer` IDR) di Detail Rumah, `BlogPosting` di artikel.
- **Sitemap:** `/sitemap.xml` (index) → `sitemap-pages.xml`, `sitemap-properti.xml`, `sitemap-artikel.xml`, dengan
  `lastmod` dan image sitemap. Hanya konten yang dipublikasikan dan tidak `noindex`. XML di-cache dan dibuang otomatis
  saat konten/settings berubah; `php artisan sitemap:refresh` jalan harian (scheduler).
- **robots.txt** dinamis. `SITE_INDEXABLE=true`: blok `/admin` dan `/livewire`, cantumkan sitemap. URL
  filter/urutan/pencarian **tidak** diblok supaya Google bisa membaca `noindex, follow` + canonical-nya.
  `SITE_INDEXABLE=false`: `Disallow: /`. **RSS:** `/artikel/feed.xml` (+ `<link rel="alternate">` di head).
- **URL & status code** (`App\Http\Middleware\RedirectManager`, sebelum routing): http→https dan www ↔ non-www
  mengikuti `APP_URL` (production), trailing slash & huruf kapital → 301, Redirect Manager (Sistem → Redirect: 301/302/410,
  hit counter), slug lama → 301 otomatis. Konten dihapus → 410, tidak ada → 404 custom (status asli).
- **Pratinjau** artikel, cluster, dan kawasan (termasuk yang belum dipublikasikan): tombol "Pratinjau" di form admin,
  URL bertanda tangan 1 jam, hanya Admin, selalu `noindex`.

## WhatsApp & Tracking

- **Tanpa form lead** (keputusan 5 Okt 2026): semua ajakan menghubungi membuka WhatsApp dengan pesan otomatis
  (`App\Support\WhatsApp`). Satu nomor: nomor WA global di Pengaturan Umum (wajib). Selalu link
  `https://wa.me/{nomor}?text={pesan}` (URL-encode, baris baru `%0A`). Template per konteks (detail cluster, promo,
  jadwal survey, halaman kawasan, umum) di Pengaturan Umum dengan placeholder `{nama_cluster}`, `{nama_kawasan}`,
  `{judul_halaman}`, `{link_halaman}` (URL halaman tanpa query string); di admin ada contoh hasil di bawah tiap field.
  Tombol WhatsApp melayang di semua halaman (mobile & desktop). `/terima-kasih` diarahkan 301 ke beranda.
- Tabel `leads` lama diekspor ke `storage/app/backup/leads-{tanggal}.csv` lalu dihapus (migrasi `2026_10_05_200000`).
- **Analytics:** GA4 langsung lewat `gtag.js` (tanpa GTM, tanpa Meta Pixel/CAPI), hanya kalau Measurement ID diisi.
  Konversi utama `click_whatsapp` (parameter `cluster`, `posisi_tombol`, `halaman`; transport beacon).
  Panduan lengkap: **`docs/TRACKING.md`**.
- **Search Console:** meta tag verifikasi dari Pengaturan Umum dipasang di `<head>`.

## Keamanan & QA

- **Security headers** di semua respons (termasuk admin): `X-Content-Type-Options`, `X-Frame-Options: SAMEORIGIN`,
  `Referrer-Policy`, `Permissions-Policy`; HSTS di production lewat HTTPS.
- **CSP** halaman publik (`App\Http\Middleware\SecurityHeaders`): nonce per request + `'strict-dynamic'`, jadi hanya
  bundle Vite dan loader GA4 (bertanda nonce) yang boleh jalan, dan script yang mereka muat (gtag.js, chunk
  halaman) ikut dipercaya. Default aktif di semua environment selain `local` (`CSP_ENABLED`).
  Admin Filament tidak diberi CSP. `connect-src`/`img-src`/`frame-src` memakai daftar domain bawaan (GA4, Google
  Maps, YouTube) + disk file publik (otomatis).
- **Rich text** disanitasi saat disimpan dan saat dikirim ke browser (`App\Support\RichText`, hanya tag yang diizinkan).
- **Backup** harian database + file unggahan (`spatie/laravel-backup`): `backup:run` 01.30, `backup:clean` 01.00,
  `backup:monitor` 09.00. Tujuan `BACKUP_DISKS` (default `local` → `storage/app/private`), email hanya kalau gagal
  (`BACKUP_NOTIFICATION_EMAIL`). MySQL butuh `mysqldump` di server. Tambahkan disk di luar server
  (`BACKUP_DISKS=local,s3`, mis. Cloudflare R2 / Backblaze B2 — env di `docs/CHECKLIST-LAUNCH.md`).
- **Cek SSR semua URL sitemap** (status, satu H1, canonical, robots, og:image, JSON-LD valid):

    ```bash
    php artisan qa:pages                          # memakai APP_URL
    php artisan qa:pages --base=https://domain-anda.com
    ```

- Laporan QA lengkap (test, SSR, structured data, aksesibilitas, Lighthouse, keamanan): **`docs/QA.md`**.
- Checklist sebelum go-live: **`docs/CHECKLIST-LAUNCH.md`**.

## Struktur Penting

- `routes/web.php` — route halaman publik
- `app/Http/Controllers/` — controller yang mengirim data halaman ke Inertia
- `app/Support/WhatsApp.php` — nomor & template pesan WhatsApp per konteks
- `app/Http/Middleware/{SecurityHeaders,CachePublicPages}.php` — security headers/CSP & cache halaman publik
- `app/Console/Commands/CheckPages.php` — `php artisan qa:pages` (cek SSR semua URL sitemap)
- `docs/` — brief, perubahan (`CHANGES.md`), data dummy, tracking, QA, checklist go-live
- `app/Support/{PageMeta,StructuredData,Sitemaps}.php`, `app/Http/Middleware/RedirectManager.php`, `app/Http/Controllers/SeoFileController.php` — meta/OG/canonical, JSON-LD, sitemap, redirect, robots, RSS
- `app/Support/Tracking.php`, `resources/views/partials/tracking-head.blade.php` — GA4 & meta verifikasi
- `resources/js/lib/analytics.ts` — event GA4 (`click_whatsapp` dkk.)
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
    > **Penting — `APP_URL` harus persis domain final:** pakai `https://`, pilih **salah satu** www atau non-www
    > (mis. `https://arunikaland.co.id` atau `https://www.arunikaland.co.id`), tanpa garis miring di akhir.
    > Di production semua request http atau varian www/non-www yang lain di-301 ke `APP_URL`, dan nilai ini dipakai
    > untuk canonical, `og:url`, sitemap, RSS, dan JSON-LD. Salah isi = seluruh URL kanonik salah.
4. `php artisan migrate --force`; deploy **pertama** saja: `php artisan db:seed --force` (isi awal settings, contoh
   konten, 3 akun admin; catat password yang dicetak, atau set `SEED_ADMIN_PASSWORD` dulu)
5. Script deploy otomatis (`.github/workflows/deploy.yml`) juga mengimpor data asli saat file `docs/data/*.json`
   berubah (dicatat di `storage/app/import-markers/`), didahului `php artisan backup:run --only-db
--disable-notifications`: `import:bsd-data … --fresh --force --no-interaction` hanya di import pertama, setelah itu
   tanpa `--fresh`; `import:bsd-update … --force --no-interaction`. Keduanya tidak pernah bertanya dengan `--force`.
6. `php artisan storage:link && php artisan optimize && php artisan filament:optimize`
7. **Cron, queue, dan SSR dipasang otomatis oleh script deploy** (tanpa SSH manual, idempotent, hanya menyentuh
   milik aplikasi ini):
    - `scripts/server/ensure-cron.sh`: menambahkan baris cron `schedule:run` untuk folder aplikasi ini kalau belum
      ada. Baris cron aplikasi lain tidak diubah. Scheduler menyalakan `queue:work --stop-when-empty` tiap menit
      (konversi foto WebP/AVIF), plus sitemap & backup. Saat deploy, job yang tertunda diproses sekali
      (`queue:work --force`, karena aplikasi masih maintenance).
    - `scripts/server/ensure-ssr.sh`: server SSR Inertia (`127.0.0.1:13714`) lewat **Supervisor** kalau ada atau bisa
      dipasang (root / sudo tanpa password), dengan program `ssr-<nama-folder>` di
      `/etc/supervisor/conf.d/ssr-<nama-folder>.conf` (autostart, autorestart). Tanpa root: cron `@reboot` + penjaga
      tiap menit (`scripts/server/ssr-watchdog.sh`). Setiap deploy SSR di-restart dengan bundle baru. Kalau port
      dipakai aplikasi lain, prosesnya tidak disentuh dan deploy memberi peringatan (ganti port lewat
      `INERTIA_SSR_URL` di `.env` + `INERTIA_SSR_PORT` saat build).
    - Di akhir deploy: `php artisan ssr:check /properti` (HTML harus dirender server dan berisi H1) dan cek heartbeat
      scheduler; hasilnya tampil sebagai notice/peringatan di ringkasan GitHub Actions.
    - Halaman yang gagal dirender server tidak disimpan di cache halaman.

8. **nginx** (HTTP/2, kompresi, cache aset). Contoh di dalam blok `server { listen 443 ssl http2; … }`:

    ```nginx
    gzip on;
    gzip_types text/plain text/css text/xml application/javascript application/json application/xml application/rss+xml image/svg+xml;
    # brotli on; brotli_types …;   # kalau modul ngx_brotli terpasang

    location /build/ {
        add_header Cache-Control "public, max-age=31536000, immutable";
        try_files $uri =404;
    }

    location ~ ^/(storage|og|fonts)/ {
        add_header Cache-Control "public, max-age=2592000";
        try_files $uri =404;
    }

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    ```

    Di belakang Cloudflare: aktifkan Brotli, HTTP/3, "Always Use HTTPS"; jangan cache HTML di edge tanpa
    aturan bypass cookie (halaman sudah di-cache di aplikasi).

9. Scheduler: baris crontab `* * * * * cd <folder aplikasi> && php artisan schedule:run >> /dev/null 2>&1` dipasang
   otomatis oleh deploy (langkah 7). Isinya: queue worker tiap menit, `sitemap:refresh` 03.00, backup 01.30,
   pembersihan & monitor backup.
10. Setup SSL (Let's Encrypt) + HTTPS redirect.
