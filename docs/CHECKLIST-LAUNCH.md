# Checklist Go-Live

Daftar yang harus diisi atau dicek pemilik sebelum website dibuka untuk publik. Centang satu per satu. Langkah teknis lengkapnya ada di `README.md` bagian **Deploy ke VPS**.

> Semua pengaturan "Admin → …" ada di `/admin`. Hanya ada satu role (Admin) dengan akses semua menu. Pengaturan ada di 4 menu: Beranda, Properti, Halaman Lain, Pengaturan Umum.

## 1. Server & `.env`

- [ ] **PHP 8.3.6+** dengan semua extension wajib (README → Requirement: jalankan cek `php -m` di sana), lalu `composer install --no-dev --optimize-autoloader` **tanpa** `--ignore-platform-reqs` dan `composer check-platform-reqs` semua "success".

- [ ] `APP_ENV=production` dan `APP_DEBUG=false`.
- [ ] `APP_KEY` sudah dibuat (`php artisan key:generate`) dan **disimpan di tempat aman**.
- [ ] **`APP_URL` persis domain final:** `https://`, pilih **salah satu** www atau non-www, tanpa garis miring di akhir (mis. `https://arunikaland.co.id`). Semua request http atau varian www/non-www lain otomatis di-301 ke sini, dan nilai ini dipakai untuk canonical, sitemap, OG, dan JSON-LD.
- [ ] HTTPS aktif (sertifikat valid). HSTS otomatis aktif di production.
- [ ] Database MySQL 8 (`DB_*`), lalu `php artisan migrate --force`.
- [ ] Deploy pertama: `php artisan db:seed --force` untuk isi awal (settings, contoh konten, 3 akun admin). Di production, password akun dicetak sekali di terminal (atau pakai `SEED_ADMIN_PASSWORD`). Catat.
- [ ] `php artisan storage:link` dan `php artisan optimize`.
- [ ] `npm ci && npm run build` (aset + bundle SSR).
- [ ] **MAIL_*** diisi (SMTP / layanan email) dan `MAIL_FROM_ADDRESS` memakai domain sendiri. Dipakai untuk email notifikasi backup gagal.
- [ ] **Queue worker**: dijalankan scheduler tiap menit (`queue:work --stop-when-empty`). Cron-nya dipasang otomatis oleh deploy. Cek ringkasan deploy GitHub Actions: notice "Queue" (job sebelum/sesudah) dan "Cron" (heartbeat scheduler).
- [ ] **SSR** dipasang otomatis oleh deploy (Supervisor, atau penjaga cron tanpa root). Cek ringkasan deploy: notice "SSR OK /properti". Cek manual: `curl -s https://domain/properti | grep "<h1"` harus mengembalikan judul.
- [ ] **Scheduler cron** dipasang otomatis oleh deploy (`scripts/server/ensure-cron.sh`): `* * * * * cd <folder aplikasi> && php artisan schedule:run >> /dev/null 2>&1`. Isinya: queue worker tiap menit, sitemap harian, backup harian 01.30, pembersihan backup, dan monitor backup.
- [ ] **Backup:** `mysqldump` tersedia di server dan `BACKUP_NOTIFICATION_EMAIL` diisi.
- [ ] **`mysqldump` terpasang di server** (paket `mysql-client` atau `mariadb-client`): dipakai `php artisan backup:run --only-db --disable-notifications`, yang juga dijalankan script deploy sebelum setiap import data. Tes sekali: perintah itu harus berakhir dengan "Backup completed!" dan `php artisan backup:list` menampilkan file barunya.
- [ ] **Backup di luar server** (wajib: kalau server rusak, backup `local` ikut hilang). Pilihan murah yang kompatibel S3 (driver `s3` sudah terpasang):
  - **Cloudflare R2**: tanpa biaya egress, gratis 10 GB/bulan. Dashboard Cloudflare → R2 → buat bucket (mis. `arunika-backup`) → *Manage R2 API Tokens* → token dengan izin *Object Read & Write* untuk bucket itu.

    ```dotenv
    BACKUP_DISKS=local,s3
    AWS_ACCESS_KEY_ID=<access key id token R2>
    AWS_SECRET_ACCESS_KEY=<secret access key token R2>
    AWS_DEFAULT_REGION=auto
    AWS_BUCKET=arunika-backup
    AWS_ENDPOINT=https://<ACCOUNT_ID>.r2.cloudflarestorage.com
    AWS_USE_PATH_STYLE_ENDPOINT=true
    ```

  - **Backblaze B2**: murah per GB, gratis 10 GB. Buat bucket **private** → *Application Keys* → key khusus bucket itu. Region & endpoint tertulis di detail bucket (mis. `s3.us-west-004.backblazeb2.com`).

    ```dotenv
    BACKUP_DISKS=local,s3
    AWS_ACCESS_KEY_ID=<keyID>
    AWS_SECRET_ACCESS_KEY=<applicationKey>
    AWS_DEFAULT_REGION=us-west-004
    AWS_BUCKET=arunika-backup
    AWS_ENDPOINT=https://s3.us-west-004.backblazeb2.com
    AWS_USE_PATH_STYLE_ENDPOINT=true
    ```

  - Bucket harus **private** (jangan publik). Jalankan `php artisan config:clear`, lalu tes: `php artisan backup:run`, lalu `php artisan backup:list` harus menampilkan backup di disk `local` **dan** `s3`. Cek juga file `.zip`-nya muncul di bucket.
  - Aturan penghapusan backup lama (`backup:clean`, default: semua 7 hari, harian 16 hari, lalu mingguan/bulanan) berlaku juga di bucket.
- [ ] Redis untuk cache/session/queue (opsional, disarankan): `CACHE_STORE`, `SESSION_DRIVER`, `QUEUE_CONNECTION`.
- [ ] Web server: kompresi gzip/brotli + cache aset `/build/*` 1 tahun (contoh nginx di README). Kalau pakai Cloudflare: Brotli, HTTP/3, "Always Use HTTPS"; jangan cache HTML di edge.

## 2. Akun admin

- [ ] Login dengan akun seeder, lalu **ganti email dan password** ketiganya: Admin → Sistem → User. Email bawaan: `admin@example.com`, `konten@example.com`, `marketing@example.com`.
- [ ] Hapus akun yang tidak dipakai, dan buat akun per orang (jangan berbagi akun).

## 3. Kontak & WhatsApp

- [ ] **Nomor WhatsApp** format `62…` di Admin → Pengaturan Umum → WhatsApp. Wajib diisi; satu nomor untuk semua tombol WA (tidak ada nomor per cluster). Selama kosong, dashboard admin dan ringkasan deploy menampilkan peringatan. Cek juga 5 template pesan (cluster, promo, survey, kawasan, umum) beserta contoh hasilnya.
- [ ] Hotline, telepon, email, alamat kantor pemasaran, dan jam buka (tampil di footer semua halaman & JSON-LD). Teks contoh `[...]` tidak tampil di footer.
- [ ] Template pesan WA default dan per halaman (Detail Rumah menyebut nama cluster & tipe).
- [ ] Link Google Maps kantor di Admin → Pengaturan Umum → Kontak (tampil di footer; kosong = tidak tampil).

## 4. Tracking (GA4)

Panduan lengkap: `docs/TRACKING.md`.

- [ ] **GA4 Measurement ID** (`G-XXXXXXXXXX`) di Admin → Pengaturan Umum → Google.
- [ ] GA4: setelah klik WhatsApp pertama, tandai `click_whatsapp` sebagai **Key event**, dan buat custom dimension `cluster`, `posisi_tombol`, `halaman` (scope Event).
- [ ] Uji di Tag Assistant / DebugView: klik tombol WhatsApp di beberapa halaman, event `click_whatsapp` muncul dengan parameter yang benar.

## 5. Konten: ganti semua data dummy

- [ ] **Import data asli properti** (sekali, setelah deploy dan `migrate`):
  ```bash
  php artisan import:bsd-data --fresh   # hapus kawasan/cluster/tipe contoh, nonaktifkan konten contoh, lalu import docs/data/bsd-city-data.json
  ```
  Lalu `php artisan import:bsd-update` untuk `bsd-city-update-2.json` (tanggal launching), `-3.json` (tampilan cluster/kawasan), dan `-4.json` (benefit per cluster), berurutan.
  Cek hasilnya: 23 kawasan, 144 cluster, 87 tipe rumah. Setelah itu cukup `php artisan import:bsd-data` (tanpa `--fresh`) kalau JSON diperbarui. **Jangan** pakai `--fresh` lagi setelah admin mulai melengkapi data: `--fresh` menghapus semua kawasan & cluster.
- [ ] **Promo & Benefit:** benefit hasil update-4 (sumber: halaman resmi, periode September 2026) langsung tampil. Konfirmasi angka diskon ke marketing BSD dan perbarui di Admin → Cluster → tab **Promo & Benefit** (atau nonaktifkan benefit di Bank Benefit).
- [ ] Lengkapi data per cluster mulai dari 10 prioritas (`docs/data/BELUM-LENGKAP.md`, atau Admin → Cluster → filter **Belum lengkap**, urutkan **Prioritas**). Centang item di tab **Internal** setelah dilengkapi.
- [ ] Teks Arunika sudah diganti BSD City otomatis saat `migrate` (hanya teks yang belum diubah admin). Cek sekilas Pengaturan Global (nama brand, tagline) dan hero Beranda.
- [ ] Setelah ada fasilitas/artikel/pengembangan mendatang yang asli: cukup publikasikan, section **Fasilitas**, **Pengembangan Mendatang**, dan **Artikel & Berita** di Beranda tampil otomatis. Selama kosong, `/fasilitas` dan `/artikel` menampilkan keadaan kosong.
- [ ] Ganti semua data di **`docs/DATA-DUMMY.md`** (LB, kamar mandi, carport, sisa unit, lokasi fasilitas, fasilitas kawasan). Selama belum diganti, field di admin bertanda kuning **Data dummy**.
- [ ] Cari dan ganti semua teks dalam kurung siku `[...]`: nama PT, alamat, telepon, email, `[XX]`, `[TAHUN]`, `[VISI PERUSAHAAN]`, `[MISI …]`, `[NAMA MARKETING]`, `[NAMA NARASUMBER]`, dll. Cek di setiap Pengaturan Halaman, Profil Developer, Profil Lokasi, Kawasan, Cluster, Artikel, dan Future Development. Setelah itu, cari `[` di halaman publik.
- [ ] Unggah foto asli: hero Beranda, galeri cluster (foto pertama = foto utama), hero kawasan, denah tipe, cover artikel, foto fasilitas, foto marketing, logo, dan favicon. Isi **alt text** di setiap foto.
- [ ] Brosur & pricelist PDF per cluster/kawasan.
- [ ] Harga, cicilan, booking fee, status unit, dan benefit sesuai kondisi terbaru.
- [ ] **Kebijakan Privasi**: isi lengkap sesuai UU PDP (dikaji bagian legal) dan tanggal berlaku.
- [ ] Artikel contoh: hapus atau ganti dengan artikel asli (penulis asli + foto).
- [ ] Pengaturan Umum → Kontak → **Media sosial** (Instagram/TikTok/YouTube/Facebook resmi; selama kosong tidak tampil di footer) dan Lanjutan → gambar share default (opsional; bawaan: `public/og/*.png`).
- [ ] Setelah semua diganti, jalankan `php artisan cache:clear` sekali (cache halaman juga dibuang otomatis setiap kali menyimpan di admin).

## 6. Search engine

- [ ] **Google Search Console:** tambah properti domain, isi kode verifikasi (meta tag) di Pengaturan Umum → Google, lalu klik Verify.
- [ ] **Submit sitemap** `https://domain/sitemap.xml` di Search Console (Bing Webmaster Tools bisa impor dari Search Console).
- [ ] **Domain final siap:** ubah `SITE_INDEXABLE=true` di `.env` server (deploy tidak pernah mengubahnya otomatis), lalu `php artisan config:cache`. Selama `false` semua halaman noindex.
- [ ] Cek `https://domain/robots.txt`: harus `Allow: /` dengan blok `/admin` dan `/livewire`, plus baris `Sitemap:`. Kalau yang muncul `Disallow: /`, berarti `SITE_INDEXABLE` masih `false`.
- [ ] Cek satu halaman di **Rich Results Test** / Schema Markup Validator (Beranda, Detail Rumah, Detail Artikel).
- [ ] Jalankan pemeriksaan SSR semua URL sitemap: `php artisan qa:pages` (harus "0 bermasalah").

## 7. Tes end-to-end

- [ ] Tombol WhatsApp di header, tombol melayang (mobile & desktop), menu Kontak (header, menu mobile, footer), nomor WhatsApp di footer, CTA, kartu marketing, section Promo & Benefit, dan Jadwalkan Survey membuka nomor WA global dengan pesan otomatis yang sesuai (termasuk link halaman yang sedang dibuka).
- [ ] `/terima-kasih` dan `/kontak` diarahkan ke beranda (301).
- [ ] Buka website di HP sungguhan (Android & iPhone): menu, galeri, tab tipe, filter listing, tombol WhatsApp melayang, peta.

## 8. Performa

- [ ] **PageSpeed Insights** (mobile) untuk Beranda, `/properti`, satu Detail Rumah, dan satu artikel, **setelah** foto asli diunggah dan ID tracking diisi. Target: Performance ≥ 90, LCP < 2,5 dtk, CLS < 0,1. Hasil audit lab sebelum go-live: Performance 94–97, lainnya 100 (lihat `docs/QA.md`).
- [ ] Setelah foto diunggah, cek versi WebP (dan AVIF kalau server mendukung, lihat README → Requirement) sudah dibuat. Header respons foto di tab Network harus `image/webp` (atau `image/avif`). Kalau belum, pastikan queue worker jalan, atau jalankan `php artisan images:variants`.
- [ ] Cek header `X-Page-Cache: HIT` di request kedua halaman publik (cache halaman aktif).

## 9. Setelah go-live

- [ ] Pantau Search Console (Coverage/Pages) 1–2 minggu pertama.
- [ ] Pantau email notifikasi backup gagal (email hanya dikirim kalau ada masalah).
- [ ] Pantau key event `click_whatsapp` di GA4 per cluster dan posisi tombol.
