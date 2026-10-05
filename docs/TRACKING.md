# Tracking (GA4 organik, WhatsApp only)

Keputusan pemilik 5 Okt 2026:

- Situs tidak beriklan di Meta dan fokus ke trafik organik.
- Tidak ada form lead. Semua ajakan menghubungi diarahkan ke WhatsApp.
- Tracking hanya **Google Analytics 4**, dipasang langsung lewat `gtag.js` (tanpa GTM).
- Meta Pixel, Meta Conversions API, GTM, dan Cloudflare Turnstile sudah dihapus beserta field admin dan domain CSP-nya.

**Konversi utama: event `click_whatsapp`.** Tandai event ini sebagai **Key event** di GA4.

Kode: `resources/js/lib/analytics.ts` (event) dan `resources/views/partials/tracking-head.blade.php` (loader gtag.js + meta verifikasi).

## Pengaturan di admin

Semua pengaturan ada di **Admin → Pengaturan → Pengaturan Umum**.

| Field | Isi | Catatan |
| --- | --- | --- |
| GA4 Measurement ID | `G-XXXXXXXXXX` | Kosong = tidak ada script tracking yang dimuat sama sekali. |
| Verifikasi Google Search Console | Isi `content="…"` dari meta tag (seluruh meta tag juga boleh ditempel) | Dipasang sebagai `<meta name="google-site-verification">` di `<head>` semua halaman. |
| Nomor WhatsApp + template pesan | Lihat bagian WhatsApp di bawah | — |

`gtag.js` baru dimuat setelah halaman selesai dimuat dan browser idle (tidak menghambat LCP). Antrean `gtag()` sudah ada sejak awal, jadi event tidak hilang.

## Event GA4

| Event | Kapan | Parameter |
| --- | --- | --- |
| `page_view` | Setiap halaman dibuka, termasuk navigasi Inertia tanpa reload (`send_page_view: false` di config, dikirim manual) | `page_location`, `page_path`, `page_title` |
| **`click_whatsapp`** | Klik link `wa.me` di mana pun | `cluster`, `posisi_tombol`, `halaman` (dikirim dengan `transport_type: 'beacon'`) |
| `click_phone` | Klik link `tel:` | `posisi_tombol`, `halaman` |
| `download_brochure` / `download_pricelist` | Klik unduh brosur / pricelist | `cluster`, `halaman`, `file_url` |
| `view_listing` | Detail Rumah dibuka | `cluster`, `kawasan` |
| `select_house_type` | Ganti tab tipe rumah | `cluster`, `house_type` |

**`click_whatsapp`** memakai `transport_type: 'beacon'` (`navigator.sendBeacon`). Dengan begitu event tetap terkirim walaupun halaman langsung pindah ke WhatsApp.

### Nilai `posisi_tombol`

| Nilai | Tombol |
| --- | --- |
| `floating` | Tombol WhatsApp melayang (semua halaman; mobile ikon saja, desktop ikon + "Chat via WhatsApp") |
| `header` | Tombol CTA di header desktop |
| `sidebar` / `inline` | Kartu marketing Detail Rumah (desktop / mobile) |
| `sidebar_survey` / `inline_survey` / `sticky_survey` | Tombol "Jadwalkan Survey" di Detail Rumah |
| `promo_section` | Tombol section Promo & Benefit |
| `cta` / `cta_survey` | Section CTA di bawah halaman |
| `menu_kontak` | Menu "Kontak" di header, menu mobile, dan kolom footer (halaman Kontak sudah dihapus) |
| `footer` | Nomor WhatsApp di info kontak footer |
| `lainnya` | Link WhatsApp lain (mis. di teks artikel) |

Parameter `cluster` hanya terisi di tombol yang berkaitan dengan cluster tertentu. `halaman` = path halaman (mis. `/properti/vireya`).

## Setup di GA4 (sekali saja)

1. **Admin → Events:** setelah ada klik WhatsApp pertama, aktifkan **Mark as key event** untuk `click_whatsapp`.
2. **Admin → Custom definitions → Create custom dimension** (scope **Event**):
   - `cluster` → "Cluster"
   - `posisi_tombol` → "Posisi tombol"
   - `halaman` → "Halaman"
3. Laporan yang disarankan: **Explore → Free form**, dimensi Cluster dan Posisi tombol, metrik Key events (`click_whatsapp`).

## WhatsApp

- **Nomor:** nomor WA cluster (form Cluster → Marketing) kalau diisi. Kalau kosong, nomor global di Pengaturan Umum. Kalau nomor global juga kosong, tombol diarahkan ke info kontak di footer (`#info-kontak`).
- **Template pesan** (Pengaturan Umum, placeholder `{nama_cluster}`):

| Konteks | Bawaan |
| --- | --- |
| Detail cluster | Halo, saya tertarik dengan {nama_cluster}. Boleh minta info harga & brosurnya? |
| Section Promo & Benefit | Halo, saya tertarik dengan promo di {nama_cluster}. Boleh minta informasi lengkapnya? |
| Tombol jadwal survey | Halo, saya ingin jadwalkan survey ke {nama_cluster}. |
| Menu Kontak, tombol melayang, header / global | Halo, saya ingin konsultasi rumah di BSD City. |

Di luar Detail Rumah, `{nama_cluster}` diisi nama brand ("BSD City").

## Content-Security-Policy

CSP halaman publik memakai nonce + `strict-dynamic`. Domain bawaan (`App\Support\CspSources::DEFAULTS`):

- **GA4:** `www.googletagmanager.com` (gtag.js), `*.google-analytics.com`, `*.analytics.google.com`, `*.googletagmanager.com`.
- **Embed:** Google Maps (`www.google.com`, `maps.google.com`), YouTube.

Domain disk file publik (CDN/S3) ditambahkan otomatis.

## Cara menguji

1. Isi GA4 Measurement ID di Pengaturan Umum, lalu buka situs dengan **Tag Assistant** (tagassistant.google.com) atau GA4 **DebugView**.
2. Klik beberapa tombol WhatsApp: event `click_whatsapp` muncul dengan `cluster`, `posisi_tombol`, dan `halaman` yang benar.
3. Di DevTools → Network, filter `collect`: request event WhatsApp bertipe **ping/beacon**.
4. Cek sumber halaman: ada `<meta name="google-site-verification">`, dan tidak ada `gtm.js`, `fbevents.js`, maupun `challenges.cloudflare.com`.
