# Design Reference — Website Developer Perumahan

Referensi visual untuk implementasi. Taruh folder ini di `docs/design/` pada repo.

## Isi

| Folder | Isi |
|---|---|
| `desktop/` | 6 halaman versi desktop (lebar 1440px), HTML statis dengan inline style |
| `mobile/` | 6 halaman versi mobile (lebar 390px) |
| `screenshots/` | Screenshot full-page tiap halaman (PNG) |

Urutan file: `01-home`, `02-listing-properti`, `03-detail-rumah`, `04-fasilitas`, `05-artikel`, `06-detail-artikel`.

## Cara pakai

- Buka file HTML di browser untuk melihat layout. Link antar halaman sudah tersambung.
- **HTML ini hanya referensi visual, bukan kode produksi.** Jangan disalin mentah-mentah. Implementasikan ulang sebagai komponen React + Tailwind sesuai brief (token warna, font, radius di bagian 6).
- Semua teks di HTML adalah **isi awal (seeder)** untuk settings/CMS, bukan teks hardcode.
- Kotak bergaris dengan label huruf kapital = placeholder gambar/video/peta.
- Teks dalam kurung siku `[...]` = data yang harus diganti dengan data asli.
- Di halaman Detail Rumah, tab tipe rumah menampilkan state "Vega" terpilih; di Artikel, filter "Semua" aktif.
- Di mobile Detail Rumah, bar bawah (harga + WA + Jadwalkan Survey) adalah **sticky** di bagian bawah layar.
- Screenshot dirender tanpa font asli (fallback). Font yang benar: Fraunces (judul) + Plus Jakarta Sans (body).

## Catatan konten

- Proyek hanya **satu lokasi/kawasan**. Sisa teks yang menyebut beberapa wilayah (misalnya "tiga kota" di hero atau daftar tiga kawasan di footer desktop) harus diganti ke versi satu lokasi, mengikuti versi mobile.
- Halaman Tentang Kami, Kontak, Terima Kasih, dan Kebijakan Privasi belum ada desainnya. Ikuti gaya visual halaman lain, dan struktur section-nya ada di brief bagian 7A.
