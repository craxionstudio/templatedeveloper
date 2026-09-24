# Data Dummy yang Harus Diganti

Daftar angka dan data yang **dikarang** saat membuat seeder karena tidak ada di desain (`docs/design/`). Semua nilai ini tampil di website dan wajib diganti dengan data asli lewat admin (`/admin`).

Selama nilainya belum diganti, field terkait di admin menampilkan penanda kuning **Data dummy**. Penanda hilang otomatis begitu nilainya diubah.

> Selain daftar di bawah, semua teks dalam kurung siku `[...]` (alamat, nomor telepon, nama PT, `[XX]`, `[TAHUN]`, dll.) juga data dummy.

Yang **bukan** dummy (persis desain): nama kawasan, nama cluster, nama tipe, kavling, luas tanah (LT), kamar tidur, harga mulai, cicilan mulai, jenis bangunan, badge, dan teks deskripsi yang tidak memakai kurung siku.

## 1. Tipe rumah: LB, kamar mandi, carport, sisa unit

Admin: **Properti → Cluster → (pilih cluster) → Tipe rumah → Ubah**.

| Kawasan | Cluster | Tipe | LB (m²) | Kamar mandi | Carport | Sisa unit |
|---|---|---|---:|---:|---:|---:|
| Arunika Garden | Vega Garden | Altair | 90 | 2 | 1 | 8 |
| Arunika Garden | Vega Garden | Deneb | 120 | 3 | 2 | 5 |
| Arunika Garden | Vega Garden | Rigel | 150 | 4 | 2 | 3 |
| Arunika Garden | Lyra Residence | Aster | 90 | 2 | 1 | 10 |
| Arunika Garden | Lyra Residence | Iris | 100 | 3 | 1 | 6 |
| Arunika Garden | Orion Park | Nova | 150 | 3 | 2 | 6 |
| Arunika Garden | Orion Park | Stella | 160 | 3 | 2 | 4 |
| Arunika Garden | Orion Park | Luna | 190 | 4 | 2 | 3 |
| Arunika Garden | Orion Park | Sol | 220 | 4 | 2 | 2 |
| Arunika Hills | Kirana Hills | Kirana | 155 | 4 | 2 | 7 |
| Arunika Hills | Kirana Hills | Kirana Plus | 180 | 4 | 2 | 4 |
| Arunika Hills | Nara Village | Nara | 110 | 3 | 1 | 9 |
| Arunika Hills | Nara Village | Nara Corner | 135 | 3 | 2 | 3 |
| Arunika Lakeside | Sora Terrace | Sora | 45 | 1 | 1 | 12 |
| Arunika Lakeside | Sora Terrace | Sora 2L | 70 | 2 | 1 | 8 |
| Cluster mandiri | Kalea Townhouse | Kalea | 140 | 3 | 1 | 6 |
| Arunika Lakeside | Sora Terrace II | Sora Plus | 80 | 2 | 1 | 20 |
| Arunika Lakeside | Sora Terrace II | Sora Corner | 100 | 2 | 1 | 6 |
| Cluster mandiri | Hana Residence | Hana | 110 | 2 | 1 | 7 |
| Cluster mandiri | Hana Residence | Hana Suite | 150 | 3 | 2 | 4 |

Beberapa LB cocok dengan kartu desain lama (Deneb 120, Aster 90, Stella 160, Kirana 155, Sora 45, Kalea 140), tapi tetap perlu dicek karena desain baru tidak mencantumkan LB.

Booking fee semua cluster (Rp 10 jt) juga dummy kecuali Vega Garden (dari desain 03). Admin: **Cluster → tab Harga**.

## 2. Lokasi fasilitas

Desain Fasilitas (04) masih memakai wilayah lama Serpong/Cibubur/Karawang. Saya petakan ke kawasan: Serpong → Arunika Garden, Cibubur → Arunika Hills, Karawang → Arunika Lakeside. Admin: **Konten → Fasilitas → Kawasan**.

| Fasilitas | Kawasan (dummy) |
|---|---|
| Central Park | Arunika Garden |
| Arunika Clubhouse | Arunika Garden |
| Arunika Walk | Arunika Garden |
| Sekolah [NAMA] | Arunika Hills |
| Klinik & Apotek 24 Jam | Arunika Garden |
| Sport Center | Arunika Hills |
| Shuttle Kawasan | Semua kawasan |
| Keamanan Terpadu | Semua kawasan |
| Rumah Ibadah | Arunika Lakeside |

## 3. Fasilitas kawasan Arunika Hills & Arunika Lakeside

Fasilitas Arunika Garden diambil dari desain (02c). Untuk dua kawasan lain tidak ada desainnya, jadi daftar ini dikarang dari data fasilitas. Admin: **Properti → Kawasan → tab Fasilitas kawasan**.

**Arunika Hills**

- Sekolah [NAMA] — TK hingga SMA
- Sport Center — Futsal, basket, badminton, padel
- One gate per cluster — Satpam 24 jam, CCTV

**Arunika Lakeside**

- Taman tepi danau — [KETERANGAN FASILITAS]
- Rumah Ibadah — Masjid dan gereja di dalam kawasan
- One gate per cluster — Satpam 24 jam, CCTV

Judul section "Tentang Kawasan" dan deskripsi kedua kawasan ini juga masih teks `[...]`.

## 4. Lainnya

- **Kategori fasilitas "Ibadah"** ditambahkan karena kartu Rumah Ibadah di desain memakai label itu (disetujui pemilik).
- **Tanggal promo**: mulai seminggu sebelum seeding, berakhir sebulan setelahnya. Admin: **Konten → Promo**.
- **Tanggal terbit cluster** dibuat berurutan supaya urutan "Terbaru" sama dengan desain.
- **Luas kawasan dan luas kota** dikosongkan (desain: `[XX] ha`). Isi di **Kawasan → Luas** dan **Profil Lokasi → Luas**.
