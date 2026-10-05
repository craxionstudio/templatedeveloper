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
4. **Badge "Promo"** menggantikan badge pilihan admin (mis. "Baru") selama cluster punya promo aktif. Setelah promo berakhir, badge admin tampil lagi. *(Diubah 5 Okt 2026 untuk Bank Benefit: badge admin tetap pertama, "Promo" jadi badge kedua.)*
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
  - **Revisi 5 Okt 2026 (permintaan pemilik):** badge pilihan admin (mis. "Baru") tetap prioritas dan tampil pertama; "Promo" jadi badge kedua (warna lebih lembut). Kalau badge admin sudah "Promo", tidak dobel. Props kartu: `badge` diganti `badges` (list, maks 2).
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

**Tahap A — Rapikan & percepat** (disetujui pemilik 5 Okt 2026, langsung di `main`)

1. **Paket tidak terpakai dihapus:**
   - `laravel/wayfinder` (composer), `@laravel/vite-plugin-wayfinder`, dan `lucide-react`. Plugin Wayfinder dilepas dari `vite.config.ts`, beserta folder hasil generate (`resources/js/actions|routes|wayfinder`) dan entri `.gitignore`-nya.
   - `concurrently`, `typescript`, `@types/react`, dan `@types/react-dom` dipindah ke `devDependencies`. `npm ci` di deploy tetap memasangnya untuk build.
2. **Fraunces dikunci** ke opsz 72 dan wght 400–700 dengan fontTools instancer (`resources/fonts/fraunces-latin-opsz72-wght-normal.woff2`).
   - Ukuran file turun dari 66 KB menjadi 33 KB per halaman.
   - H1 sedikit lebih ramping (opsz 72 dipakai di semua ukuran), jadi pemenggalan baris bisa bergeser. Contohnya, judul cluster di mobile jadi 2 baris, sebelumnya 3.
   - Bobot yang dipakai situs (400/500/600/700) tidak berubah.
3. **Newsletter dihapus:**
   - Yang dihapus: form di halaman Artikel, `POST /newsletter` beserta rate limit-nya, model `NewsletterSubscriber`, menu admin Newsletter, tab Newsletter di Pengaturan Halaman → Artikel, dan label `newsletter_success`.
   - Tabel `newsletter_subscribers` di-drop lewat migrasi `2026_10_05_100000`.
4. **Webhook lead dihapus:** job `SendLeadWebhook` dan field "Webhook URL" di Pengaturan Global → Notifikasi lead. Email notifikasi lead tetap ada. Settings migrasi `2026_10_05_100000_remove_newsletter_and_lead_webhook` membuang key lamanya.
5. **Satu role: Admin** (`UserRole::Admin`, akses semua menu):
   - Migrasi `2026_10_05_100100` memindahkan semua user lama (Super Admin, Admin Konten, Marketing) ke `admin`. Default kolom jadi `admin`.
   - Pilihan role di form User dihapus. Seeder hanya membuat `admin@example.com`.
   - Lead tetap tidak bisa dibuat dari admin.
6. **Queue tanpa Supervisor:**
   - Scheduler menjalankan `queue:work --stop-when-empty --max-time=55 --tries=3` tiap menit (`withoutOverlapping`, di background, `routes/console.php`). Konversi foto WebP/AVIF dan email lead jalan selama cron `schedule:run` terpasang.
   - Satu baris crontab yang wajib ada: `* * * * * cd /path/ke/app && php artisan schedule:run >> /dev/null 2>&1`.
   - Setelah deploy Tahap B, laporan masih menunjukkan 4 job antre sejak 29 Sep. Karena itu ditambahkan diagnosis: heartbeat scheduler (`storage/app/scheduler-heartbeat` tiap menit), log worker `storage/logs/queue-worker.log`, dan rincian job per queue (attempts/reserved) di laporan deploy.
   - Deploy (langkah 9b) mengecek dan melaporkan di ringkasan GitHub Actions: cron terpasang atau belum, jumlah proses `queue:work`, program queue di Supervisor, serta jumlah job antre dan gagal. Laporan ini tidak menggagalkan deploy.
- **Test:**
  - Test role diganti dengan test migrasi role (user lama jadi Admin dan bisa membuka semua menu).
  - Test baru: newsletter tidak ada lagi.
  - Test webhook dan newsletter dihapus.

**Tahap B — Admin ringkas** (disetujui pemilik 5 Okt 2026, langsung di `main`)

1. **Terisi otomatis:**
   - **Slug** dari nama/judul, dibuat unik (`-2`, `-3`, …), lewat trait `FillsSlugAutomatically`. Berlaku untuk cluster, kawasan, artikel, kategori, penulis, benefit, fasilitas, kategori fasilitas, tag, dan tipe rumah (unik per cluster).
   - **Ringkasan:** cluster/kawasan dari deskripsi, artikel dari isi (`App\Support\Summary`).
   - **Tahun launching** dari tanggal launching.
   - **Alt text foto** dari keterangan atau "Foto {nama}". Nama file foto diambil dari nama.
   - **Meta title/description dan gambar share** memakai fallback yang sudah ada: nama, ringkasan, foto utama.
   - Field SEO/teknis pindah ke section **Lanjutan** (tertutup): slug, ringkasan, tipe properti, jenis bangunan, meta title, meta description, latitude/longitude.
   - Field yang dihapus dari form (kolom database tetap ada):
     - canonical, noindex, dan gambar OG per record;
     - semua alt text;
     - urutan (diganti drag di tabel);
     - tanggal terbit cluster/kawasan;
     - catatan cicilan/booking fee;
     - jabatan & foto marketing per cluster;
     - **sisa unit** di tipe rumah.
2. **Tanpa toggle "Tampilkan section":**
   - Section tersembunyi otomatis kalau datanya kosong: cluster/fasilitas/pengembangan/artikel terbit, Profil Developer/Lokasi, visi & timeline, peta, spesifikasi, fasilitas cluster, benefit.
   - Teks contoh dalam kurung siku (`[VISI PERUSAHAAN]`) dan link `#` dianggap kosong (`App\Support\Content`).
   - CTA bawah halaman selalu memakai CTA global, kecuali di Kontak yang tanpa CTA.
   - 69 label teks di Pengaturan Global disembunyikan dan memakai nilai bawaan.
3. **Pengaturan jadi 4 menu** (grup "Pengaturan"):
   - **Beranda**
   - **Properti** (listing + Detail Kawasan + Detail Rumah)
   - **Halaman Lain** (Tentang Kami, Kontak, Artikel, Fasilitas, Kebijakan Privasi, Terima Kasih)
   - **Pengaturan Umum** (WhatsApp, template pesan, kontak, nama perusahaan, media sosial, logo, GA4 Measurement ID, verifikasi Search Console, email notifikasi lead)

   Cara kerjanya:
   - Field yang bisa diubah ditentukan `editable()` tiap kelas `App\Settings`. `PageSettings::section()` hanya membaca key itu dari database; key lain selalu memakai teks tetap di `database/settings/defaults/*.php`.
   - Satu menu bisa menyimpan beberapa grup settings (`GroupedSettingsPage`).
   - Teks `[...]` tidak dimuat ke form, jadi admin langsung melihat placeholder contoh.
   - **Catatan:** teks lama di database untuk field yang tidak lagi ada di admin diabaikan. Contohnya pengaturan GTM/Pixel/Turnstile yang tersimpan: tetap dipakai sampai Tahap C menghapusnya.
4. **Form Cluster:**
   - Wajib hanya **nama**, **kawasan** (pilihan "Cluster mandiri (tanpa kawasan)" untuk cluster tanpa kawasan), dan **minimal 1 foto**.
   - Semua field punya placeholder contoh nyata.
   - Aksi **Duplikat cluster** (tabel & halaman edit) menyalin data, tipe rumah, benefit, foto, brosur/pricelist. Salinan belum dipublikasikan dan diberi nama "… (salinan)"; catatan internal & prioritas tidak ikut.
   - Menu Kategori Fasilitas dan Tag disembunyikan. Keduanya dibuat langsung dari form Fasilitas/Artikel.
5. **Angka akhir** (cara hitung sama dengan audit: field form resource, relation manager, dan halaman admin, termasuk yang tersembunyi; repeater = 1):

   | | Audit (sebelum A) | Sesudah A | Sesudah B |
   |---|---|---|---|
   | Field admin | ±700 | 691 | **227** (wajib 28) |
   | Menu | 30 | 29 | **19** |

   Halaman Promo lama (tersembunyi, 18 field) ikut dihitung.
- **Test baru:** wajib nama/kawasan/foto, slug/ringkasan/tahun otomatis, duplikat cluster, 4 menu pengaturan, simpan multi-settings, teks tetap di kode, section otomatis tersembunyi, dan teks contoh dianggap kosong. Test lama untuk toggle/label/field yang dihapus disesuaikan.

**Tahap C — WhatsApp only + tracking Google** (disetujui pemilik 5 Okt 2026, langsung di `main`)

*Lead: form dihapus, semua lewat WhatsApp.*

1. **Yang dihapus:**
   - Form lead (sidebar, inline, modal "Jadwalkan", form Kontak) dan Cloudflare Turnstile.
   - `POST /lead` beserta rate limit, honeypot, dan normalisasi nomor.
   - Job `SendMetaLeadEvent`, email notifikasi lead, dan cookie atribusi UTM.
   - Menu Lead dan 3 widget lead di dasbor.
   - Halaman `/terima-kasih`, sekarang **301 ke beranda**. Settings halaman Terima Kasih, form Kontak, dan notifikasi lead ikut dibuang (settings migrasi `2026_10_05_200000_whatsapp_only_and_ga4`).
2. **Tabel `leads`:** migrasi `2026_10_05_200000_export_and_drop_leads_table` mengekspor semua lead ke `storage/app/backup/leads-{tanggal}.csv` (UTF-8 BOM, plus nama cluster/tipe) sebelum tabel, model, dan menu Lead dihapus. File yang sudah ada tidak ditimpa.
3. **Semua CTA jadi tombol WhatsApp** dengan pesan otomatis (`App\Support\WhatsApp`). Template bisa diubah di Pengaturan Umum dengan placeholder `{nama_cluster}`:
   - **Detail cluster** (kartu marketing, tab tipe): "Halo, saya tertarik dengan {nama_cluster}. Boleh minta info harga & brosurnya?" Di tab tipe, `{nama_cluster}` = "Cluster Tipe X".
   - **Promo & Benefit:** "Halo, saya tertarik dengan promo di {nama_cluster}. Boleh minta informasi lengkapnya?"
   - **Jadwal survey** (kartu marketing, bar sticky mobile, CTA bawah): "Halo, saya ingin jadwalkan survey ke {nama_cluster}." Di luar Detail Rumah, `{nama_cluster}` diisi nama brand.
   - **Kontak/global:** "Halo, saya ingin konsultasi rumah di BSD City." Teks bawaan lama yang tersimpan ikut diganti; teks yang sudah diubah admin dipertahankan.
4. **Nomor WA:** nomor WA cluster kalau diisi, kalau kosong nomor global. Field "WhatsApp marketing default" di Pengaturan → Properti dihapus.
5. **Tombol WhatsApp melayang** di mobile & tablet (< 1280px) di semua halaman. Di Detail Rumah tombol ini memakai nomor & pesan cluster dan naik di atas bar harga. Ikon WA di header mobile dan tombol WA di bar sticky dihapus supaya tidak dobel.

*Tracking: tidak beriklan di Meta, fokus organik.*

6. **Dihapus:**
   - Meta Pixel dan Meta CAPI (`/track/contact`, job `SendMetaContactEvent`, `MetaConversions`, `MetaContext`, token & settings terenkripsi, `config/services.meta`).
   - GTM: loader, noscript, `dataLayer` event, dan domain CSP tambahan.
   - Domain CSP Meta, GTM, Turnstile, dan doubleclick iframe.
   - Key tracking lama yang tersimpan di database ikut dibuang.
7. **GA4 langsung lewat gtag.js:**
   - Dimuat setelah `load` + idle.
   - Measurement ID diisi di Pengaturan Umum; kalau kosong, tidak ada script tracking sama sekali.
   - CSP hanya domain GA4, Google Maps, dan YouTube.
8. **Event `click_whatsapp`:** parameter `cluster`, `posisi_tombol`, `halaman`, dengan `transport_type: 'beacon'`. Diuji di browser: event terkirim dengan parameter yang benar.
9. **Verifikasi Google Search Console** tetap di Pengaturan Umum. Ditemukan saat pengerjaan: meta tag verifikasi sebelumnya **tidak pernah dipasang** di `<head>`. Sekarang dipasang, dan admin boleh menempelkan seluruh meta tag.
10. **Kebijakan Privasi** tetap ada. Draf bawaan ditulis ulang tanpa form & Meta, dengan WhatsApp + GA4/cookie. Settings migrasi mengganti isi yang masih draf awal (`[ISI KEBIJAKAN PRIVASI …]`); teks yang sudah ditulis admin tidak ditimpa. Masih ada penanda `[WAJIB DITINJAU BAGIAN LEGAL…]` untuk ditinjau legal.
11. **`docs/TRACKING.md` ditulis ulang:** konversi utama `click_whatsapp` (Key event), custom dimension, nilai `posisi_tombol`, dan cara menguji. README dan CHECKLIST-LAUNCH ikut disesuaikan.

- **Test:**
  - `tests/Feature/WhatsAppTest.php`: template per konteks & dari admin, nomor cluster/global, endpoint lama hilang, 301 terima kasih, ekspor CSV leads, GA4 hanya kalau ID diisi (tanpa GTM/Pixel), meta verifikasi, CSP, Kebijakan Privasi.
  - `LeadTest` dihapus.
  - Test CSP/cache/SEO/halaman yang menyebut form, Pixel, atau terima kasih disesuaikan.
- **Perbaikan deploy (5 Okt 2026):** deploy pertama Tahap C gagal di settings migrasi. Cache daftar kelas settings di server masih memuat `ThankYouPageSettings` yang sudah dihapus. Migrasi ekspor & hapus `leads` sudah sempat berjalan. Script deploy sekarang menjalankan `php artisan optimize:clear` sebelum `migrate`. Kegagalan ini sudah direproduksi dan perbaikannya diuji di lokal.
- **Catatan migrasi lama:** dua settings migrasi lama (`2026_09_25_*`) membaca nilai bawaan yang sekarang sudah dihapus. Keduanya diberi fallback supaya instalasi baru (`migrate:fresh`) tetap jalan; di production keduanya sudah pernah jalan.

**Laporan akhir Tahap A + B + C**

| | Sebelum (main `bab693f`) | Sesudah A+B+C |
|---|---|---|
| JS bersama (gzip, semua halaman) | 116,5 KB | 112,8 KB |
| JS Beranda (total) | 128,0 KB | 124,3 KB |
| JS /properti | 128,0 KB | 124,3 KB |
| JS Detail Rumah | 132,3 KB | 128,6 KB |
| JS Detail Kawasan | 127,5 KB | 123,8 KB |
| JS Artikel | 125,8 KB | 120,9 KB |
| JS Kontak | 123,3 KB | 119,6 KB |
| Font Fraunces | 65,7 KB | 32,5 KB |
| Script pihak ketiga | GTM + GA4 + Meta Pixel + Turnstile (kalau diisi) | hanya gtag.js GA4 (kalau diisi) |
| Field admin | ±700 (audit) | **223** (wajib 27) |
| Menu admin | 30 (audit) | **18** |

Cara ukur JS: build production, gzip level 9 per file dari `manifest.json` (entry + import statis + chunk halaman), sama untuk kedua build. Field/menu dihitung dengan skrip audit yang sama.

**Cron, queue, dan SSR otomatis lewat deploy** (permintaan pemilik 5 Okt 2026; tidak ada akses SSH manual)

- **Cron** (`scripts/server/ensure-cron.sh`):
  - Baris `* * * * * cd <folder aplikasi> && php artisan schedule:run …` ditambahkan kalau belum ada. Crontab lama dibaca lalu ditambah di bawahnya; baris lain, termasuk milik aplikasi `craxionstudio`, tidak diubah.
  - Saat deploy, job yang tertunda diproses sekali dengan `queue:work --force --stop-when-empty`. `--force` diperlukan karena tanpa itu worker langsung berhenti selama maintenance mode (ditemukan saat simulasi).
- **SSR** (`scripts/server/ensure-ssr.sh`):
  - **Supervisor** kalau ada atau bisa dipasang (root / sudo tanpa password): program `ssr-<nama-folder>`, autostart + autorestart, di-restart setiap deploy.
  - Tanpa root: cron `@reboot` + penjaga tiap menit (`scripts/server/ssr-watchdog.sh`, `setsid nohup`, `flock`).
  - Server SSR sekarang hanya mendengarkan `127.0.0.1` (sebelumnya `0.0.0.0`). Port bisa diganti lewat `INERTIA_SSR_PORT` / `INERTIA_SSR_URL`.
  - Kalau port SSR dipakai proses aplikasi lain (dicek lewat folder kerja proses), proses itu tidak dihentikan dan deploy memberi peringatan.
- **Cek akhir deploy:**
  - `php artisan ssr:check /properti` (baru) merender halaman lewat kernel aplikasi tanpa cache, lalu memastikan ada `data-server-rendered` dan H1. Kalau gagal, muncul peringatan "SSR GAGAL" di ringkasan Actions.
  - Heartbeat scheduler ditunggu paling lama 75 detik sebagai bukti cron jalan.
  - `php artisan ops:queue-status` (baru) dipakai untuk laporan antrean.
- **Cache halaman:** HTML yang gagal dirender server tidak lagi disimpan, supaya versi "div kosong" tidak tertahan setelah SSR hidup lagi.
- **Diuji di container:**
  - Jalur tanpa root (user biasa + cron daemon): cron ditambah sekali, baris aplikasi lain utuh, 4 job diproses, SSR hidup lagi setelah proses dibunuh, port milik proses lain tidak disentuh.
  - Jalur Supervisor (root): pemasangan otomatis, konfigurasi ditulis sekali, restart per deploy, autorestart setelah crash.
  - Simulasi langkah 9–12 deploy berakhir dengan notice SSR OK dan heartbeat.
- **Test:** `tests/Feature/ServerOpsTest.php` (ssr:check gagal/lolos, ops:queue-status, jadwal scheduler) dan test cache halaman tanpa SSR.
- **Perbaikan setelah deploy pertama:**
  - Cron sudah terpasang, 4 job WebP (sejak 29 Sep) sudah diproses, dan SSR sudah jalan lewat penjaga cron tanpa root. Tapi deploy berhenti di langkah 12, karena dua hal:
    - (1) `ssr:check` memakai host `localhost`, sehingga di production dialihkan 301 ke host kanonik. Sekarang memakai `APP_URL`.
    - (2) `script_stop` di ssh-action memeriksa exit code setelah setiap baris, sehingga cabang `else` pada if/else multi-baris menghentikan deploy. Langkah 12 sekarang memakai bentuk satu baris.
  - Kedua jalur (gagal & sukses) sudah diuji dengan simulasi `script_stop`.
  - Deploy kedua: cron terbukti jalan (heartbeat dari cron server). `ssr:check` gagal karena Inertia DevTools aktif di server dan foldernya (`storage/inertia-devtools`, milik user web server) tidak bisa ditulis user deploy. `ssr:check` sekarang mematikan DevTools selama cek.
  - DevTools hanya aktif otomatis di `APP_ENV=local`, jadi deploy sekarang memberi peringatan kalau `APP_ENV` server bukan `production` (kalau bukan production, semua halaman dikirim noindex).

**.env production aman + indeks Google terpisah (SITE_INDEXABLE)** (permintaan pemilik 5 Okt 2026, domain masih sementara)

- **Deploy (`scripts/server/ensure-env.sh`, langkah 4b):**
  - Mengubah `APP_ENV=production`, `APP_DEBUG=false`, `LOG_LEVEL=warning` (kalau kosong/debug), dan `INERTIA_DEVTOOLS_ENABLED=false`. Baris yang ada diubah, duplikat dibuang, key lain utuh.
  - Menambah `SITE_INDEXABLE=false` hanya kalau belum ada; tidak pernah diubah ke `true`.
  - Kalau ada perubahan: `.env` dibackup ke `.env.backup-{tanggal-jam}` (chmod 600), lalu `optimize:clear` + `config:cache`.
- **Rollback otomatis (`scripts/server/verify-env.sh`):**
  - Sebelum perubahan, deploy mencatat apakah `APP_URL` bisa dijangkau dari server.
  - Setelah `up`, deploy mengecek lagi dari luar. Kalau sebelumnya terjangkau tapi sekarang redirect loop / error, `.env` dikembalikan dari backup. Risiko loop ini ada karena production memaksa skema & host `APP_URL` dan proxy tidak di-trust.
  - Header beranda dan robots.txt dari luar ikut dilaporkan; nilai cookie disembunyikan.
- **`SITE_INDEXABLE`** (`config/site.php`, `App\Support\Indexing`), terpisah dari `APP_ENV`:
  - `false`: `X-Robots-Tag` + meta robots `noindex, nofollow` di semua halaman, robots.txt `Disallow: /` tanpa sitemap.
  - `true`: normal, plus sitemap.
  - Middleware `NoIndexOutsideProduction` diganti `NoIndexUntilIndexable`.
  - Status tampil di Pengaturan Umum → Google sebagai info (bukan field).
- **Laporan akhir deploy:** `php artisan ops:site-status` (APP_ENV, APP_DEBUG, LOG_LEVEL, DevTools, SITE_INDEXABLE, X-Robots-Tag beranda, robots.txt, paksa HTTPS/host, HSTS, cookie secure, cache halaman, CSP). Deploy memberi error kalau `SITE_INDEXABLE=false` tetapi beranda tidak noindex.
- **Perilaku yang ikut berubah karena `APP_ENV=production`:**
  - Paksa skema & host `APP_URL` (301).
  - HSTS 1 tahun untuk request HTTPS (tanpa includeSubDomains).
  - Cache halaman tamu aktif.
  - CSP aktif.
  - Inertia DevTools mati.
  - Perintah database destruktif (`migrate:fresh`, `db:wipe`) diblok.
  - Password admin baru wajib kuat (12+ karakter, huruf besar/kecil, angka, simbol, belum bocor).
  - Halaman error tanpa detail.
  - Cookie session belum diberi flag Secure (`SESSION_SECURE_COOKIE` tidak diatur).
- **Deploy pertama gagal di `git pull`:** `storage/framework/.gitignore` di server punya perubahan lokal, dan commit ini ikut mengubah file itu. Perubahan file tersebut dibatalkan (perubahan lokal di server tidak disentuh), dan file sementara deploy dipindah ke `storage/framework/cache/` yang sudah di-ignore. Website tetap live dengan versi sebelumnya selama itu.
- **Deploy kedua: rollback otomatis bekerja.** Setelah `APP_ENV=production`, website dari luar membalas **502** sehingga `.env` dikembalikan, dan website tetap jalan.
  - Penyebab: header respons production lebih dari 4 KB (header `Link` preload semua aset + nonce CSP + CSP + cookie), melewati buffer bawaan nginx. nginx membalas `upstream sent too big header`.
  - Sudah direproduksi dengan nginx (`proxy_buffer_size 4k`) di container.
  - Perbaikan: `AddLinkHeadersForPreloadedAssets::using(4)`, hanya 2 font, CSS, dan JS utama di header; sisanya tetap di-preload lewat `<link>` di HTML. Header turun ke ±2,9 KB dan semua halaman 200 lewat nginx.
  - Test regresi: header production < 3,5 KB.
- **Deploy ketiga: 502 hilang, tapi website membalas 500 lewat web server** (request internal tetap OK), dan `.env` kembali di-rollback otomatis. Langkah yang diambil:
  - Cache halaman dan cache layout dibuat tahan gagal: kalau cache tidak bisa dibaca/ditulis (mis. izin folder milik user lain), halaman tetap dikirim. Ada test untuk ini.
  - Saat gagal, `verify-env.sh` sekarang menampilkan diagnosis: user deploy & PHP-FPM, pemilik/izin folder storage & cache, dan baris pertama error terakhir di log Laravel.
- **Deploy keempat: penyebab 500 ketemu lewat diagnosis.** Log berisi `touch(): Utime failed: Operation not permitted` pada file view hasil kompilasi.
  - View Blade dikompilasi saat deploy (`php artisan optimize`) oleh user deploy, sedangkan PHP-FPM berjalan sebagai `www-data`.
  - Kalau timestamp view dicek, Laravel memanggil `touch()` pada file kompilasi itu, dan Linux menolak karena pemiliknya user lain.
  - Perbaikan: `config/view.php` mematikan `check_cache_timestamps` di production. Setiap deploy menghapus dan mengompilasi ulang semua view, jadi pengecekan ini tidak diperlukan. Di lokal tetap aktif; bisa diatur lewat `VIEW_CHECK_CACHE_TIMESTAMPS`.
  - Sudah direproduksi di container (file kompilasi milik root, PHP berjalan sebagai `nobody`): error muncul saat pengecekan aktif dan hilang saat dimatikan. Ada test regresi.
- **Diuji di container:** `.env` kotor (key dobel, tanda kutip, tanpa newline), dua kali jalan (kedua tanpa perubahan), `SITE_INDEXABLE=true` tidak disentuh, rollback saat situs error, dan simulasi langkah 4b + 12 dengan `script_stop`.


**Tombol WhatsApp melayang di semua layar + halaman Kontak dihapus** (permintaan pemilik 6 Okt 2026)

1. **Tombol WhatsApp melayang** di semua halaman, mobile dan desktop, pojok kanan bawah:
   - Mobile & tablet (< 1280px): ikon saja, 56px. Desktop: ikon + "Chat via WhatsApp".
   - Pesan dan nomor mengikuti halaman: di Detail Rumah memakai nomor WA cluster (kalau diisi) dan template cluster yang menyebut nama cluster. Di halaman lain memakai nomor global dan "Halo, saya ingin konsultasi rumah di BSD City."
   - Klik tercatat sebagai `click_whatsapp` dengan `posisi_tombol = floating`. Link dibuka di tab baru.
   - Tidak menutupi konten penting:
     - Footer diberi ruang bawah, jadi baris terakhir footer tidak tertutup.
     - Di Detail Rumah mobile, tombol tetap di atas bar harga sticky.
     - Di Detail Rumah desktop, kalau tombol menabrak kartu marketing yang sticky, tombol mengecil jadi ikon di margin kanan halaman. Elemen yang dihindari ditandai `data-floating-avoid`.
2. **Halaman Kontak dihapus:**
   - `/kontak` **301 ke beranda**.
   - Dihapus dari sitemap, dari tab "Kontak" di Pengaturan → Halaman Lain (judul, deskripsi, URL embed peta, SEO), dan dari import SEO halaman. Baris `/kontak` di JSON import dilewati tanpa peringatan.
   - Settings migrasi `2026_10_06_100000_remove_contact_page` membuang settings `page_contact.*`. Di Kebijakan Privasi, kalimat "lewat halaman Kontak" diganti "lewat WhatsApp atau email di bagian bawah situs"; sisa teks admin tidak diubah.
   - Ikut dihapus: `ContactController`, `Pages/Contact.tsx`, `ContactPageSettings`, dan domain embed peta Kontak di CSP.
3. **Menu "Kontak" langsung membuka WhatsApp** (nomor global, pesan "Halo, saya ingin konsultasi rumah di BSD City.", tab baru, `posisi_tombol = menu_kontak`):
   - Berlaku di header desktop, menu mobile, dan kolom "Perusahaan" di footer.
   - Menu Kontak selalu ada di akhir menu header. Item `/kontak` lama yang tersimpan di Menu Navigasi diabaikan supaya tidak dobel.
   - Kalau nomor WA global kosong, menu Kontak dan tombol WA mengarah ke info kontak di footer (`#info-kontak`), bukan lagi ke `/kontak`.
4. **Info kontak di footer semua halaman** (kolom "Kantor Pemasaran", isi dari Pengaturan Umum):
   - Isi: alamat, telepon (link `tel:`), WhatsApp (nomor global, `posisi_tombol = footer`), email (link `mailto:`), jam buka, dan link Google Maps.
   - Field baru **Link Google Maps kantor** di Pengaturan Umum → Kontak. Kalau kosong tapi koordinat ada, link dibuat dari koordinat; kalau keduanya kosong, link tidak tampil.
   - Teks contoh `[...]` dan field kosong tidak tampil.
5. **JSON-LD kantor pemasaran** (`RealEstateAgent`: alamat, telepon, email, jam buka, koordinat) sekarang ada di layout global, yaitu semua halaman, bukan hanya Beranda & Kontak. `@id`/`url` menunjuk ke beranda. `Organization` tetap di semua halaman.
- **Catatan migrasi lama:** migrasi rebrand (`2026_09_29_100000`) sekarang melewati settings yang sudah dihapus, supaya tetap bisa dijalankan ulang. `database/settings/defaults/page_contact.php` tetap ada karena dipakai migrasi lama (pola sama dengan Terima Kasih).
- **Test:** `tests/Feature/WhatsAppTest.php` (+6): 301 & sitemap, menu Kontak WA di header/drawer/footer (tanpa dobel), fallback tanpa nomor, info kontak footer (placeholder disembunyikan, link Maps dari koordinat), JSON-LD di semua halaman, dan settings/field admin Kontak terhapus. Test lama yang membuka `/kontak` disesuaikan.


**WhatsApp: satu nomor, link langsung, template dengan link halaman** (permintaan pemilik 6 Okt 2026)

1. **Satu nomor:** semua tombol/link WhatsApp memakai nomor WA global di Pengaturan Umum.
   - Field "WhatsApp cluster" di form Cluster → Marketing dihapus.
   - Kolom `clusters.marketing_whatsapp` di-drop lewat migrasi `2026_10_06_200000_drop_marketing_whatsapp_from_clusters`.
   - Logika fallback nomor cluster → global dihapus.
2. **Selalu link langsung** `https://wa.me/{nomor}?text={pesan}`:
   - Pesan di-URL-encode (RFC 3986: spasi `%20`, baris baru `%0A`). Baris baru Windows dari textarea admin disamakan jadi `%0A`.
   - Fallback `#info-kontak` dihapus. Kalau nomor di database masih kosong, link tetap `https://wa.me/?text=…`.
   - Menu Kontak, tombol melayang, dan nomor WA di footer selalu dibuka di tab baru.
3. **Nomor wajib:**
   - Di Pengaturan Umum nomor tidak bisa disimpan kosong, dengan validasi format `62` + 8–13 digit.
   - Selama nomor di database kosong, dashboard admin menampilkan peringatan "Nomor WhatsApp belum diisi" (widget `WhatsappNumberWarning`).
   - Ringkasan deploy GitHub Actions memberi `::warning` "Nomor WhatsApp kosong". `ops:site-status` sekarang punya baris "Nomor WhatsApp".
4. **Template pesan** (Pengaturan Umum, satu per konteks). Bawaan baru, baris kedua `{link_halaman}`:
   - **Detail cluster:** tombol utama, tab tipe, CTA, dan tombol melayang di Detail Rumah.
   - **Promo & Benefit.**
   - **Jadwal survey.**
   - **Halaman kawasan (baru):** CTA dan tombol melayang di Detail Kawasan.
   - **Tombol melayang & menu Kontak (halaman lain):** juga header dan footer.

   Placeholder: `{nama_cluster}`, `{nama_kawasan}`, `{judul_halaman}`, `{link_halaman}`. Placeholder lama `{cluster}` tetap didukung.
   - Di bawah tiap field ada daftar placeholder dan contoh hasil. Contohnya memakai Castilo at Terravia, kawasan Greenwich Park, atau /properti, dan ikut berubah saat template diketik.
   - Settings migrasi `2026_10_06_200000_whatsapp_templates_with_link` mengganti template yang masih teks bawaan lama dengan bawaan baru dan menambah template kawasan. Teks yang sudah diubah admin tidak ditimpa.
5. **`{link_halaman}`** = APP_URL + path halaman yang sedang dibuka, tanpa query string (`?tipe=`, `utm_*` dibuang). **`{judul_halaman}`** = title halaman. Placeholder yang tidak dipakai di template tidak menambah apa pun.
   - Data layout (header, menu, footer) tetap di-cache. Link WhatsApp di dalamnya diisi ulang per halaman saat dirender, jadi tiap halaman membawa link-nya sendiri.
6. **GA4:** klik tetap tercatat sebagai `click_whatsapp` (parameter `cluster`, `posisi_tombol`, `halaman`). Tidak ada perubahan di `analytics.ts`.
- **Test** (`WhatsAppTest` +10, `BankBenefitTest`, `SiteLayoutTest` disesuaikan):
  - Encoding (`&`, `?`, `,`, spasi, `%0A`, `\r\n`) dan normalisasi nomor.
  - Link Detail Rumah lengkap tanpa query string.
  - Semua placeholder, dan tanpa link kalau placeholder tidak dipakai.
  - Template kawasan, dan link per halaman walau layout di-cache.
  - Migrasi template, nomor wajib di admin, contoh hasil di admin, dan peringatan dashboard/deploy.

---

## Revisi 1 — 23 Sep 2026: pola repo rezabsd

- SQLite untuk lokal, MySQL 8 untuk production.
- Ikuti struktur dan format README repo `craxionstudio/rezabsd`.
- Build, migrate, dan test harus lolos di sesi sebelum push.
