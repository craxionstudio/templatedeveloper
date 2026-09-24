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

---

## Revisi 1 — 23 Sep 2026: pola repo rezabsd

- SQLite untuk lokal, MySQL 8 untuk production.
- Ikuti struktur dan format README repo `craxionstudio/rezabsd`.
- Build, migrate, dan test harus lolos di sesi sebelum push.
