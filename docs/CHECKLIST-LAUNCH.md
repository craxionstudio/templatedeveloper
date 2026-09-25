# Checklist Go-Live

Daftar yang harus diisi atau dicek pemilik sebelum website dibuka untuk publik. Centang satu per satu. Langkah teknis lengkapnya ada di `README.md` bagian **Deploy ke VPS**.

> Semua pengaturan "Admin → …" ada di `/admin`. Tab **Tracking & verifikasi** dan **Notifikasi lead** di Pengaturan Global hanya terlihat oleh **Super Admin**.

## 1. Server & `.env`

- [ ] `APP_ENV=production` dan `APP_DEBUG=false`.
- [ ] `APP_KEY` sudah dibuat (`php artisan key:generate`) dan **disimpan di tempat aman**. Access token CAPI dan secret key Turnstile dienkripsi dengan key ini; kalau `APP_KEY` diganti, isi ulang keduanya.
- [ ] **`APP_URL` persis domain final:** `https://`, pilih **salah satu** www atau non-www, tanpa garis miring di akhir (mis. `https://arunikaland.co.id`). Semua request http atau varian www/non-www lain otomatis di-301 ke sini, dan nilai ini dipakai untuk canonical, sitemap, OG, dan JSON-LD.
- [ ] HTTPS aktif (sertifikat valid). HSTS otomatis aktif di production.
- [ ] Database MySQL 8 (`DB_*`), lalu `php artisan migrate --force`.
- [ ] Deploy pertama: `php artisan db:seed --force` untuk isi awal (settings, contoh konten, 3 akun admin). Di production, password akun dicetak sekali di terminal (atau pakai `SEED_ADMIN_PASSWORD`). Catat.
- [ ] `php artisan storage:link` dan `php artisan optimize`.
- [ ] `npm ci && npm run build` (aset + bundle SSR).
- [ ] **MAIL_*** diisi (SMTP / layanan email) dan `MAIL_FROM_ADDRESS` memakai domain sendiri. Tes: kirim form lead, lalu pastikan email notifikasi masuk (cek juga folder spam).
- [ ] **Queue worker** jalan terus lewat Supervisor (`php artisan queue:work`). Tanpa worker: email lead tidak terkirim, event CAPI tidak dikirim, dan foto tidak dibuatkan versi AVIF/WebP.
- [ ] **SSR** jalan terus lewat Supervisor (`php artisan inertia:start-ssr`). Cek: `curl -s https://domain/ | grep "<h1"` harus mengembalikan judul.
- [ ] **Scheduler cron:** `* * * * * cd /var/www/arunika && php artisan schedule:run >> /dev/null 2>&1`. Isinya: sitemap harian, backup harian 01.30, pembersihan backup, dan monitor backup.
- [ ] **Backup:** `mysqldump` tersedia di server dan `BACKUP_NOTIFICATION_EMAIL` diisi.
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

- [ ] **Nomor WhatsApp** format `62…` di Admin → Pengaturan Global → Kontak. Selama kosong, semua tombol WA diarahkan ke halaman Kontak dan tombol "Lanjut chat WhatsApp" di halaman terima kasih tidak muncul.
- [ ] Nomor WA marketing per cluster (opsional): Admin → Properti → Cluster → Marketing.
- [ ] Hotline, telepon, email, alamat kantor pemasaran, jam buka, dan koordinat (untuk peta dan JSON-LD).
- [ ] Template pesan WA default dan per halaman (Detail Rumah menyebut nama cluster & tipe).
- [ ] URL embed Google Maps di Admin → Pengaturan Halaman → Kontak → Peta.

## 4. Tracking & iklan

Panduan lengkap: `docs/TRACKING.md`.

- [ ] **GTM ID** (`GTM-XXXXXXX`) di Admin → Pengaturan Global → Tracking & verifikasi.
- [ ] Di GTM: buat variabel, trigger, dan tag GA4 + Meta Pixel sesuai `docs/TRACKING.md`, lalu **Publish** container.
- [ ] Kolom **GA4 ID** dan **Meta Pixel ID** langsung **dikosongkan** kalau GA4/Pixel sudah dipasang lewat GTM, supaya event tidak terhitung dua kali.
- [ ] **Pixel ID untuk Conversions API** (Pixel yang sama dengan di GTM) + **access token CAPI** (Events Manager → Settings → Conversions API → Generate access token).
- [ ] Uji deduplikasi: isi **Test event code**, kirim form lead, lalu di Events Manager → Test events harus muncul `Lead` dari Browser dan dari Server dengan status *Deduplicated*. **Kosongkan** test event code setelah selesai.
- [ ] GA4: tandai `generate_lead` sebagai *Key event*. Kalau ada Google Ads, impor konversinya.
- [ ] Tag GTM Custom HTML **jangan** memakai opsi "Support document.write" (diblok CSP).
- [ ] Setiap tag pihak ketiga selain GA4/Meta (TikTok Pixel, Google Ads, dll.): tambahkan domainnya di **Tracking & verifikasi → Domain tambahan CSP**, lalu pastikan tidak ada pesan *Content Security Policy* di Console browser (langkah & contoh domain: `docs/TRACKING.md` → "Menambah tag baru di GTM").

## 5. Anti-spam & notifikasi lead

- [ ] **Cloudflare Turnstile:** buat widget di dashboard Cloudflare untuk domain final, lalu isi **site key dan secret key** di Tracking & verifikasi. Keduanya harus terisi; kalau salah satu kosong, Turnstile dilewati.
- [ ] **Email penerima notifikasi lead** (bisa lebih dari satu) di Pengaturan Global → Notifikasi lead.
- [ ] Webhook (opsional) untuk WA gateway / Google Sheet.

## 6. Konten: ganti semua data dummy

- [ ] Ganti semua data di **`docs/DATA-DUMMY.md`** (LB, kamar mandi, carport, sisa unit, lokasi fasilitas, fasilitas kawasan). Selama belum diganti, field di admin bertanda kuning **Data dummy**.
- [ ] Cari dan ganti semua teks dalam kurung siku `[...]`: nama PT, alamat, telepon, email, `[XX]`, `[TAHUN]`, `[VISI PERUSAHAAN]`, `[MISI …]`, `[NAMA MARKETING]`, `[NAMA NARASUMBER]`, dll. Cek di setiap Pengaturan Halaman, Profil Developer, Profil Lokasi, Kawasan, Cluster, Artikel, Promo, dan Future Development. Setelah itu, cari `[` di halaman publik.
- [ ] Unggah foto asli: hero Beranda, galeri cluster (foto pertama = foto utama), hero kawasan, denah tipe, cover artikel, foto fasilitas, foto marketing, logo, dan favicon. Isi **alt text** di setiap foto.
- [ ] Brosur & pricelist PDF per cluster/kawasan.
- [ ] Harga, cicilan, booking fee, status unit, dan periode promo sesuai kondisi terbaru.
- [ ] **Kebijakan Privasi**: isi lengkap sesuai UU PDP (dikaji bagian legal) dan tanggal berlaku.
- [ ] Teks halaman Terima Kasih dan 404.
- [ ] Artikel contoh: hapus atau ganti dengan artikel asli (penulis asli + foto).
- [ ] Pengaturan Global → SEO default: pola title, OG image default (1200×630, opsional; bawaan: `public/og/*.png`), dan `sameAs` (URL Instagram/TikTok/YouTube/Facebook resmi). Link sosial di footer masih `#`, jadi ganti juga.
- [ ] Setelah semua diganti, jalankan `php artisan cache:clear` sekali (cache halaman juga dibuang otomatis setiap kali menyimpan di admin).

## 7. Search engine

- [ ] **Google Search Console:** tambah properti domain, isi kode verifikasi (meta tag) di Tracking & verifikasi, lalu klik Verify.
- [ ] **Submit sitemap** `https://domain/sitemap.xml` di Search Console (dan Bing Webmaster Tools, verifikasi dengan kode Bing).
- [ ] Cek `https://domain/robots.txt`: harus `Allow: /` dengan blok `/admin`, `/livewire`, `/terima-kasih`, plus baris `Sitemap:`. Kalau yang muncul `Disallow: /`, berarti `APP_ENV` belum `production`.
- [ ] Cek satu halaman di **Rich Results Test** / Schema Markup Validator (Beranda, Detail Rumah, Detail Artikel).
- [ ] Jalankan pemeriksaan SSR semua URL sitemap: `php artisan qa:pages` (harus "0 bermasalah").

## 8. Tes end-to-end

- [ ] **Form lead** dari tiga tempat (Detail Rumah sidebar/inline, modal "Jadwalkan Kunjungan/Survey", halaman Kontak) dengan URL berparameter UTM (mis. `/?utm_source=tes&utm_campaign=golive`):
  - pindah ke `/terima-kasih`;
  - lead tersimpan di Admin → Lead lengkap dengan UTM, halaman form, posisi form, dan Event ID;
  - email notifikasi masuk;
  - event `Lead` masuk di Meta (Browser + Server) dan `generate_lead` di GA4 (DebugView);
  - tombol "Lanjut chat WhatsApp" membuka nomor yang benar dengan pesan berisi nama cluster.
- [ ] Newsletter di halaman Artikel: email tersimpan di Admin → Newsletter.
- [ ] Tombol WhatsApp di header, CTA, sticky bar mobile, dan kartu marketing membuka nomor yang benar.
- [ ] Hapus lead dan subscriber hasil tes.
- [ ] Buka website di HP sungguhan (Android & iPhone): menu, galeri, tab tipe, filter listing, form, peta.

## 9. Performa

- [ ] **PageSpeed Insights** (mobile) untuk Beranda, `/properti`, satu Detail Rumah, dan satu artikel, **setelah** foto asli diunggah dan ID tracking diisi. Target: Performance ≥ 90, LCP < 2,5 dtk, CLS < 0,1. Hasil audit lab sebelum go-live: Performance 94–97, lainnya 100 (lihat `docs/QA.md`).
- [ ] Setelah foto diunggah, cek versi AVIF/WebP sudah dibuat. Header respons foto di tab Network harus `image/avif` atau `image/webp`. Kalau belum, pastikan queue worker jalan, atau jalankan `php artisan images:variants`.
- [ ] Cek header `X-Page-Cache: HIT` di request kedua halaman publik (cache halaman aktif).

## 10. Setelah go-live

- [ ] Pantau Search Console (Coverage/Pages) 1–2 minggu pertama.
- [ ] Pantau email notifikasi backup gagal (email hanya dikirim kalau ada masalah).
- [ ] Bandingkan jumlah lead di admin dengan konversi di Meta/GA4 setelah seminggu.
