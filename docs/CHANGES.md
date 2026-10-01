# Catatan Perubahan Brief

File ini mencatat setiap revisi brief setelah pekerjaan dimulai. Revisi terbaru di paling atas. `docs/BRIEF.md` selalu versi lengkap yang berlaku. File ini hanya menjelaskan **apa yang berubah dan apa dampaknya ke kode yang sudah ada**.

---

## Revisi 2 — 24 Sep 2026: model Kawasan

### Ringkasan

Dulu: satu lokasi, cluster langsung di bawahnya, tanpa pengelompokan.
Sekarang: **Kawasan (opsional) → Cluster → Tipe rumah**. Sebagian besar cluster masuk ke sebuah kawasan, tapi ada juga **cluster mandiri** (`kawasan_id = null`). Tetap satu lokasi, jadi tetap tidak ada filter kota/wilayah.

### Yang berubah

| Area | Perubahan | Bagian brief |
|---|---|---|
| Data | Tabel baru `kawasans`. `clusters.kawasan_id` nullable (FK, `nullOnDelete`). Kolom baru di `clusters`: jenis bangunan, tipe properti. Kolom baru di `house_types`: kamar tidur tambahan, `sort_order`, `is_published`, slug unik per cluster. | 5 |
| URL | Baru: `/properti/kawasan` (tampilan Kawasan) dan `/properti/kawasan/{slug}` (Detail Kawasan). `/properti` tetap tampilan Cluster dan dapat filter `?kawasan=`. Route kawasan didaftarkan sebelum `/properti/{slug-cluster}`; slug cluster `kawasan` ditolak. | 3 |
| Halaman | Baru: Produk Listing tampilan Kawasan, Detail Kawasan. Diubah: Produk Listing (toggle Cluster/Kawasan, filter kawasan, kartu cluster baru), Detail Rumah (breadcrumb + label kawasan, jumlah tab tipe dinamis), Home (Listing Produk pakai kartu cluster). | 4 |
| Komponen | **Kartu cluster** baru (reusable), menggantikan kartu per tipe. Kartu kawasan baru. Toggle tampilan listing. | 4 |
| Admin | Grup navigasi baru **Properti**: resource Kawasan, Cluster (select kawasan opsional), Profil Lokasi. Settings page baru `KawasanDetailPageSettings`; `ListingPageSettings` ditambah tab toggle & tampilan Kawasan. | 7, 7A |
| SEO | JSON-LD `Place` untuk Detail Kawasan, `ItemList` untuk kedua tampilan listing, sitemap memuat kawasan. | 8.3, 8.4 |
| Seeder | 3 kawasan, 9 cluster (2 di antaranya mandiri), 20 tipe. Detail di bagian 10. | 10 |
| Desain | `docs/design/` diganti: 8 halaman (sebelumnya 6). | README desain |

### File desain

- Baru: `02a-properti-cluster`, `02b-properti-kawasan`, `02c-detail-kawasan` (desktop + mobile + screenshot).
- Diperbarui: `03-detail-rumah` (breadcrumb kawasan, Vega Garden sekarang 3 tipe).
- **Dihapus:** `desktop/02-listing-properti.html`, `mobile/02-listing-properti.html`, `screenshots/*-02-listing-properti.png`. Kalau file ini masih ada di repo, hapus.
- Font sekarang ada di `docs/design/fonts/`, jadi HTML dan screenshot tampil dengan font asli.
- Desain Home masih memakai kartu lama per tipe dengan label "Arunika Serpong/Cibubur/Karawang". Itu sisa versi lama: implementasikan pakai kartu cluster dan label kawasan yang benar.

### Dampak ke kode yang sudah ada

- **Milestone 1 (setup):** tidak ada yang perlu dibongkar. Cukup cek layout global: kolom Properti di footer nanti diisi dari data kawasan, dan menu header tetap satu item "Properti".
- **Kalau migrasi/model/seeder cluster sudah terlanjur dibuat:** tambahkan migrasi baru, jangan edit migrasi lama yang sudah pernah dijalankan. Selama belum ada data production, `migrate:fresh --seed` boleh.
- **Kalau komponen kartu per tipe sudah dibuat:** ganti dengan kartu cluster; kartu per tipe tidak dipakai lagi di halaman mana pun.

### Yang perlu dikonfirmasi ke pemilik (tanya sebelum memutuskan sendiri)

- Belum ada. Kalau menemukan kasus yang tidak tercakup (misalnya kawasan tanpa cluster yang dipublikasikan), tampilkan kawasan itu hanya kalau punya minimal 1 cluster yang dipublikasikan, lalu catat di ringkasan milestone.

### Keputusan

Dikonfirmasi pemilik pada 24 Sep 2026 (opsi A: ikuti desain).

**Alur navigasi properti**

- **Tampilan Cluster:** `/properti` → kartu cluster → Detail Rumah (`/properti/{slug-cluster}`). Detail Rumah berisi tab tipe rumah sebanyak tipe di cluster itu.
- **Tampilan Kawasan:** `/properti/kawasan` → kartu kawasan + section "Cluster yang berdiri sendiri" → klik kartu kawasan → Detail Kawasan (`/properti/kawasan/{slug}`) → kartu cluster di kawasan itu → Detail Rumah.
- **Cluster mandiri:** kartunya langsung ke Detail Rumah.
- **Dropdown kawasan** di tampilan Cluster hanya filter biasa. Halaman tetap di `/properti?kawasan={slug}` (tidak pindah ke Detail Kawasan) dan bisa digabung dengan filter tipe, kamar, harga, dan status. Opsi "Cluster mandiri" juga filter biasa: `?kawasan=mandiri`.
- **Toggle Cluster/Kawasan** adalah dua link biasa ke dua URL, bukan tab JavaScript. Keduanya ter-render SSR dan terindeks.

**Detail Kawasan wajib bisa dijangkau dari:**

1. Toggle Kawasan (`/properti/kawasan`)
2. Kartu kawasan
3. Kolom Properti di footer (otomatis dari kawasan yang dipublikasikan)
4. Breadcrumb Detail Rumah (dilewati kalau cluster mandiri)
5. Section "Kawasan lainnya" di Detail Kawasan
6. Sitemap (`sitemap-properti.xml`)

**Keputusan setelah Milestone 2** (dikonfirmasi pemilik 24 Sep 2026)

1. Tidak ada pilihan "tampilan default" di Pengaturan Halaman → Properti. `/properti` selalu tampilan Cluster.
2. Link "Karier" di footer diganti "Kontak". Halaman Karier tidak dibuat sekarang.
3. Hak akses Admin Konten: boleh Pengaturan Global, Menu Navigasi, dan Redirect, **kecuali** tab "Tracking & verifikasi" di Pengaturan Global (GTM/GA4, Meta Pixel, kode verifikasi, Turnstile key). Tab itu hanya untuk Super Admin: disembunyikan di form, nilainya tidak dikirim ke browser, dan perubahan dari role lain ditolak di server. Marketing tetap hanya Lead & Newsletter.
4. Kategori fasilitas "Ibadah" ditambahkan (dipakai kartu Rumah Ibadah di desain).
5. Data dummy yang dikarang (tidak ada di desain) dicatat di `docs/DATA-DUMMY.md`. Field terkait di admin menampilkan penanda "Data dummy" selama nilainya belum diganti.

**Keputusan setelah Milestone 3** (dikonfirmasi pemilik 25 Sep 2026)

1. Halaman tanpa desain (Tentang Kami, Kontak, Kebijakan Privasi, Terima Kasih, 404) diterima sementara; pemilik me-review setelah pull.
2. Breadcrumb Detail Rumah **tampil di mobile**, versi ringkas satu baris (font kecil, scroll horizontal kalau panjang). Ini jalan balik ke halaman kawasan (brief 8.7).
3. Meta title yang diisi di admin menggantikan pola judul otomatis.
4. Waktu baca artikel dihitung otomatis dari jumlah kata.
5. Foto pendukung di isi artikel seeder tidak perlu ditambahkan.
6. Tombol WhatsApp di halaman Kontak hanya muncul kalau nomor WA di Pengaturan Global valid.

**Keputusan untuk Milestone 4** (lead & tracking, dikonfirmasi pemilik 25 Sep 2026)

- **Meta Conversions API:** event `Lead` dikirim dari server dengan `event_id` yang sama dengan Pixel (deduplikasi). Pixel ID dan access token diisi di Pengaturan Global → tab Tracking (hanya Super Admin). Access token disimpan terenkripsi dan tidak ditampilkan ulang setelah disimpan. Token kosong = CAPI dilewati tanpa error.
- Nomor WA dan email dinormalisasi lalu di-hash SHA-256 sesuai spesifikasi Meta sebelum dikirim.
- **Notifikasi lead:** email ke satu atau lebih alamat dari Pengaturan Global, lewat queue. Webhook opsional (URL dari settings; kosong = tidak dikirim).
- **Turnstile:** key dari settings. Key kosong (lokal/dev) = validasi Turnstile dilewati; honeypot dan rate limit tetap jalan.

Keputusan teknis Milestone 4 (menunggu konfirmasi pemilik):

- Tab "Notifikasi lead" (email penerima & webhook) hanya untuk Super Admin, sama seperti tab Tracking, karena webhook bisa dipakai mengirim data lead ke luar dan Admin Konten tidak punya akses Lead.
- Secret key Turnstile juga disimpan terenkripsi di settings (bukan `.env`), sama seperti access token CAPI. Turnstile aktif hanya kalau site key dan secret key sama-sama terisi.
- Atribusi: landing page & referrer dari kunjungan pertama; UTM/fbclid/gclid diperbarui kalau pengunjung datang lagi lewat kampanye baru (klik terakhir yang bertanda).
- Tombol "Jadwalkan Kunjungan/Survey" membuka form singkat (modal); bisa dimatikan di Pengaturan Global → CTA global.
- Bot yang mengisi honeypot dijawab seolah sukses (redirect ke /terima-kasih) tanpa data disimpan dan tanpa event konversi.

**Tambahan setelah Milestone 4** (dikonfirmasi pemilik 25 Sep 2026)

1. **Tracking GTM-first.** Semua event di-push ke `dataLayer` dengan nama dan parameter sesuai brief 8.8; `generate_lead` membawa `event_id`. Kolom GA4 dan Meta Pixel ID di admin tetap opsional dengan peringatan "Kosongkan jika Pixel/GA4 sudah dipasang lewat GTM, supaya event tidak terhitung dua kali." (plus tanda "GTM juga terisi" kalau keduanya diisi). Conversions API memakai `event_id` yang sama dengan di dataLayer. Karena Pixel boleh hanya dipasang lewat GTM, ada kolom baru **Pixel ID untuk Conversions API** (kosong = pakai Meta Pixel ID). Panduan lengkap: `docs/TRACKING.md`.
2. **Rate limit lead:** 5 per menit per IP (tetap), batas harian per IP naik dari 30 ke **100** (open house, banyak orang dari WiFi yang sama), dan **maksimal 3 lead per nomor WA per 24 jam**. Batas per nomor hanya menghitung lead yang tersimpan, jadi salah isi form tidak ikut terhitung; nomor ditulis dalam format apa pun (08…/+62…) dianggap sama.
3. **First-touch UTM:** lead menyimpan `first_utm_source/medium/campaign` (kampanye pertama yang membawa UTM, tidak pernah ditimpa) selain UTM terakhir yang sudah ada. Keduanya tampil di detail lead (admin) dan ikut di ekspor CSV/XLSX serta webhook.

Dikonfirmasi pemilik 25 Sep 2026:

- Batas per nomor WA memakai **24 jam terakhir** (rolling), bukan reset tengah malam, supaya tidak bisa diakali dengan submit menjelang pukul 00.00.
- First-touch diambil dari **kunjungan pertama yang membawa UTM**. Kunjungan pertama yang sebenarnya (termasuk tanpa UTM) tetap tercatat di `landing_page`.

**Keputusan Milestone 5** (dikonfirmasi pemilik 25 Sep 2026)

1. Sitemap dibangun dinamis dari database dan di-cache (bukan file statis). Cache dibuang otomatis setiap konten/settings berubah dan dibangun ulang harian (`sitemap:refresh`).
2. **Koreksi brief 8.4:** robots.txt **tidak** memblok URL filter/urutan/pencarian. Kalau diblok, Google tidak bisa membaca noindex-nya dan URL itu tetap bisa terindeks tanpa isi. Halaman itu cukup memakai meta robots `noindex, follow` + canonical ke versi tanpa query. robots.txt production tetap memblok `/admin`, `/livewire`, dan `/terima-kasih` (`/pratinjau` juga tidak diblok: URL-nya bertanda tangan, menolak akses tanpa login admin, dan selalu `noindex`).
3. OG image default per tipe halaman berupa gambar statis bergaya brand di `public/og/` (nama brand tertulis di gambar). Kalau nama brand berubah, unggah OG default baru di Pengaturan Global → SEO default.
4. Host kanonik diambil dari `APP_URL`; di production semua request http atau www/non-www yang berbeda di-301 ke sana. Domain final diisi pemilik saat deploy (catatan di README bagian deploy).
5. `Organization` + `WebSite` di semua halaman; `RealEstateAgent` (kantor pemasaran) di Beranda dan Kontak.
6. Pratinjau untuk artikel, cluster, dan kawasan (termasuk yang belum dipublikasikan): tombol "Pratinjau" di form admin, URL bertanda tangan 1 jam, hanya Super Admin/Admin Konten, `noindex`. Pratinjau cluster juga menampilkan tipe yang belum dipublikasikan.
7. IndexNow tidak dipasang.

Catatan teknis lain: Detail Rumah memakai `Residence` (cluster) berisi tiap tipe sebagai entitas ganda `Product` + `SingleFamilyResidence` supaya `Offer` valid bersama `floorSize`/`numberOfRooms`. Konten yang dihapus (soft delete) → 410 Gone, kecuali ada redirect di Redirect Manager.

**Keputusan Milestone 6** (dikonfirmasi pemilik 25 Sep 2026)

1. **Token warna terakota:** `#A94F2A` (desain) → **`#9A4524`**, hover tetap `#7E3A1E` (lebih gelap dari warna baru). Dengan warna desain, teks terakota kecil di latar sand (4,32:1) dan teks merah muda di CTA terakota (4,44:1) tidak lolos kontras WCAG AA 4,5:1; dengan `#9A4524` semua kombinasi ≥ 5:1 dan Lighthouse Accessibility 100. Brief bagian 6 sudah diperbarui; file HTML di `docs/design/` tetap memakai warna lama sebagai referensi tata letak.
2. **Cache halaman publik** di aplikasi (HTML awal untuk tamu), hanya di production, TTL maksimal 1 jam; dibuang otomatis saat konten/media/settings berubah.
3. **Queue worker wajib** di server (email notifikasi, CAPI, webhook, varian gambar). Untuk tes lokal tanpa worker: `QUEUE_CONNECTION=sync` (catatan di README bagian "Menjalankan di Lokal").

Catatan teknis lain: varian gambar AVIF + WebP di 480/960/1600 px (tidak diperbesar); judul `h2` tersembunyi (sr-only) "Daftar fasilitas" dan "Daftar kawasan" untuk urutan heading; dampak GTM/Pixel belum bisa diukur di lingkungan build (akses keluar ke Google/Facebook diblok) — ukur ulang dengan PageSpeed Insights setelah deploy dan ID tracking diisi.

**Keputusan Milestone 7** (dikonfirmasi pemilik 25 Sep 2026)

1. **CSP hanya untuk halaman publik** (nonce + `'strict-dynamic'`); admin Filament tanpa CSP karena Alpine/Livewire membutuhkan `unsafe-eval`. Header keamanan lain (nosniff, X-Frame-Options, Referrer-Policy, Permissions-Policy) di semua respons termasuk admin. Tag GTM dengan `document.write` diblok CSP.
2. **Backup** database + file unggahan (`storage/app/public`, tanpa varian AVIF/WebP yang bisa dibuat ulang); kode ada di Git. Saat deploy ditambah disk di luar server (`BACKUP_DISKS=local,s3`; opsi murah Cloudflare R2 / Backblaze B2 di `docs/CHECKLIST-LAUNCH.md`).
3. **Akun seeder di production:** password dari `SEED_ADMIN_PASSWORD` atau acak (dicetak sekali), bukan `password`; seeder tidak me-reset password akun yang sudah ada.

Catatan teknis lain: rich text disanitasi saat disimpan dan saat dikirim ke browser; perbaikan aksesibilitas dari audit axe-core (h2 "Daftar artikel", carousel fasilitas bisa difokus, fokus lightbox galeri).

**Tambahan setelah Milestone 7** (dikonfirmasi pemilik 25 Sep 2026)

- **Domain tambahan CSP** di Pengaturan Global → Tracking & verifikasi (Super Admin saja): daftar domain per direktif (`script-src`, `connect-src`, `img-src`, `frame-src`) yang digabung ke CSP publik. Format divalidasi: hanya nama domain atau `https://domain[:port]`; wildcard `*`, kata kunci (`'unsafe-eval'`, `'unsafe-inline'`), `http://`, path, spasi, dan `;` ditolak. Disimpan dalam bentuk baku `https://domain`.
- Supaya field itu berarti, `connect-src`, `img-src`, dan `frame-src` tidak lagi mengizinkan semua `https:`, tapi memakai daftar bawaan (GTM, GA4, Meta Pixel, Turnstile, Google Maps, YouTube; `App\Support\CspSources::DEFAULTS`) + domain tambahan + domain yang diturunkan otomatis (embed peta Kontak, CDN/bucket foto). Konsekuensi: gambar dari domain luar di isi artikel juga perlu domainnya ditambahkan di `img-src`.
- `docs/TRACKING.md`: bagian "Menambah tag baru di GTM" (cara membaca pesan CSP di Console, contoh domain TikTok Pixel & Google Ads).
- `docs/CHECKLIST-LAUNCH.md`: backup luar server via Cloudflare R2 / Backblaze B2 (driver S3 `league/flysystem-aws-s3-v3` dipasang) beserta env-nya.

**Kompatibilitas PHP 8.3.6** (permintaan pemilik 28 Sep 2026; server produksi/lokal memakai PHP 8.3.6)

- `composer.json`: `"php": "^8.3"` dan `config.platform.php = 8.3.6`, sehingga `composer install/update` hanya memilih versi yang jalan di PHP 8.3.6, tanpa `--ignore-platform-reqs`. `composer.lock` dibuat ulang dengan PHP 8.3.6.
- Laravel 13 dan Filament 5 **tetap** (keduanya mendukung PHP 8.3). Perubahan versi library:

  | Package | Sebelum | Sesudah | Alasan |
  |---|---|---|---|
  | `symfony/*` (http-kernel, console, mailer, mime, routing, html-sanitizer, dll.) | 8.1.x | 7.4.x (LTS) | Symfony 8 butuh PHP 8.4 |
  | `spatie/laravel-sitemap` | 8.2.0 | 7.4.0 | 8.x butuh PHP 8.4 |
  | `spatie/schema-org` | 5.0.1 | 3.23.2 | 5.x butuh PHP 8.4 |
  | `filament/filament` (+ plugin) | 5.8.4 | 5.9.0 | pembaruan minor ikut resolusi ulang |
  | `inertiajs/inertia-laravel` | 3.3.4 | 3.4.0 | pembaruan minor |
  | `nesbot/carbon` | 3.14.0 | 3.14.1 | patch |

  Ditambah otomatis: `symfony/polyfill-php83` (dan `polyfill-php84` tetap, menyediakan `Pdo\Mysql` dll. di PHP 8.3), `spatie/browsershot` + `nicmart/tree` (dependensi laravel-sitemap 7, tidak dipakai langsung). Total 52 package berubah; API yang dipakai aplikasi (Sitemap/Url/Image, Schema/MultiTypedEntity) sama, tidak ada perubahan kode.
- Kode aplikasi dicek bebas sintaks khusus PHP 8.4+ (property hooks, asymmetric visibility, `new` tanpa kurung saat chaining, `array_find`/`array_any`/`array_all`, `mb_trim`, dll.) dan lolos `php -l` di PHP 8.3.6. `use Pdo\Mysql` di `config/database.php` (bawaan Laravel) aman karena disediakan `symfony/polyfill-php84`.
- **AVIF:** GD bawaan Ubuntu untuk PHP 8.3.6 tidak mendukung AVIF. Varian gambar kini hanya membuat format yang didukung server (`ResponsiveImages::formats()`): WebP selalu, AVIF bila GD/Imagick mendukung. Sebelumnya konversi AVIF akan gagal di server seperti itu.
- Seluruh test, `migrate:fresh --seed`, `npm run build`, dan `qa:pages` dijalankan dengan PHP 8.3.6 (paket Ubuntu `8.3.6-0ubuntu0.24.04.11`) dan juga PHP 8.4.
- README → Requirement: PHP 8.3.6+, daftar extension wajib, perintah `php -m` untuk mengecek, dan catatan AVIF.

**Status opsional & cluster tanpa tipe/harga** (permintaan pemilik 28 Sep 2026)

- **Status penjualan** cluster (ready stock / inden / sold out) opsional. Migrasi `make_cluster_status_nullable` (kolom nullable, tanpa default). Form admin: "Status penjualan (opsional)", placeholder "Tanpa status". Status kosong = tidak ada badge status di kartu maupun Detail Rumah; di JSON-LD `availability` hanya diisi kalau bisa ditentukan (status atau sisa unit).
- **Cluster tanpa tipe / tanpa harga** didukung. Di admin, "Harga mulai" dan "LT" tipe rumah tidak wajib lagi. Nilai 0/kosong dianggap belum diisi: `Rupiah` mengembalikan `null` (tidak pernah "Rp 0"), dan agregat cluster (`price_min`, LT, KT, cicilan) mengabaikan 0.
  - Kartu cluster & kartu kawasan: harga diganti label **"Hubungi kami untuk harga"** (`labels.price_on_request` di Pengaturan Global, bisa diubah). Kartu kawasan ringkas tidak menulis "· mulai …" kalau belum ada harga; statistik harga di hero Detail Kawasan memakai label yang sama.
  - Detail Rumah: section harga/cicilan/booking fee, spesifikasi, dan baris data tab tipe hanya tampil kalau datanya ada. Tipe yang sama sekali tanpa data (harga, luas, KT, denah) tidak dijadikan tab. Tombol WA (pesan tanpa nama tipe) dan form lead tetap tampil; sticky bar mobile menampilkan "Hubungi kami untuk harga".
  - JSON-LD: `Offer` hanya dibuat untuk tipe dengan harga > 0; cluster tanpa tipe tidak punya `containsPlace`; jumlah kamar hanya diisi kalau > 0.
- **Urutan listing:** cluster yang sudah punya harga selalu di atas, yang belum punya harga di bawah (scope `Cluster::pricedFirst()`), di `/properti` (semua pilihan urut), cluster mandiri di `/properti/kawasan`, dan daftar cluster di Detail Kawasan.
- Test: `tests/Feature/ClusterWithoutTypesTest.php` (form admin tanpa status, props & JSON-LD Detail Rumah tanpa tipe / tipe tanpa harga, kartu di listing/kawasan/beranda/sitemap, urutan, `Rupiah`, render SSR). Helper `jsonLd()`/`ofType()` dipindah ke `tests/Pest.php`. Dijalankan di PHP 8.3.6 dan 8.4 (SQLite) dan juga MariaDB.

**Import data asli BSD City** (permintaan pemilik 28 Sep 2026; data di `docs/data/bsd-city-data.json`, checklist di `docs/data/BELUM-LENGKAP.md`)

- Perintah `php artisan import:bsd-data {path=docs/data/bsd-city-data.json} {--fresh} {--force}` (`App\Console\Commands\ImportBsdData`). Cara pakai ada di README → Import data asli dan CHECKLIST-LAUNCH bagian 6.
  - **Upsert per slug**: tipe rumah per (cluster, slug); tipe tanpa nama memakai slug teknis `tipe-N`.
  - **Nilai null tidak menimpa isi yang sudah ada.**
  - **Checklist digabung**, dan status centang dari admin dipertahankan.
  - **`--fresh`** menghapus permanen semua kawasan, cluster, tipe rumah (beserta galeri, media, SEO), dan 2 promo contoh Arunika (`ContentSeeder::DUMMY_PROMO_TITLES`). User, artikel, lead, fasilitas kota, dan settings lain tidak disentuh.
  - **SEO halaman** masuk ke tab SEO settings tiap halaman. Pola Detail Kawasan/Rumah `{Nama …}` diubah jadi `{name}`.
  - **Konversi:**
    - Rentang disimpan angka terkecil; `3+1` / `5+1+1` disimpan angka pertama; lantai 2,5 disimpan 2. Nilai aslinya ditulis di catatan internal tipe.
    - `x` di kavling jadi `×`.
    - Deskripsi teks biasa jadi paragraf HTML.
    - Ikon fasilitas/keunggulan dipilih dari kata kunci (tanpa ikon cocok = ikon centang).
  - **Urutan**: cluster prioritas 1–10 di urutan teratas, sisanya mengikuti urutan file.
- Migrasi `add_import_fields_to_property_tables`:
  - **Cluster**: `catatan_internal`, `perlu_dilengkapi` (`[{item, selesai}]`) + `perlu_dilengkapi_count` (dihitung otomatis), `prioritas`, `facilities`, `launch_year`.
  - **Kawasan**: `access` (lokasi & akses), `opened_year`.
  - **Tipe rumah**: `catatan_internal`; `name` dan `floors` boleh kosong.
- **Admin:**
  - **Cluster**: tab **Internal** (prioritas, catatan internal, checklist perlu dilengkapi dengan centang), fasilitas cluster, dan tahun launching.
  - **Tabel cluster**: kolom **Kelengkapan** ("Belum lengkap (N)", tooltip berisi item yang belum), filter **Belum lengkap**, kolom **Prioritas** yang bisa diurutkan (tanpa prioritas selalu di bawah).
  - **Tipe rumah**: nama tidak wajib, ada catatan internal, dan "Sisa unit" ditandai internal.
  - **Kawasan**: daftar lokasi & akses, dan tahun dibuka.
- **Website:**
  - **Detail Kawasan**: daftar "Lokasi & akses" (judul di Pengaturan Halaman → Detail Kawasan → Fasilitas).
  - **Detail Rumah**:
    - Section "Fasilitas {cluster}" (Pengaturan Halaman → Detail Rumah).
    - Section deskripsi disembunyikan kalau deskripsi kosong.
    - **Sisa unit tidak ditampilkan lagi** (label `units_available` & `unit` dihapus dari defaults).
  - **Tipe tanpa nama:**
    - Label tab jadi "Harga mulai Rp …", dan judul H1 tanpa nama tipe.
    - Pesan WA dan nama di JSON-LD memakai nama cluster.
    - Tidak dibuat chip di kartu.
    - Section tipe tidak tampil kalau semua tipe hanya berisi harga.
  - Tahun dibuka/launching belum ditampilkan di website (hanya di admin).
- Test: `tests/Feature/ImportBsdDataTest.php` (--fresh, idempoten, isian admin tetap, konversi, SEO, render halaman, data internal tidak bocor, tabel & form admin).

**Ganti Arunika → BSD City, footer kawasan, konten contoh nonaktif** (permintaan pemilik 29 Sep 2026)

1. **Teks Arunika → BSD City**:
   - **Diganti di tiga tempat:** isi awal (`database/settings/defaults/`, seeder) **dan** database yang sudah berjalan lewat migrasi.
     - `database/settings/2026_09_29_100000_rebrand_to_bsd_city.php` untuk settings.
     - `database/migrations/2026_09_29_100100_replace_arunika_content.php` untuk Profil Developer, Profil Lokasi, dan deskripsi kategori artikel.
   - **Teks yang sudah diubah admin tidak ditimpa:** nilai hanya diganti kalau masih sama dengan teks lama. Jalan otomatis saat deploy (`php artisan migrate`).
   - **Global:**
     - Brand "BSD City", tagline "Kota mandiri Sinar Mas Land di Serpong".
     - Pesan WA default dan deskripsi footer diperbarui.
   - **Beranda:**
     - Hero: eyebrow "Serpong, Tangerang", headline & sub-headline baru, tombol "Lihat Semua Cluster" → /properti dan "Chat Marketing" → WA.
     - Judul listing "Temukan rumah di BSD City", deskripsi keunggulan wilayah, judul artikel, dan meta description.
   - **Properti:** judul "Properti BSD City", deskripsi baru, eyebrow "Serpong, Tangerang", SEO kedua tampilan (statistik tetap otomatis).
   - **Detail Kawasan:** eyebrow "Kawasan di BSD City".
   - **Detail Rumah:** jabatan marketing default "Marketing BSD City".
   - **Fasilitas:** eyebrow "Fasilitas Kota", deskripsi, dan SEO.
   - **Artikel:** judul "Kabar & inspirasi dari BSD City" dan SEO.
   - **Tentang Kami & Kontak:** alt foto dan SEO.
   - **Profil Developer:** deskripsi diganti paragraf profil BSD City dari data asli, alt foto diganti.
   - **Profil Lokasi:** nama & alt foto.
   - **Logo** tetap teks. Tagline hanya tampil di header kalau ≤ 24 karakter (tagline baru tidak muat di samping menu 1280 px). Menu header tidak lagi patah baris.
   - **OG image bawaan** (`public/og/*.png`) dibuat ulang dengan brand BSD City lewat script baru `scripts/og-images.mjs`.
   - **Lain-lain:** user agent `qa:pages` jadi `SiteQA/1.0`, `APP_NAME` di `.env.example` jadi "BSD City".
   - **Sisa "Arunika" yang sengaja dibiarkan:**
     - Data contoh di `PropertySeeder`/`ContentSeeder`/`ArticleSeeder` (dihapus atau dinonaktifkan oleh import; dipakai test).
     - Nama cookie `arunika_attribution` (mengganti nama membuang data first-touch pengunjung yang sudah ada).
     - Contoh nama database di `.env.example`.
2. **Footer kolom Properti:**
   - Maksimal 8 kawasan. Kawasan dengan cluster Prioritas 1–10 tampil dulu (prioritas terkecil di atas), lalu urutan kawasan.
   - Ditutup link "Semua kawasan" → /properti/kawasan.
   - Jumlah, label, dan URL link diatur di Pengaturan Global → Footer (`property_limit`, `property_all_label`, `property_all_url`).
3. **Konten contoh dinonaktifkan, tidak dihapus:**
   - Fasilitas, pengembangan mendatang, artikel, dan promo contoh diberi `is_published = false` (`App\Support\DummyContent`). Dijalankan oleh migrasi di atas dan oleh `import:bsd-data --fresh`, yang sekarang tidak lagi menghapus promo.
   - Section **Fasilitas**, **Pengembangan Mendatang**, dan **Artikel & Berita** di Beranda dimatikan lewat toggle.
   - **Keadaan kosong:**
     - `/fasilitas` dan `/artikel` tetap bisa dibuka dan menampilkan kartu keadaan kosong (judul, deskripsi, tombol).
     - Teksnya diatur di tab Daftar masing-masing Pengaturan Halaman.
     - Selama kosong, statistik & foto header Fasilitas serta filter/pencarian Artikel disembunyikan.
     - Pencarian artikel tanpa hasil tetap memakai "Belum ada artikel yang cocok.".
   - **Sitemap** sudah hanya memuat konten yang dipublikasikan; kategori artikel tanpa artikel terbit tidak masuk.
   - **Perbaikan kecil:** kolom email newsletter di mobile sebelumnya gepeng (`flex-1` di layout kolom).
- Test: `tests/Feature/RebrandBsdCityTest.php` (migrasi settings & data, teks admin aman, tidak ada "Arunika" di halaman publik, hero & header Properti, footer, section Beranda, keadaan kosong, sitemap).

**Update 2: tanggal launching & promo** (permintaan pemilik 29 Sep 2026; data di `docs/data/bsd-city-update-2.json`)

1. **Urutan "Terbaru" sesuai tanggal launching:**
   - Kolom baru `clusters.tanggal_launching` (date, nullable, ber-index).
   - **Admin:** date picker di form Cluster, dan kolom tabel yang bisa diurutkan (kosong selalu di bawah).
   - **Isi awal:** migrasi mengisi 1 Januari tahun launching untuk cluster yang sudah punya tahun. `import:bsd-data` melakukan hal yang sama untuk import baru.
   - **Scope `Cluster::latestLaunched()`:** tanggal launching DESC (kosong paling bawah), lalu prioritas (kosong di bawah), lalu nama.
   - **Dipakai di:**
     - Urutan "Terbaru" di `/properti`, yang juga menjadi default (settings `default_sort` dipastikan `terbaru` lewat migrasi settings).
     - Daftar cluster di Detail Kawasan (`Kawasan::publishedClusters`).
   - **Aturan "berharga di atas" dari revisi 28 Sep** sekarang hanya berlaku untuk urut harga (terendah/tertinggi). Di "Terbaru", cluster baru tanpa harga tetap tampil sesuai tanggal launching-nya.
2. **Promo:**
   - **Bug:** kotak "Promo rumah ini" sebelumnya hanya tampil kalau promo punya daftar benefit (`items`), dan hanya satu promo. Promo hasil import yang hanya berisi deskripsi tidak pernah tampil.
   - **Komponen baru `PromoSection`:** menampilkan semua promo aktif (label, judul, deskripsi, benefit opsional, periode). Catatan internal tidak dikirim ke browser.
   - **Detail Rumah:** promo aktif yang terhubung ke cluster. Section disembunyikan kalau tidak ada.
   - **Detail Kawasan:** section baru "Promo di kawasan ini" (Pengaturan Halaman → Detail Kawasan).
     - Isinya promo aktif yang terhubung langsung ke kawasan (pivot baru `kawasan_promo`) ditambah promo aktif cluster-cluster di kawasan itu.
     - Tiap kartu menautkan cluster-nya ke Detail Rumah. Satu promo satu kartu.
   - **Kartu cluster:** badge **"Promo"** otomatis selama cluster punya promo aktif. Badge ini mengalahkan badge pilihan admin; setelah promo berakhir, badge admin kembali.
   - **Admin:**
     - Form Promo: pilihan kawasan (opsional) dan catatan internal (`promos.catatan_internal`, berisi `sumber` dari file).
     - Form Kawasan: tab Promo.
   - **Promo kedaluwarsa tidak tampil di mana pun:**
     - Semua tempat memakai `Promo::active()`.
     - Tanggal berakhir tanpa jam disimpan sampai 23:59:59.
     - **Cache halaman** tidak hidup melewati waktu terdekat promo mulai/berakhir (`PageCache::ttl()`), jadi promo muncul/hilang tepat waktu walau halaman di-cache.
3. **Perintah `php artisan import:bsd-update {path}`** (`App\Console\Commands\ImportBsdUpdate`):
   - Upsert, aman diulang: tanggal launching per `cluster_slug`, promo per judul.
   - Relasi promo ke cluster ditambahkan tanpa melepas relasi dari admin.
   - `is_published` dari file hanya dipakai saat promo dibuat.
   - **Hasil:** 21 tanggal launching dan 9 promo. Semua promo draft sesuai file, jadi belum ada yang tampil sampai dipublikasikan di admin.
- **Perbaikan kecil:** nama tipe yang sudah diawali "Tipe" tidak lagi jadi "Tipe Tipe 5" (judul, tab, pesan WA).
- Test: `tests/Feature/ImportBsdUpdateTest.php`; test urutan lama disesuaikan.

**Keputusan Update 2 (disetujui pemilik 29 Sep 2026)**

1. **Urutan harga vs "Terbaru":** aturan "cluster berharga di atas" hanya berlaku untuk urut harga (terendah/tertinggi). Di "Terbaru", cluster baru yang belum punya harga tetap tampil sesuai tanggal launching-nya.
2. **Status publikasi promo saat import ulang:** `is_published` dari file hanya dipakai saat promo pertama kali dibuat. `import:bsd-update` yang dijalankan ulang tidak mematikan promo yang sudah dipublikasikan admin.
3. **Relasi promo ke cluster saat import ulang:** hanya ditambah (`syncWithoutDetaching`), tidak pernah dilepas. Cluster yang ditambahkan admin tetap ada.
4. **Badge "Promo"** menggantikan badge pilihan admin (mis. "Baru") selama cluster punya promo aktif. Setelah promo berakhir, badge admin tampil lagi.
5. **Nama tipe berawalan "Tipe"** ("Tipe 5 Standard") tidak diberi awalan "Tipe" lagi di judul Detail Rumah, tab tipe, dan pesan WhatsApp.

**Import otomatis di script deploy** (29 Sep 2026)

- Script deploy menjalankan `import:bsd-data … --fresh --force --no-interaction` (hanya import pertama) dan `import:bsd-update … --force --no-interaction` saat file data berubah.
- **`import:bsd-update`** sekarang menerima `--force`. Perintah ini memang tidak pernah bertanya (tidak menghapus apa pun), jadi opsinya diterima supaya kedua import bisa dipanggil dengan flag yang sama.
- **`import:bsd-data`:** satu-satunya konfirmasi (`--fresh` di production) dilewati dengan `--force`. Dengan `--no-interaction` tanpa `--force`, perintah berhenti dengan pesan jelas (exit 1) dan tidak menghapus apa pun.
- **Diuji:**
  - Test: `APP_ENV=production` + `--force --no-interaction` berjalan tanpa pertanyaan; tanpa `--force` konfirmasi muncul.
  - CLI: `APP_ENV=production php artisan import:bsd-data --fresh --no-interaction` → exit 1; dengan `--force` → exit 0.
- **`php artisan backup:run --only-db --disable-notifications`** berhasil (koneksi `mysql` & `mariadb`, `APP_ENV=production`, disk `local`). Di server butuh `mysqldump` (ditambahkan di CHECKLIST-LAUNCH bagian server).

**Bank Benefit menggantikan Promo** (permintaan pemilik 1 Okt 2026, branch `feature/bank-benefit`)

**Data**
- Tabel `benefits` (nama, slug unik, kategori `pembayaran` / `bonus_unit` / `material` / `diskon`, ikon dari set ikon yang ada, urutan, aktif).
- Pivot `benefit_cluster` (`teks_tampil` maks 40 karakter, kosong = nama benefit; `urutan`; timestamps). Tanpa tanggal berakhir dan tanpa keterangan: benefit tampil selama dicentang di cluster.
- **Isi awal 15 benefit** (`App\Support\BenefitCatalog`), dijalankan oleh migrasi, jadi otomatis ada di production saat deploy.
  - Idempotent per slug: benefit yang belum ada dibuat; yang sudah ada (mungkin diubah admin) tidak ditimpa.
  - "Free Biaya Surat (AJB/BBN)" memakai slug `free-biaya-surat`.
- **Promo lama tidak dihapus:** tabel, model, dan halaman admin tetap ada.
  - Menu Promo disembunyikan.
  - Banner promo Beranda, "Promo rumah ini", dan "Promo di kawasan ini" tidak tampil lagi.
  - Pengaturan section promo lama dihapus dari Pengaturan Halaman.
  - Field promo di form Cluster/Kawasan dihapus.
  - Komponen `promo-section` dan presenter `PromoCard` dihapus. `PageCache::ttl()` kembali ke TTL config, karena benefit tidak punya periode.
  - Penghapusan tabel menunggu konfirmasi pemilik.

**Admin**
- Menu **Properti → Bank Benefit**: CRUD, drag untuk mengurutkan, kolom "Dipakai di X cluster", filter kategori, toggle aktif.
- **Cluster:**
  - Tab baru **Promo & Benefit**: repeater ke pivot (Select benefit searchable dikelompokkan per kategori, tidak bisa dobel; Teks tampil dengan placeholder "Opsional, contoh: Diskon hingga 13%"; bisa diurutkan). Tab "Marketing & Promo" jadi "Marketing".
  - Tabel cluster: kolom **Benefit** (jumlah) dan **Benefit diperbarui** (updated_at pivot terakhir), filter **Benefit**.
  - Bulk action **Tambah benefit ke cluster terpilih** (teks tampil opsional) dan **Lepas benefit dari cluster terpilih**.
- **Pengaturan Halaman → Detail Rumah → Promo & Benefit:** toggle, judul, teks syarat & ketentuan, label tombol, template pesan WA (`{cluster}`).
  - Migrasi settings melengkapi key `sections.*` yang belum tersimpan. Sebelumnya, section baru seperti "Fasilitas cluster" tampil dengan toggle mati di form admin dan bisa ikut tersimpan mati.

**Website**
- **Detail Rumah, section "Promo & Benefit":**
  - Benefit aktif dikelompokkan per kategori (urutan: pembayaran, bonus unit, material, diskon), ikon + teks + "*".
  - Di bawahnya teks syarat & ketentuan dan tombol **Dapatkan informasi lengkapnya via WhatsApp** ke nomor WA cluster (fallback nomor global) dengan pesan otomatis.
  - Tersembunyi kalau cluster tanpa benefit.
- **Tracking tombol WA (dan semua link WA):**
  - `click_whatsapp` di dataLayer dengan `event_id`, `cluster`, `sumber="promo_section"`.
  - Pixel `Contact` dengan `eventID` yang sama.
  - Kalau Meta CAPI aktif, browser mengirim `POST /track/contact` lalu job `SendMetaContactEvent` mengirim event `Contact` ke CAPI dengan `event_id` sama (deduplikasi). Endpoint dibatasi 20/menit per IP.
  - Sebelumnya klik WA hanya sampai ke Pixel browser, tanpa CAPI. Logika konteks CAPI (IP, UA, `_fbp`, `_fbc`/fbclid) dipindah ke `App\Support\MetaContext` dan dipakai juga oleh lead.
- **Kartu cluster:** chip maks 3 benefit (teks tampil atau nama, tanpa "*") + "+N". Badge **Promo** kalau ada minimal 1 benefit aktif, menggantikan badge promo lama.
- **`/properti?benefit=tanpa-dp,free-bphtb`:**
  - Cluster harus punya semua benefit yang dipilih. Filter "Promo & benefit" tampil di bar filter.
  - **Satu benefit** tanpa filter/urut lain = halaman sendiri yang boleh diindex: judul & H1 "Rumah Tanpa DP di BSD City" (pola di Pengaturan Halaman → Properti → SEO), meta description sendiri, self-canonical, dan masuk sitemap selama ada cluster terbit yang memakainya.
  - Kombinasi lebih dari satu benefit, atau benefit + filter lain: noindex, canonical ke `/properti`.
- **Urutan "Promo":** cluster dengan benefit aktif terbanyak di atas, lalu urutan Terbaru.
- **Label "Sold out" tidak tampil di mana pun:**
  - Badge status Detail Rumah kosong untuk sold out.
  - Opsi "Sold out" dihapus dari filter status, dan filter status dikeluarkan dari bar filter default (diganti filter benefit).
  - Sisa unit dan periode harga tetap tidak tampil.
- **`qa:pages`** sekarang ikut mengecek URL sitemap yang ber-query (halaman per benefit). Sebelumnya query dibuang sehingga URL itu tidak pernah dicek.

**Import**
- `import:bsd-update` membaca `"benefits": [{"cluster_slug", "benefit_slug", "teks_tampil"}]`. Upsert per (cluster, benefit).
- Jejak import disimpan di `benefit_cluster_imports`. Pivot yang **diubah di admin** setelah import terakhir, **dilepas di admin**, atau **dibuat admin sendiri** tidak ditimpa dan tidak dibuat ulang. Slug yang tidak ditemukan dilewati dengan peringatan.

**Test:** `tests/Feature/BankBenefitTest.php` (12 test).
- Isi awal idempotent.
- Section Detail Rumah: grup, teks, WA cluster/global, settings, benefit nonaktif.
- Chip & badge kartu.
- Filter + SEO satu/kombinasi benefit.
- Urutan Promo dan sitemap.
- Sold out.
- `/track/contact` + payload CAPI.
- Import benefit (idempoten, tidak menimpa ubahan admin).
- Admin: Bank Benefit, repeater, kolom, filter, bulk action.
- 5 test promo lama di `ImportBsdUpdateTest` diganti 1 test bahwa promo lama tidak tampil lagi walau dipublikasikan.

---

## Revisi 1 — 23 Sep 2026: pola repo rezabsd

- SQLite untuk lokal, MySQL 8 untuk production.
- Ikuti struktur dan format README repo `craxionstudio/rezabsd`.
- Build, migrate, dan test harus lolos di sesi sebelum push.
