# Brief Proyek: Website Developer Perumahan (Arunika Land — nama sementara)

> Brief ini untuk Claude Code. Baca sampai habis sebelum mulai. Kerjakan per milestone (bagian 11), commit di akhir tiap milestone, dan **tanya dulu** kalau ada keputusan yang tidak tercakup di sini. Jangan mengarang data bisnis (harga, alamat, angka pencapaian) — pakai seeder dummy yang jelas ditandai.

---

## 1. Konteks

- Website resmi developer perumahan dengan **satu kawasan/lokasi** (tidak ada filter wilayah).
- Tujuan utama: **lead generation** (form + WhatsApp) dan membangun kepercayaan terhadap developer. Trafik utama dari Meta Ads, Google Ads, dan organik (SEO).
- Mayoritas pengunjung mobile → desain dan performa **mobile-first**.
- Bahasa konten: Bahasa Indonesia (`lang="id"`, locale `id_ID`, zona waktu `Asia/Jakarta`, format Rupiah `Rp 1,6 M` / `Rp 9,8 jt/bln`).
- Referensi desain ada di `docs/design/` (6 halaman × desktop 1440 + mobile 390, dalam bentuk HTML statis + screenshot PNG). **Baca `docs/design/README.md` dan buka screenshot tiap halaman sebelum membuat komponen.** Ikuti layout, urutan section, dan token visual di bagian 6. HTML itu hanya referensi visual, jangan disalin mentah-mentah ke kode produksi.

## 2. Stack

| Lapisan | Pilihan |
|---|---|
| Backend | **Laravel 13** (PHP 8.3+) |
| Admin/CMS | **Filament 5** di `/admin` |
| Frontend publik | **React + TypeScript** via **Inertia.js v3** (`@inertiajs/react`) |
| Rendering | **Inertia SSR** (Node SSR server, `php artisan inertia:start-ssr`) — semua halaman publik wajib ter-render di server |
| Database | **MySQL 8** |
| Build | Vite, Tailwind CSS v4 (token dari bagian 6) |
| Queue/Cache | Redis (fallback `database` untuk dev) |
| Media | `spatie/laravel-medialibrary` + konversi WebP/AVIF |
| SEO utilitas | `spatie/laravel-sitemap`, `spatie/schema-org` |
| Testing | Pest |

Pakai versi stabil terbaru saat instalasi; kalau ada konflik versi antara Laravel 13 dan Filament 5, laporkan dulu sebelum downgrade.

Mulai dari **Laravel React starter kit** (Inertia + React + TS), lalu tambahkan Filament. Buang auth publik (register/login pengunjung) — hanya admin yang login lewat Filament.

## 3. Peta Halaman & URL

URL pakai slug Bahasa Indonesia, huruf kecil, tanpa trailing slash (redirect 301 kalau ada trailing slash).

| Halaman | URL | Catatan |
|---|---|---|
| Home | `/` | |
| Produk Listing | `/properti` | Filter via query string (`?tipe=&kamar=&harga=&status=&urut=`) |
| Detail Rumah | `/properti/{slug-cluster}` | Satu halaman per cluster; tab tipe rumah di dalamnya (`?tipe=vega` opsional, canonical tetap tanpa query) |
| Fasilitas | `/fasilitas` | Filter kategori via query string |
| Artikel (index) | `/artikel` | Highlight + filter kategori |
| Kategori artikel | `/artikel/kategori/{slug}` | Halaman terindeks sendiri (bukan query string) |
| Detail Artikel | `/artikel/{slug}` | |
| Tentang | `/tentang-kami` | Versi lengkap section About (link "Profil lengkap") |
| Kontak | `/kontak` | Form + peta + jam buka |
| Terima kasih | `/terima-kasih` | Setelah submit form; `noindex` |
| Kebijakan privasi | `/kebijakan-privasi` | Wajib karena mengumpulkan data pribadi (UU PDP) |
| Sitemap / robots | `/sitemap.xml`, `/robots.txt` | Dinamis |
| RSS artikel | `/artikel/feed.xml` | |

## 4. Struktur Section per Halaman (dari desain)

**Home:** Hero (media aerial + headline + CTA "Lihat Semua Listing" & WA) → About Developer (teks, 4 statistik, kutipan visi) → Promotional Banner (slider, dikelola admin) → Listing Produk (4 unggulan + tombol "Lihat Semua Listing") → Keunggulan Wilayah (peta + 4 poin akses) → Fasilitas Developer (4 unggulan + link) → Pengembangan Mendatang (timeline tahun + status) → Artikel Highlight (1 utama + 3) → CTA → Footer.

**Produk Listing:** Header kawasan (foto, nama, deskripsi, luas, jumlah cluster, harga mulai) → bar filter + urutkan + jumlah hasil → grid kartu rumah → pagination (desktop) / "Muat lagi" (mobile, tetap ada link pagination asli untuk crawler) → CTA → Footer.

**Detail Rumah:** Galeri (foto/video/virtual tour 360°/denah, lightbox) → Nama cluster/tipe + badge + alamat → Harga, cicilan, booking fee + link simulasi KPR → Promo rumah → Spesifikasi (6 key facts + tabel material) → Tab tipe-tipe rumah (denah, LT/LB, kamar, harga, sisa unit) → Deskripsi + unduh brosur/pricelist → Kartu marketing + form lead (sidebar desktop, inline mobile) → Listing lainnya → CTA → Footer. **Mobile: sticky bottom bar** (harga mulai + tombol WA + "Jadwalkan Survey").

**Fasilitas:** Header (judul, deskripsi, 3 statistik, foto) → chip kategori → daftar fasilitas (foto, kategori, nama, deskripsi).

**Artikel:** Header + artikel highlight → pencarian + chip kategori → grid kartu artikel → pagination → newsletter → Footer.

**Detail Artikel:** Breadcrumb → kategori, H1, penulis, tanggal, waktu baca → gambar utama → body rich text (H2, kutipan, gambar + caption, list) → tag + tombol bagikan → artikel terkait → CTA → Footer.

**Global:** Header (logo, menu, hotline, CTA WA) — mobile: logo + ikon WA + hamburger drawer. Footer (profil singkat, link properti, perusahaan, sosial, alamat kantor pemasaran, disclaimer harga/gambar ilustrasi).

## 5. Model Data (MySQL)

Semua tabel konten punya `is_published`, `published_at`, `sort_order` bila relevan, `timestamps`, dan soft delete untuk konten utama.

- **Pengaturan global & per halaman** disimpan lewat `spatie/laravel-settings` (tabel `settings`), **satu settings class/group per halaman**. Detail isinya di bagian 7A.
- `developer_profile` (dipakai di Home dan Tentang Kami): headline, deskripsi, sejarah, kutipan visi, statistik (repeater: nilai + label), foto.
- `area` (kawasan tunggal): nama, lokasi, deskripsi, luas (ha), media hero, peta embed/koordinat, poin keunggulan (repeater: ikon, judul, deskripsi, jarak/waktu).
- `promos`: judul, deskripsi, label, periode (`starts_at`, `ends_at`), gambar desktop & mobile, CTA (teks + URL), `placement` (home_banner / listing_badge / detail), relasi many-to-many ke `clusters`. Promo kedaluwarsa otomatis tidak tampil.
- `clusters` (= listing/Detail Rumah): nama, slug, ringkasan, deskripsi (rich text), alamat, badge (Terlaris/Baru/Promo/Segera), status (ready stock / inden / sold out), harga mulai, cicilan mulai, booking fee, catatan harga, galeri (media collection: photos, videos, tour_360_url, floorplans), file brosur & pricelist (PDF), spesifikasi material (repeater key-value), legalitas, unggulan (`is_featured`).
- `house_types`: `cluster_id`, nama, slug, ukuran kavling, LT, LB, kamar tidur, kamar mandi, lantai, carport, harga mulai, cicilan mulai, sisa unit, denah (media).
- `facilities`: nama, slug, kategori (`facility_categories`), ikon, deskripsi, foto, `is_featured` (tampil di home).
- `future_developments`: tahun/target, judul, deskripsi, status (beroperasi / konstruksi / perencanaan), gambar opsional.
- `articles`: judul, slug, excerpt, body (rich text dari Filament RichEditor, simpan HTML aman), cover + alt, `category_id`, tags (many-to-many), `author_id`, `is_highlight`, waktu baca (dihitung otomatis), `published_at`, `updated_at` tampil sebagai "Diperbarui".
- `article_categories`, `tags`, `authors` (nama, foto, bio singkat).
- `leads`: nama, WhatsApp (normalisasi ke 62…), email opsional, cluster/tipe minat, rencana bayar, sumber form (halaman + posisi form), `utm_source/medium/campaign/content/term`, `fbclid`, `gclid`, landing page pertama, referrer, IP (hash), user agent, status follow-up (baru/dihubungi/survey/closing/batal), catatan, `assigned_to`.
- `newsletter_subscribers`: email, status, sumber.
- `seo_meta` (polymorphic `seoable`): meta title, meta description, canonical override, OG image, `noindex` flag.
- `redirects`: from_path, to_path, kode (301/302/410), hit counter.

## 6. Desain & Token

- Warna: ground `#F4F1EA`, sand `#EAE4D7`, ink `#1E2B24`, hijau gelap `#23392E` / `#2C4538`, teks sekunder `#4A5650`, caption `#55605A`, garis `#DDD6C8`, aksen terakota `#A94F2A` (hover `#7E3A1E`), peach di latar gelap `#E9A07F`.
- Font: **Fraunces** (display/judul, 500–600) + **Plus Jakarta Sans** (body, 400–700). **Self-host** file woff2 (subset latin), `font-display: swap`, preload 2 file terpenting — jangan load dari Google Fonts saat runtime.
- Radius: kartu 20–24px, section besar 32px, tombol pill 999px. Touch target minimal 44px.
- Breakpoint: mobile < 768, tablet 768–1279, desktop ≥ 1280 (container 1280 + padding 80px; mobile padding 20px).
- Ikon: SVG stroke inline (lucide-react boleh), tanpa emoji.
- Kontras teks minimal 4.5:1. Semua tombol pakai `<button>`/`<a>` asli.
- Carousel (promo, listing mobile, fasilitas mobile): CSS scroll-snap, tanpa library berat; konten tetap ada di HTML SSR.

## 7. Filament Admin

- Panel `/admin`, login email+password, role `super_admin`, `admin_konten`, `marketing` (pakai `bezhansalleh/filament-shield` atau policy manual).
- Navigasi admin dikelompokkan:
  - **Konten:** Cluster (dengan relation manager House Types + galeri), Promo, Fasilitas + Kategori, Pengembangan Mendatang, Profil Developer & Kawasan.
  - **Artikel:** Artikel, Kategori, Tag, Penulis.
  - **Pengaturan Halaman:** satu menu per halaman (lihat 7A).
  - **Marketing:** Lead (baca + ubah status, filter tanggal/UTM/cluster, ekspor CSV/XLSX), Newsletter.
  - **Sistem:** Pengaturan Global, Menu Navigasi, Redirect, User & Role.
- Tiap resource konten punya tab **SEO** (meta title dengan counter 60 karakter, description dengan counter 160, OG image, noindex, preview snippet Google).
- Wajib isi **alt text** saat upload gambar (validasi).
- Slug otomatis dari judul, bisa diedit; saat slug berubah, otomatis buat redirect 301 dari slug lama.
- Dashboard: widget lead hari ini/minggu ini, lead per cluster, lead per sumber (UTM), artikel terbaru.
- Tombol "Preview" untuk artikel draft (URL bertanda tangan, `noindex`).

## 7A. Pengaturan per Halaman (full CMS)

**Aturan utama: tidak ada teks, gambar, atau link yang di-hardcode di komponen React.** Semua teks yang tampil (eyebrow, judul, deskripsi, label tombol, link CTA, gambar section) datang dari CMS. Kalau field kosong, pakai fallback dari seeder, jangan tampilkan string kosong.

Implementasi:
- Satu settings class per halaman (`GlobalSettings`, `HomePageSettings`, `ListingPageSettings`, `ClusterDetailPageSettings`, `FacilityPageSettings`, `ArticleIndexPageSettings`, `ArticleDetailPageSettings`, `AboutPageSettings`, `ContactPageSettings`, `ThankYouPageSettings`, `PrivacyPageSettings`), masing-masing di group terpisah.
- Satu **Filament Settings Page per halaman** di grup navigasi "Pengaturan Halaman". Di dalamnya pakai **Tabs, satu tab per section** sesuai urutan di desain, plus tab **SEO** di paling akhir.
- Tiap section punya toggle **Tampilkan section** (section yang dimatikan tidak dirender dan tidak masuk JSON-LD terkait).
- Field section standar (dipakai ulang lewat komponen form reusable): eyebrow, judul, deskripsi, label + URL tombol utama, label + URL tombol kedua, gambar desktop, gambar mobile (opsional), alt text.
- Section yang menampilkan data (listing, fasilitas, artikel, promo) punya pengaturan **sumber data**: mode `otomatis` (ambil yang `is_featured`/terbaru) atau `pilih manual` (select multiple + urutan drag), plus jumlah item.
- Tab SEO per halaman: meta title, meta description, OG image, `noindex`, canonical override, dan preview snippet.
- Cache settings per group; simpan settings → cache halaman terkait dan sitemap ikut di-invalidate.
- Tombol "Lihat halaman" di header tiap settings page.

Isi per halaman:

| Halaman admin | Tab / section | Yang bisa diatur |
|---|---|---|
| **Pengaturan Global** | Identitas | Nama brand, nama PT, logo (terang & gelap), favicon, tagline |
| | Kontak | Hotline, nomor WA (62…), template pesan WA default, email, alamat kantor pemasaran, koordinat, jam buka |
| | Header | Teks & link tombol CTA header, tampilkan hotline ya/tidak |
| | Footer | Deskripsi singkat, kolom link, link sosial, teks copyright, disclaimer |
| | CTA global | Eyebrow, judul, deskripsi, tombol WA & tombol kunjungan (dipakai di semua halaman, bisa di-override per halaman) |
| | Mobile | Teks sticky bar Detail Rumah, tampilkan ikon WA di header |
| | Tracking & verifikasi | GTM/GA4, Meta Pixel, kode verifikasi Search Console & Bing, Turnstile key |
| | Label umum | Teks tombol kecil (Muat lagi, Lihat denah, Baca artikel, Bagikan, dll.) |
| | SEO default | Pola title, OG image default, `sameAs` untuk Organization |
| **Menu Navigasi** | Header & drawer mobile | Item menu (label, URL/halaman, urutan, buka tab baru) |
| **Beranda** | Hero | Eyebrow, headline, sub-headline, media (gambar/video + poster), 2 tombol |
| | Tentang Developer | Judul, isi (ambil dari Profil Developer atau override), statistik, foto, link "Profil lengkap" |
| | Banner Promo | Mode otomatis (promo aktif placement home) / pilih manual, autoplay on/off |
| | Listing Produk | Judul, sumber data, jumlah (default 4), label tombol "Lihat Semua Listing" |
| | Keunggulan Wilayah | Judul, deskripsi, peta, badge jarak, poin keunggulan (ambil dari Kawasan atau override) |
| | Fasilitas | Judul, sumber data, jumlah, label link |
| | Pengembangan Mendatang | Judul, deskripsi, catatan disclaimer, sumber data |
| | Artikel Highlight | Judul, artikel utama (pilih), 3 artikel pendamping (otomatis/manual) |
| | CTA | Pakai CTA global atau override |
| | SEO | (standar) |
| **Properti (Listing)** | Header Kawasan | Judul, deskripsi, gambar, 3 statistik |
| | Filter & urutan | Filter yang ditampilkan (tipe, kamar, harga, status), rentang harga, urutan default, jumlah per halaman |
| | Empty state | Teks kalau hasil filter kosong |
| | CTA, SEO | (standar) |
| **Detail Rumah (template)** | Label & urutan section | Judul section (Promo, Spesifikasi, Tipe, Deskripsi, Listing Lainnya), toggle tiap section, catatan harga default, teks booking fee |
| | Form & marketing | Judul form, label tombol, marketing default (nama, foto, WA) jika cluster tidak punya marketing sendiri |
| | Listing Lainnya | Jumlah & mode (otomatis/manual) |
| | CTA, SEO default | Pola title/description untuk semua cluster; konten per cluster tetap diatur di resource Cluster |
| **Fasilitas** | Header | Eyebrow, judul, deskripsi, 3 statistik, 3 gambar |
| | Daftar | Kategori yang ditampilkan sebagai filter, urutan |
| | CTA, SEO | (standar) |
| **Artikel (index)** | Header | Eyebrow, judul, deskripsi, artikel highlight (pilih) |
| | Daftar | Jumlah per halaman, kategori yang tampil di filter, tampilkan pencarian |
| | Newsletter | Toggle, judul, deskripsi, label tombol |
| | SEO | (standar; halaman kategori memakai deskripsi kategori) |
| **Detail Artikel (template)** | Tampilan | Tampilkan penulis, waktu baca, tombol bagikan, tag |
| | Artikel terkait | Judul section, jumlah |
| | CTA, SEO default | Pola title/description artikel |
| **Tentang Kami** | Hero, Sejarah, Visi & Misi, Statistik, Timeline perusahaan, Tim/manajemen (opsional), Penghargaan (opsional), CTA, SEO | Semua teks, gambar, repeater item |
| **Kontak** | Header, info kontak (default dari Global), peta, form (label & opsi dropdown), CTA, SEO | |
| **Terima Kasih** | Judul, pesan, tombol lanjut WA, tautan ke listing/artikel | `noindex` terkunci |
| **Kebijakan Privasi** | Judul, isi rich text, tanggal berlaku | |

Konten yang berupa koleksi (cluster, tipe rumah, promo, fasilitas, pengembangan, artikel) tetap dikelola di resource masing-masing. Settings halaman hanya mengatur **teks section, tampil/tidak, dan item mana yang ditampilkan**.

## 8. Technical SEO (wajib semua)

### 8.1 Rendering & crawlability
- Semua halaman publik **SSR**: `curl` ke tiap URL harus mengembalikan H1, teks utama, link internal, dan JSON-LD di HTML awal (tanpa JS).
- Head dikelola dari server lewat komponen `<Head>` Inertia; pastikan tag head ikut di output SSR (bukan hanya client).
- Link internal pakai `<Link>`/`<a href>` asli (bukan `onClick` navigasi).
- Satu `<h1>` per halaman, hierarki heading berurutan.
- `lang="id"` di `<html>`.

### 8.2 Meta & canonical
- Title unik per halaman, pola default: `{Judul Halaman} | {Nama Brand}`; home: `{Nama Brand} — {tagline}`. Admin bisa override.
- Meta description unik; fallback otomatis dari excerpt/ringkasan (dipotong ±155 karakter di batas kata).
- `<link rel="canonical">` absolut di semua halaman. Halaman dengan query filter/urut/pencarian → canonical ke versi tanpa query **dan** `noindex,follow` bila ada parameter filter. Pagination `?page=2` → canonical ke dirinya sendiri (self-referencing), bukan ke halaman 1.
- Open Graph (`og:title`, `og:description`, `og:image` 1200×630, `og:url`, `og:type`, `og:locale=id_ID`, `og:site_name`) + Twitter Card `summary_large_image`. Buat OG image default per tipe halaman.
- `meta robots`: `noindex` untuk `/terima-kasih`, preview draft, hasil pencarian artikel, dan **seluruh environment non-production** (plus header `X-Robots-Tag: noindex` di staging).

### 8.3 Structured data (JSON-LD, pakai `spatie/schema-org`)
- Global: `Organization` (atau `RealEstateAgent`) dengan logo, `sameAs` sosial, `contactPoint`; `WebSite`.
- Kantor pemasaran: `LocalBusiness`/`RealEstateAgent` dengan alamat, `geo`, `openingHoursSpecification`, telepon.
- Semua halaman selain home: `BreadcrumbList` (sama dengan breadcrumb yang tampil).
- Produk Listing: `ItemList` berisi URL cluster.
- Detail Rumah: `Residence`/`SingleFamilyResidence` (atau `House`) dengan `floorSize`, `numberOfRooms`, `numberOfBathroomsTotal`, `address`, `image`, plus `Offer` (harga mulai, `priceCurrency: IDR`, `availability`). Catatan: tidak ada jaminan rich result, tapi markup tetap valid.
- Detail Artikel: `BlogPosting`/`NewsArticle` (headline, image, `datePublished`, `dateModified`, author `Person`, publisher).
- FAQ (kalau nanti ada): `FAQPage` boleh dipasang untuk pemahaman konten; jangan berharap tampil sebagai rich result.
- Validasi semua tipe di Rich Results Test / Schema Markup Validator; tambahkan test yang mem-parse JSON-LD tiap tipe halaman.

### 8.4 Sitemap, robots, feed
- `/sitemap.xml` sebagai sitemap index → `sitemap-pages.xml`, `sitemap-properti.xml`, `sitemap-artikel.xml` (termasuk kategori). Isi `lastmod` dari `updated_at`. Regenerasi otomatis via observer/queue saat konten berubah + scheduler harian.
- Image sitemap untuk galeri cluster dan cover artikel.
- `/robots.txt` dinamis: production mengizinkan crawl, blok `/admin`, `/livewire`, `/terima-kasih`, URL berparameter filter; cantumkan URL sitemap. Non-production: `Disallow: /`.
- RSS `/artikel/feed.xml` + `<link rel="alternate" type="application/rss+xml">`.
- Opsional: ping IndexNow saat artikel/cluster dipublikasikan.

### 8.5 URL & status code
- Slug bersih, lowercase, pakai tanda hubung. Redirect 301 untuk: trailing slash, `http→https`, `www` ↔ non-`www` (pilih satu), huruf kapital.
- Manager redirect dari tabel `redirects` (middleware, cache).
- Konten dihapus → 410 atau 301 ke pengganti; halaman tidak ada → 404 custom yang ter-render SSR (dengan link ke listing & artikel), status code benar (bukan soft-404).
- Konten `is_published = false` → 404 untuk publik.

### 8.6 Performa & Core Web Vitals
- Target lapangan: **LCP < 2,5 dtk, INP < 200 ms, CLS < 0,1** di mobile. Lighthouse mobile ≥ 90 untuk Performance, SEO, Best Practices, Accessibility.
- Gambar: konversi WebP + AVIF, `srcset`/`sizes` responsif, `width`/`height` eksplisit, `loading="lazy"` kecuali gambar LCP (hero/cover/galeri pertama) yang pakai `fetchpriority="high"` dan di-preload.
- Video hero: poster image dulu, video dimuat setelah interaksi/idle; jangan autoplay video berat di mobile.
- Peta: facade (gambar statis + tombol "Buka peta"), iframe Google Maps baru dimuat saat diklik.
- Code-splitting per halaman (dynamic import di resolver Inertia), hindari library carousel/animasi besar.
- Font self-host (lihat 6), preconnect hanya ke domain yang benar-benar dipakai.
- Script pihak ketiga (GTM, Pixel) dimuat `defer`/setelah interaksi atau lewat consent; ukur dampaknya.
- Cache: response cache untuk halaman publik (invalidasi saat konten diubah), cache query, header `Cache-Control` untuk aset (immutable + hash), Gzip/Brotli. Siap diletakkan di belakang CDN (Cloudflare).
- HTTPS + HSTS, HTTP/2 atau HTTP/3.

### 8.7 Konten & internal linking
- Breadcrumb tampil di semua halaman selain home.
- Detail Rumah → "Listing lainnya" (cluster lain); Detail Artikel → artikel terkait (kategori/tag sama) + CTA ke listing relevan.
- Alt text wajib; nama file gambar diubah jadi slug deskriptif saat upload.
- Tanggal "Diperbarui" tampil di artikel bila `updated_at` jauh dari `published_at`.
- Waktu baca dihitung otomatis.

### 8.8 Analytics & verifikasi
- Google Search Console (meta verifikasi dari settings) + Bing Webmaster.
- GTM atau GA4 langsung; Meta Pixel. ID diisi dari admin, tidak di-hardcode.
- Event: `generate_lead` (submit form, dengan parameter cluster & posisi form), `click_whatsapp`, `click_phone`, `download_brochure`, `download_pricelist`, `view_listing`, `select_house_type`. Meta Pixel: `Lead`, `Contact`, `ViewContent`.
- Opsional (tanya dulu): Meta Conversions API server-side dari event lead, dengan `event_id` deduplikasi.

## 9. Lead Handling

- Form ada di: Detail Rumah (sidebar/inline), CTA global (modal), halaman Kontak, newsletter (email saja).
- Validasi: nama wajib, nomor WA Indonesia valid (normalisasi `08…`/`+62…` → `62…`), minat cluster opsional.
- Anti-spam: honeypot + rate limit per IP + Cloudflare Turnstile (atau reCAPTCHA v3) — konfigurasi dari env.
- Simpan UTM, `fbclid`, `gclid`, landing page pertama, dan referrer (tangkap di kunjungan pertama, simpan di cookie/session 30 hari, kirim bersama form).
- Setelah submit: redirect ke `/terima-kasih` (event konversi terbaca di sana) + opsi tombol lanjut chat WA dengan pesan template berisi nama cluster.
- Notifikasi ke marketing: email (queue) + opsional webhook (untuk WA gateway / Google Sheet) — URL dari settings.
- Tombol WA global: `https://wa.me/{nomor}?text={template}`, template per halaman (menyebut nama cluster di Detail Rumah).
- Checkbox persetujuan + link kebijakan privasi (UU PDP).

## 10. Kualitas & Keamanan

- Pest test: section yang dimatikan di settings tidak dirender; perubahan teks di settings langsung tampil (cache ter-invalidate); route publik 200 + SSR berisi H1; halaman unpublished 404; redirect bekerja; sitemap berisi URL yang dipublikasikan saja; robots berbeda antara prod/non-prod; JSON-LD valid per tipe halaman; submit lead tersimpan dengan UTM.
- Sanitasi HTML rich text (hanya tag yang diizinkan), escape output.
- CSP dasar, security headers (X-Frame-Options, Referrer-Policy, Permissions-Policy).
- Backup database terjadwal (`spatie/laravel-backup`).
- Seeder dummy: 1 kawasan, 6–9 cluster, 3 tipe per cluster, 9 fasilitas, 4 future development, 9 artikel di 5 kategori, 2 promo. Tandai semua data dummy dengan kurung siku `[...]` di teks yang harus diganti.
- `README.md`: cara setup lokal, menjalankan SSR server, queue, scheduler, dan deploy (Supervisor untuk SSR + queue worker).

## 11. Milestone (kerjakan berurutan)

1. **Setup** — Laravel 13 + React starter kit (Inertia v3, TS) + SSR aktif + Filament 5 + MySQL + Tailwind token + font self-host. Layout global (header, footer, drawer mobile) ter-render SSR.
2. **Model & admin** — migrasi, model, relasi, seeder dummy (termasuk isi awal semua settings halaman), semua Filament resource + tab SEO, Pengaturan Global, Menu Navigasi, dan satu settings page per halaman (7A).
3. **Halaman publik** — Home, Produk Listing, Detail Rumah, Fasilitas, Artikel, Kategori, Detail Artikel, Tentang, Kontak, 404, sesuai desain desktop & mobile.
4. **Lead & tracking** — form, anti-spam, UTM capture, halaman terima kasih, notifikasi, event analytics/Pixel.
5. **Technical SEO** — meta/OG, canonical, JSON-LD, sitemap, robots, RSS, redirect manager, noindex rules.
6. **Performa** — optimasi gambar, facade peta/video, code-splitting, cache, audit Lighthouse mobile ≥ 90.
7. **QA** — Pest test hijau, cek SSR tiap halaman via curl, validasi structured data, checklist aksesibilitas, README deploy.

Di akhir tiap milestone: ringkas apa yang dikerjakan, apa yang belum, dan keputusan yang butuh konfirmasi.
