# Tracking (GTM-first)

Semua event website di-push ke `window.dataLayer`. Google Tag Manager (GTM) yang meneruskannya ke GA4, Meta Pixel, Google Ads, dan tool lain. Dengan cara ini, tag bisa ditambah atau diubah di GTM tanpa mengubah kode.

- Kode: `resources/js/lib/analytics.ts` (event), `resources/views/partials/tracking-head.blade.php` (loader).
- Pengaturan: **Admin → Pengaturan Global → Tracking & verifikasi** (hanya Super Admin).

## Pengaturan di admin

| Kolom | Isi | Catatan |
|---|---|---|
| Google Tag Manager ID | `GTM-XXXXXXX` | **Disarankan.** Semua tag diatur di GTM. |
| GA4 Measurement ID | `G-XXXXXXXXXX` | Opsional. Memasang GA4 langsung (gtag). **Kosongkan jika GA4 sudah dipasang lewat GTM**, supaya event tidak terhitung dua kali. |
| Meta Pixel ID | angka | Opsional. Memasang Pixel langsung (fbq). **Kosongkan jika Pixel sudah dipasang lewat GTM**, supaya event tidak terhitung dua kali. |
| Pixel ID untuk Conversions API | angka | Pixel tujuan event server (CAPI). Kosong = pakai Meta Pixel ID di atas. Isi ini kalau Pixel dipasang lewat GTM. |
| Access token Conversions API | token | Disimpan terenkripsi, tidak ditampilkan ulang. Kosong = CAPI dilewati tanpa error. |
| Test event code | `TEST12345` | Opsional, untuk uji coba di Events Manager → Test events. Kosongkan setelah selesai. |

Kalau GTM diisi bersama GA4/Pixel langsung, admin menampilkan peringatan "GTM juga terisi".

Script GTM/GA4/Pixel dimuat setelah halaman selesai dimuat dan browser idle. `dataLayer` dibuat paling awal, jadi event yang terjadi sebelum GTM termuat tetap antre dan tidak hilang.

## Daftar event dataLayer

Semua event berbentuk `dataLayer.push({ event: '<nama>', ...parameter })`. Parameter kosong tidak dikirim.

| Event | Kapan | Parameter | Meta Pixel (standar) |
|---|---|---|---|
| `generate_lead` | Halaman `/terima-kasih`, **sekali** tepat setelah form lead berhasil dikirim (refresh tidak menghitung ulang) | `event_id` (UUID, sama dengan CAPI), `cluster`, `house_type`, `form_position` (`sidebar` / `inline` / `modal` / `kontak` / `sticky`) | `Lead` + `eventID` |
| `click_whatsapp` | Klik link `wa.me` di mana pun | `page_path`, `link_url`, `link_position` (mis. `cta`, `sidebar`, `sticky`, `terima-kasih`), `cluster` | `Contact` |
| `click_phone` | Klik link `tel:` | `page_path`, `link_url` | `Contact` |
| `download_brochure` | Klik unduh brosur (Detail Rumah) | `page_path`, `cluster`, `file_url` | — |
| `download_pricelist` | Klik unduh pricelist (Detail Rumah) | `page_path`, `cluster`, `file_url` | — |
| `view_listing` | Membuka Detail Rumah | `cluster`, `kawasan` | `ViewContent` |
| `select_house_type` | Memilih tab tipe di Detail Rumah | `cluster`, `house_type` | — |
| `virtual_page_view` | Setiap pindah halaman **setelah** halaman pertama (navigasi Inertia tanpa reload) | `page_location`, `page_path`, `page_title` | `PageView` |

Halaman pertama sudah dihitung GTM lewat event bawaan `gtm.js` (trigger *All Pages / Initialization*). Karena itu `virtual_page_view` tidak dikirim untuk halaman pertama.

Contoh isi dataLayer setelah submit form di Detail Rumah:

```js
{
  event: 'generate_lead',
  event_id: '2f2f855c-3b38-43b6-8fed-5210100768aa',
  cluster: 'Vega Garden',
  house_type: 'Deneb 7×15',
  form_position: 'sidebar'
}
```

## Contoh setting GTM

### 1. Variabel (Variables → User-Defined → Data Layer Variable)

Buat satu variabel per parameter, versi Data Layer 2:

| Nama variabel | Data Layer Variable Name |
|---|---|
| `DLV - event_id` | `event_id` |
| `DLV - cluster` | `cluster` |
| `DLV - house_type` | `house_type` |
| `DLV - form_position` | `form_position` |
| `DLV - kawasan` | `kawasan` |
| `DLV - link_position` | `link_position` |
| `DLV - link_url` | `link_url` |
| `DLV - file_url` | `file_url` |
| `DLV - page_location` | `page_location` |
| `DLV - page_path` | `page_path` |
| `DLV - page_title` | `page_title` |

### 2. Trigger (Triggers → Custom Event)

| Nama trigger | Event name |
|---|---|
| `CE - generate_lead` | `generate_lead` |
| `CE - contact` | `click_whatsapp\|click_phone` (centang *Use regex matching*) |
| `CE - download` | `download_brochure\|download_pricelist` (regex) |
| `CE - view_listing` | `view_listing` |
| `CE - select_house_type` | `select_house_type` |
| `CE - virtual_page_view` | `virtual_page_view` |

### 3. Tag GA4

1. **Google Tag** (tag ID `G-XXXXXXXXXX`), trigger *Initialization – All Pages*. Ini sudah menghitung halaman pertama.
2. **GA4 Event – page_view (SPA)**: event name `page_view`, parameter `page_location` = `{{DLV - page_location}}`, `page_title` = `{{DLV - page_title}}`. Trigger `CE - virtual_page_view`.
3. **GA4 Event – generate_lead**: event name `generate_lead`, parameter `cluster`, `house_type`, `form_position`, `event_id`. Trigger `CE - generate_lead`. Tandai `generate_lead` sebagai *Key event* di GA4.
4. **GA4 Event – event lain**: event name `{{Event}}` (variabel bawaan). Trigger `CE - contact`, `CE - download`, `CE - view_listing`, `CE - select_house_type`. Parameter sesuai tabel di atas.

Kolom **GA4 Measurement ID di admin dikosongkan.**

### 4. Tag Meta Pixel

Pakai tag **Custom HTML** (atau template komunitas "Facebook Pixel" dengan pengaturan yang sama).

**Pixel – Base + PageView.** Trigger *All Pages* dan `CE - virtual_page_view`. Aktifkan *Tag firing options: Once per event*.

```html
<script>
  !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
  n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
  n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
  t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
  document,'script','https://connect.facebook.net/en_US/fbevents.js');
  if (!window.__pixelInit) { fbq('init', 'PIXEL_ID_ANDA'); window.__pixelInit = true; }
  fbq('track', 'PageView');
</script>
```

**Pixel – Lead (dengan deduplikasi).** Trigger `CE - generate_lead`. Atur *Tag Sequencing* supaya "Pixel – Base" jalan lebih dulu.

```html
<script>
  fbq('track', 'Lead', {
    content_name: {{DLV - cluster}},
    content_category: {{DLV - form_position}}
  }, { eventID: {{DLV - event_id}} });
</script>
```

**Pixel – Contact.** Trigger `CE - contact`.

```html
<script>
  fbq('track', 'Contact', { content_name: {{DLV - cluster}} });
</script>
```

**Pixel – ViewContent.** Trigger `CE - view_listing`.

```html
<script>
  fbq('track', 'ViewContent', {
    content_name: {{DLV - cluster}},
    content_category: {{DLV - kawasan}},
    content_type: 'product'
  });
</script>
```

Kolom **Meta Pixel ID di admin dikosongkan.** Kolom **Pixel ID untuk Conversions API** diisi dengan Pixel yang sama.

### 5. Google Ads (opsional)

Tag *Google Ads Conversion Tracking* dengan trigger `CE - generate_lead`. `gclid` sudah tersimpan di setiap lead (kolom `gclid`) untuk offline conversion import.

Tambahkan juga domain Google Ads di **Domain tambahan CSP** (lihat "Menambah tag baru di GTM" di bawah).

## Deduplikasi Pixel + Conversions API

Setiap lead mendapat `event_id` (UUID) saat disimpan di server. Nilai yang sama dipakai di tiga tempat:

1. **Server → Meta Conversions API.** Job `SendMetaLeadEvent` mengirim event `Lead` dengan `event_id` itu.
2. **Browser → dataLayer.** `/terima-kasih` mengirim `generate_lead` dengan parameter `event_id` yang sama.
3. **GTM → Pixel.** Tag "Pixel – Lead" mengirim `fbq('track', 'Lead', {...}, { eventID: {{DLV - event_id}} })`.

Meta menggabungkan event browser dan server yang punya **nama event sama (`Lead`) dan `event_id`/`eventID` sama** dalam 48 jam, sehingga konversi hanya terhitung satu kali. Syaratnya:

- Nama event di Pixel harus persis `Lead`.
- Parameter di Pixel harus `eventID` (huruf besar ID), di opsi ke-4 `fbq`, bukan di parameter ke-3.
- Pixel ID di GTM harus sama dengan "Pixel ID untuk Conversions API" di admin.

`event_id` juga terlihat di admin: **Lead → detail → Sumber → Event ID (Pixel/CAPI)**.

Data pribadi yang dikirim CAPI (nomor WA, email, nama) dinormalisasi lalu di-hash SHA-256. `fbp` (cookie `_fbp`), `fbc` (cookie `_fbc`, atau dibentuk dari `fbclid`), IP, dan user agent ikut dikirim untuk pencocokan.

## Content-Security-Policy

Halaman publik memakai CSP (Content-Security-Policy) yang membatasi dari mana browser boleh memuat script, mengirim data, menampilkan gambar, dan membuka iframe:

- **Script:** nonce + `'strict-dynamic'`. GTM dimuat oleh script bertanda nonce, jadi script yang disisipkan GTM (termasuk Custom HTML Pixel di atas) ikut dipercaya oleh browser modern. Pengecualiannya: tag yang memakai **`document.write`** (opsi "Support document.write" di GTM) diblok. Jangan aktifkan opsi itu.
- **Kirim data (`connect-src`), gambar/piksel (`img-src`), iframe (`frame-src`):** hanya domain yang ada di daftar. Bawaan sudah mencakup GTM, GA4, Meta Pixel, Cloudflare Turnstile, Google Maps, dan YouTube (daftar lengkap: `App\Support\CspSources::DEFAULTS`). Domain embed peta di halaman Kontak dan domain CDN/bucket foto ditambahkan otomatis.

Domain lain ditambahkan lewat **Admin → Pengaturan Global → Tracking & verifikasi → Domain tambahan CSP** (Super Admin saja).

## Menambah tag baru di GTM

Setiap tag pihak ketiga baru (TikTok Pixel, Google Ads, Hotjar, LinkedIn Insight, dll.) mengirim data atau memuat gambar/iframe dari domainnya sendiri. Domain itu **harus ditambahkan** di field **Domain tambahan CSP**; kalau tidak, tag terlihat "jalan" di GTM Preview tetapi datanya diblok browser dan tidak pernah sampai ke platform iklan.

Langkah:

1. Buat dan uji tag di GTM seperti biasa (Preview), lalu buka website dari mode Preview.
2. Buka **Console** browser (Chrome: klik kanan → Inspect → tab Console, atau `F12`) dan picu event-nya (buka halaman, kirim form, dsb.).
3. Cari pesan merah yang memuat **Content Security Policy**, misalnya:

   ```text
   Refused to connect to 'https://analytics.tiktok.com/api/v2/pixel' because it violates the following
   Content Security Policy directive: "connect-src 'self' https://www.googletagmanager.com …".

   Refused to load the image 'https://analytics.tiktok.com/i.gif' because it violates the following
   Content Security Policy directive: "img-src 'self' data: blob: …".
   ```

   Kata setelah *directive:* (`connect-src`, `img-src`, `frame-src`, `script-src`) = kolom yang harus diisi; domain di awal pesan = yang harus ditambahkan.
4. Isi domainnya di kolom yang sesuai (cukup nama domain, mis. `analytics.tiktok.com`; tekan Enter setelah tiap domain), lalu **Simpan**. CSP berlaku di request berikutnya.
5. Muat ulang halaman dan pastikan tidak ada lagi pesan CSP. Cek juga di tab **Network** bahwa request ke platform tersebut berstatus 200/204.

Aturan isian (divalidasi saat disimpan): hanya nama domain atau `https://domain[:port]`. **Tidak boleh** wildcard `*`, kata kunci seperti `'unsafe-eval'`/`'unsafe-inline'`, `http://`, atau path. Kalau sebuah layanan memakai banyak subdomain, tambahkan satu per satu sesuai yang muncul di Console.

### Contoh: TikTok Pixel

| Kolom | Domain |
|---|---|
| script-src | `analytics.tiktok.com` |
| connect-src | `analytics.tiktok.com`, `analytics-ipv6.tiktok.com` |
| img-src | `analytics.tiktok.com` |

### Contoh: Google Ads (konversi & remarketing)

| Kolom | Domain |
|---|---|
| script-src | `www.googleadservices.com`, `googleads.g.doubleclick.net` |
| connect-src | `www.googleadservices.com`, `googleads.g.doubleclick.net` |
| img-src | `www.googleadservices.com`, `googleads.g.doubleclick.net`, `www.google.co.id` |
| frame-src | `bid.g.doubleclick.net` |

(`td.doubleclick.net`, `www.google.com`, dan `*.g.doubleclick.net` untuk connect/img sudah termasuk bawaan.)

### Catatan layanan lain

- **Hotjar / Microsoft Clarity** memakai koneksi WebSocket (`wss://`) dan banyak subdomain; field ini hanya menerima `https://` tanpa wildcard, jadi pemasangannya perlu penyesuaian kode oleh developer.
- Nama domain dari penyedia bisa berubah. Selalu cocokkan dengan pesan di Console, bukan hanya dengan tabel di atas.
- Darurat (tag penting error dan belum sempat ditangani): `CSP_ENABLED=false` di `.env` mematikan CSP sementara. Aktifkan lagi setelah domain ditambahkan.

## Cara menguji

1. **GTM Preview (Tag Assistant).** Buka situs lewat Preview, isi form di Detail Rumah, lalu cek event `generate_lead` di halaman terima kasih beserta `event_id`-nya.
2. **Meta Events Manager → Test events.** Isi "Test event code" di admin, lalu kirim form. Harus muncul satu event `Lead` dari **Browser** dan satu dari **Server** dengan status *Deduplicated*. Kosongkan kembali test code setelah selesai.
3. **GA4 DebugView.** Aktifkan mode debug di GTM Preview, lalu cek `generate_lead` dan `page_view` saat berpindah halaman.
