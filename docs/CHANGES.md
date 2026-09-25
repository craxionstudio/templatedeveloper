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

---

## Revisi 1 — 23 Sep 2026: pola repo rezabsd

- SQLite untuk lokal, MySQL 8 untuk production.
- Ikuti struktur dan format README repo `craxionstudio/rezabsd`.
- Build, migrate, dan test harus lolos di sesi sebelum push.
