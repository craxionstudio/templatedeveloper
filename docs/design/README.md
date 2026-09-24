# Design Reference — Website Developer Perumahan

Referensi visual untuk implementasi (Revisi 2, model Kawasan). Perubahan dari versi sebelumnya ada di `docs/CHANGES.md`.

## Isi

| Folder | Isi |
|---|---|
| `desktop/` | 8 halaman versi desktop (lebar 1440px), HTML statis dengan inline style |
| `mobile/` | 8 halaman versi mobile (lebar 390px) |
| `screenshots/` | Screenshot full-page tiap halaman (PNG), sudah dengan font asli |
| `fonts/` | Fraunces + Plus Jakarta Sans (woff2) yang dipakai HTML di atas. Boleh dipakai ulang untuk self-host di aplikasi |

| File | Halaman | URL di aplikasi |
|---|---|---|
| `01-home` | Beranda | `/` |
| `02a-properti-cluster` | Produk Listing, tampilan Cluster (default) | `/properti` |
| `02b-properti-kawasan` | Produk Listing, tampilan Kawasan | `/properti/kawasan` |
| `02c-detail-kawasan` | Detail Kawasan (contoh: Arunika Garden) | `/properti/kawasan/{slug}` |
| `03-detail-rumah` | Detail Rumah (contoh: Vega Garden, tipe Deneb) | `/properti/{slug-cluster}` |
| `04-fasilitas` | Fasilitas | `/fasilitas` |
| `05-artikel` | Artikel | `/artikel` |
| `06-detail-artikel` | Detail Artikel | `/artikel/{slug}` |

## Cara pakai

- Buka file HTML di browser untuk melihat layout. Link antar halaman sudah tersambung.
- **HTML ini hanya referensi visual, bukan kode produksi.** Jangan disalin mentah-mentah. Implementasikan ulang sebagai komponen React + Tailwind sesuai brief (token warna, font, radius di bagian 6).
- Semua teks di HTML adalah **isi awal (seeder)** untuk settings/CMS, bukan teks hardcode.
- Kotak bergaris dengan label huruf kapital = placeholder gambar/video/peta.
- Teks dalam kurung siku `[...]` = data yang harus diganti dengan data asli.
- Toggle Cluster/Kawasan di Produk Listing adalah dua link ke dua URL, bukan tab JavaScript.
- Kartu cluster di `02a`, `02b`, `02c`, dan "Rumah lain" di `03` adalah **satu komponen yang sama**. Home juga harus memakai komponen ini (desain Home masih menampilkan kartu lama per tipe, abaikan).
- Di Detail Rumah, tab tipe menampilkan tipe "Deneb" terpilih. Jumlah tab mengikuti jumlah tipe di cluster.
- Di mobile Detail Rumah, bar bawah (harga + WA + Jadwalkan Survey) adalah **sticky** di bagian bawah layar.

## Catatan konten

- Proyek hanya **satu lokasi** (Serpong) yang terdiri dari beberapa kawasan. Label lama "Arunika Serpong/Cibubur/Karawang" yang masih tersisa di kartu Home harus diganti ke nama kawasan yang benar.
- Halaman Tentang Kami, Kontak, Terima Kasih, dan Kebijakan Privasi belum ada desainnya. Ikuti gaya visual halaman lain, dan struktur section-nya ada di brief bagian 7A.
