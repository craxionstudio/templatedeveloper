# Brief Proyek: Website Developer Perumahan (Arunika Land — nama sementara)

> Brief ini untuk Claude Code. Baca sampai habis sebelum mulai. Kerjakan per milestone (bagian 11), commit di akhir tiap milestone, dan **tanya dulu** kalau ada keputusan yang tidak tercakup di sini. Jangan mengarang data bisnis (harga, alamat, angka pencapaian) — pakai seeder dummy yang jelas ditandai.
>
> **Revisi 2 (24 Sep 2026): model Kawasan.** Hierarki properti sekarang Kawasan (opsional) → Cluster → Tipe rumah. Ringkasan perubahan dan dampaknya ke kode yang sudah ada ada di `docs/CHANGES.md`. Kalau ada yang bertentangan antara ingatanmu tentang brief lama dan file ini, **file ini yang berlaku**.

---

## 1. Konteks

- Website resmi developer perumahan dengan **satu lokasi** (kota mandiri Arunika, Serpong). Tidak ada filter kota/wilayah.
- Di dalam lokasi itu ada beberapa **kawasan** (misalnya Arunika Garden, Arunika Hills, Arunika Lakeside). Tiap kawasan berisi beberapa **cluster**, dan tiap cluster berisi **1 sampai n tipe rumah**. Ada juga **cluster mandiri** yang tidak masuk kawasan mana pun (pola seperti BSD City). Jadi hierarkinya: Kawasan (opsional) → Cluster → Tipe rumah.
- Tujuan utama: **lead generation** (form + WhatsApp) dan membangun kepercayaan terhadap developer. Trafik utama dari Meta Ads, Google Ads, dan organik (SEO).
- Mayoritas pengunjung mobile → desain dan performa **mobile-first**.
- Bahasa konten: Bahasa Indonesia (`lang="id"`, locale `id_ID`, zona waktu `Asia/Jakarta`, format Rupiah `Rp 1,6 M` / `Rp 9,8 jt/bln`).
- Referensi desain ada di `docs/design/` (8 halaman × desktop 1440 + mobile 390, dalam bentuk HTML statis + screenshot PNG). **Baca `docs/design/README.md` dan buka screenshot tiap halaman sebelum membuat komponen.** Ikuti layout, urutan section, dan token visual di bagian 6. HTML itu hanya referensi visual, jangan disalin mentah-mentah ke kode produksi.

## 2. Stack

| Lapisan | Pilihan |
|---|---|
| Backend | **Laravel 13** (PHP 8.3+) |
| Admin/CMS | **Filament 5** di `/admin` |
| Frontend publik | **React + TypeScript** via **Inertia.js v3** (`@inertiajs/react`) |
| Rendering | **Inertia SSR** (Node SSR server, `php artisan inertia:start-ssr`) — semua halaman publik wajib ter-render di server |
| Database | **SQLite** untuk lokal/development (`database/database.sqlite`), **MySQL 8** untuk production. Semua migration wajib kompatibel keduanya. |
| Build | Vite, Tailwind CSS v4 (token dari bagian 6) |
| Queue/Cache | Redis (fallback `database` untuk dev) |
| Media | `spatie/laravel-medialibrary` + konversi WebP/AVIF |
| SEO utilitas | `spatie/laravel-sitemap`, `spatie/schema-org` |
| Testing | Pest |

Pakai versi stabil terbaru saat instalasi. Kalau Filament 5 bermasalah di Laravel 13, pakai Laravel 12 seperti repo `craxionstudio/rezabsd` (stack yang sama dan sudah terbukti jalan).

**Ikuti pola repo `craxionstudio/rezabsd`** untuk setup, struktur folder (`app/Filament`, `resources/js/Pages`, controller yang mengirim SEO/JSON-LD ke Inertia), placeholder SVG saat foto kosong, dan format README. Project ini repo terpisah; jangan ubah apa pun di rezabsd.

**Alur kerja:** kode dibangun dan dites di sesi ini, lalu di-push ke GitHub. Pemilik akan `git pull` dan menjalankannya di perangkat lain (sudah ada PHP, Composer, MySQL, Node). Jadi sebelum push, pastikan `composer install`, `php artisan migrate:fresh --seed`, `npm run build`, dan test lolos di sesi ini.

Mulai dari **Laravel React starter kit** (Inertia + React + TS), lalu tambahkan Filament. Buang auth publik (register/login pengunjung) — hanya admin yang login lewat Filament.

## 3. Peta Halaman & URL

URL pakai slug Bahasa Indonesia, huruf kecil, tanpa trailing slash (redirect 301 kalau ada trailing slash).

| Halaman | URL | Catatan |
|---|---|---|
| Home | `/` | |
| Produk Listing — tampilan Cluster | `/properti` | Default. Grid kartu cluster. Filter via query string (`?kawasan=&tipe=&kamar=&harga=&status=&urut=`); `kawasan=mandiri` untuk cluster tanpa kawasan |
| Produk Listing — tampilan Kawasan | `/properti/kawasan` | Grid kartu kawasan + section "Cluster yang berdiri sendiri". Halaman terindeks sendiri |
| Detail Kawasan | `/properti/kawasan/{slug-kawasan}` | Info kawasan + semua cluster di dalamnya |
| Detail Rumah | `/properti/{slug-cluster}` | Satu halaman per cluster; tab tipe rumah di dalamnya (`?tipe=deneb` opsional, canonical tetap tanpa query). URL cluster **tidak** memuat slug kawasan, supaya URL tetap stabil kalau cluster dipindah kawasan |

Catatan route: daftarkan route `/properti/kawasan` dan `/properti/kawasan/{slug}` **sebelum** `/properti/{slug-cluster}`, dan tolak `kawasan` sebagai slug cluster (validasi di Filament).
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

**Home:** Hero (media aerial + headline + CTA "Lihat Semua Listing" & WA) → About Developer (teks, 4 statistik, kutipan visi) → Promotional Banner (slider, dikelola admin) → Listing Produk (4 **kartu cluster** unggulan, pakai komponen kartu cluster yang sama dengan Produk Listing; desain Home masih menampilkan kartu lama per tipe, abaikan itu + tombol "Lihat Semua Listing") → Keunggulan Wilayah (peta + 4 poin akses) → Fasilitas Developer (4 unggulan + link) → Pengembangan Mendatang (timeline tahun + status) → Artikel Highlight (1 utama + 3) → CTA → Footer.

**Produk Listing — Cluster** (`02a-properti-cluster`): Header (foto aerial, lokasi, judul, deskripsi, 3 statistik: jumlah kawasan, jumlah cluster, harga mulai) → **toggle tampilan Cluster | Kawasan** (dua link biasa dengan jumlah, bukan state JS) → bar filter (kawasan, tipe properti, kamar, harga, status) + urutkan + jumlah hasil ("9 cluster · 20 tipe rumah") → grid **kartu cluster** → pagination (desktop) / "Muat lagi" (mobile, tetap ada link pagination asli untuk crawler) → CTA → Footer.

**Kartu cluster** (komponen reusable, dipakai di Listing, Detail Kawasan, Home, "Listing lainnya"): foto, badge (Terlaris/Baru/Promo/Segera), label "N tipe rumah", label kawasan (atau "Cluster mandiri"), nama cluster, jenis bangunan, chip tiap tipe ("Deneb · 7×15"), rentang LT, rentang kamar tidur, rentang harga, cicilan mulai. Rentang dihitung dari tipe-tipe di cluster itu, bukan diisi manual.

**Produk Listing — Kawasan** (`02b-properti-kawasan`): Header sama → toggle (Kawasan aktif) → ringkasan ("3 kawasan dengan total 7 cluster, ditambah 2 cluster mandiri") → grid **kartu kawasan** (foto, jumlah cluster, nama, deskripsi singkat, nama-nama cluster, harga mulai, link "Lihat kawasan") → section **Cluster yang berdiri sendiri** (kartu cluster mandiri) → CTA → Footer.

**Detail Kawasan** (`02c-detail-kawasan`): Breadcrumb (Beranda / Properti / Kawasan / {nama}) → hero (foto, eyebrow, nama, deskripsi, statistik: luas, jumlah cluster, harga mulai) → Tentang Kawasan (judul, deskripsi, tombol unduh brosur kawasan + lihat peta, daftar fasilitas kawasan dengan ikon) → "Cluster di {kawasan}" (jumlah cluster & tipe + grid kartu cluster) → Kawasan lainnya → CTA → Footer.

**Detail Rumah:** Breadcrumb (Beranda / Properti / {Kawasan, dilewati kalau cluster mandiri} / {Cluster}) → Galeri (foto/video/virtual tour 360°/denah, lightbox) → Label "Kawasan X · Cluster Y" + nama cluster/tipe + badge + alamat → Harga, cicilan, booking fee + link simulasi KPR → Promo rumah → Spesifikasi (6 key facts + tabel material) → Tab tipe-tipe rumah (jumlah tab mengikuti jumlah tipe di cluster, 1 sampai n; tiap tab: denah, LT/LB, kamar, harga, sisa unit; kalau hanya 1 tipe, tampilkan tanpa tab) → Deskripsi + unduh brosur/pricelist → Kartu marketing + form lead (sidebar desktop, inline mobile) → Listing lainnya → CTA → Footer. **Mobile: sticky bottom bar** (harga mulai + tombol WA + "Jadwalkan Survey").

**Fasilitas:** Header (judul, deskripsi, 3 statistik, foto) → chip kategori → daftar fasilitas (foto, kategori, nama, deskripsi).

**Artikel:** Header + artikel highlight → pencarian + chip kategori → grid kartu artikel → pagination → newsletter → Footer.

**Detail Artikel:** Breadcrumb → kategori, H1, penulis, tanggal, waktu baca → gambar utama → body rich text (H2, kutipan, gambar + caption, list) → tag + tombol bagikan → artikel terkait → CTA → Footer.

**Global:** Header (logo, menu, hotline, CTA WA) — mobile: logo + ikon WA + hamburger drawer. Footer (profil singkat, kolom Properti berisi link ke tiap kawasan (otomatis dari data kawasan yang dipublikasikan), perusahaan, sosial, alamat kantor pemasaran, disclaimer harga/gambar ilustrasi).

## 5. Model Data (MySQL)

Semua tabel konten punya `is_published`, `published_at`, `sort_order` bila relevan, `timestamps`, dan soft delete untuk konten utama.

- **Pengaturan global & per halaman** disimpan lewat `spatie/laravel-settings` (tabel `settings`), **satu settings class/group per halaman**. Detail isinya di bagian 7A.
- `developer_profile` (dipakai di Home dan Tentang Kami): headline, deskripsi, sejarah, kutipan visi, statistik (repeater: nilai + label), foto.
- `area` (profil lokasi/kota mandiri, satu baris saja): nama, lokasi, deskripsi, luas (ha), media hero, peta embed/koordinat, poin keunggulan (repeater: ikon, judul, deskripsi, jarak/waktu). Dipakai di Home (Keunggulan Wilayah) dan header Produk Listing.
- `kawasans` (**baru**): nama, slug, ringkasan (untuk kartu), deskripsi (rich text), luas (ha), foto hero + galeri (media), fasilitas kawasan (repeater: ikon, judul, keterangan), brosur kawasan (PDF), peta embed/koordinat, `is_featured`, `sort_order`, `is_published`, SEO (via `seo_meta`). Jumlah cluster dan harga mulai **dihitung** dari cluster yang dipublikasikan, bukan diisi manual.
- `promos`: judul, deskripsi, label, periode (`starts_at`, `ends_at`), gambar desktop & mobile, CTA (teks + URL), `placement` (home_banner / listing_badge / detail), relasi many-to-many ke `clusters`. Promo kedaluwarsa otomatis tidak tampil.
- `clusters` (= listing/Detail Rumah): **`kawasan_id` nullable** (FK ke `kawasans`, `nullOnDelete`; null = cluster mandiri), nama, slug, jenis bangunan (mis. "Rumah 2 lantai", "Townhouse 3 lantai"), tipe properti (rumah/townhouse/ruko/kavling, untuk filter), ringkasan, deskripsi (rich text), alamat, badge (Terlaris/Baru/Promo/Segera), status (ready stock / inden / sold out), harga mulai, cicilan mulai, booking fee, catatan harga, galeri (media collection: photos, videos, tour_360_url, floorplans), file brosur & pricelist (PDF), spesifikasi material (repeater key-value), legalitas, unggulan (`is_featured`).
- `house_types`: `cluster_id`, nama, slug (unik per cluster), ukuran kavling, LT, LB, kamar tidur, kamar tidur tambahan (untuk "4+1"), kamar mandi, lantai, carport, harga mulai, cicilan mulai, sisa unit, denah (media), `sort_order`, `is_published`. Satu cluster boleh punya 1 sampai n tipe. Rentang harga/LT/KT di kartu cluster dihitung dari tabel ini (boleh di-cache di kolom turunan pada `clusters`, diperbarui lewat observer).
- `facilities`: nama, slug, kategori (`facility_categories`), ikon, deskripsi, foto, `is_featured` (tampil di home).
- `future_developments`: tahun/target, judul, deskripsi, status (beroperasi / konstruksi / perencanaan), gambar opsional.
- `articles`: judul, slug, excerpt, body (rich text dari Filament RichEditor, simpan HTML aman), cover + alt, `category_id`, tags (many-to-many), `author_id`, `is_highlight`, waktu baca (dihitung otomatis), `published_at`, `updated_at` tampil sebagai "Diperbarui".
- `article_categories`, `tags`, `authors` (nama, foto, bio singkat).
- `leads`: nama, WhatsApp (normalisasi ke 62…), email opsional, cluster/tipe minat, rencana bayar, sumber form (halaman + posisi form), `utm_source/medium/campaign/content/term`, `fbclid`, `gclid`, landing page pertama, referrer, IP (hash), user agent, status follow-up (baru/dihubungi/survey/closing/batal), catatan, `assigned_to`.
- `newsletter_subscribers`: email, status, sumber.
- `seo_meta` (polymorphic `seoable`): meta title, meta description, canonical override, OG image, `noindex` flag.
- `redirects`: from_path, to_path, kode (301/302/410), hit counter.

## 6. Desain & Token

- Warna: ground `#F4F1EA`, sand `#EAE4D7`, ink `#1E2B24`, hijau gelap `#23392E` / `#2C4538`, teks sekunder `#4A5650`, caption `#55605A`, garis `#DDD6C8`, aksen terakota `#9A4524` (hover `#7E3A1E`, lebih gelap; lihat catatan kontras di bawah), peach di latar gelap `#E9A07F`.
- Font: **Fraunces** (display/judul, 500–600) + **Plus Jakarta Sans** (body, 400–700). **Self-host** file woff2 (subset latin), `font-display: swap`, preload 2 file terpenting — jangan load dari Google Fonts saat runtime.
- Radius: kartu 20–24px, section besar 32px, tombol pill 999px. Touch target minimal 44px.
- Breakpoint: mobile < 768, tablet 768–1279, desktop ≥ 1280 (container 1280 + padding 80px; mobile padding 20px).
- Ikon: SVG stroke inline (lucide-react boleh), tanpa emoji.
- Kontras teks minimal 4.5:1. Terakota di desain HTML masih `#A94F2A`; di kode dipakai `#9A4524` supaya teks terakota kecil di latar sand dan teks terang di CTA terakota lolos 4.5:1 (keputusan pemilik setelah Milestone 6, lihat `docs/CHANGES.md`). Semua tombol pakai `<button>`/`<a>` asli.
- Carousel (promo, listing mobile, fasilitas mobile): CSS scroll-snap, tanpa library berat; konten tetap ada di HTML SSR.

## 7. Filament Admin

- Panel `/admin`, login email+password, role `super_admin`, `admin_konten`, `marketing` (pakai `bezhansalleh/filament-shield` atau policy manual).
- Navigasi admin dikelompokkan:
  - **Properti:** Kawasan (dengan relation manager Cluster), Cluster (select Kawasan opsional dengan opsi kosong = "Cluster mandiri", filter tabel per kawasan, relation manager House Types dengan urutan drag + galeri), Profil Lokasi (`area`).
  - **Konten:** Promo, Fasilitas + Kategori, Pengembangan Mendatang, Profil Developer.
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
- Satu settings class per halaman (`GlobalSettings`, `HomePageSettings`, `ListingPageSettings` (dipakai kedua tampilan listing), `KawasanDetailPageSettings`, `ClusterDetailPageSettings`, `FacilityPageSettings`, `ArticleIndexPageSettings`, `ArticleDetailPageSettings`, `AboutPageSettings`, `ContactPageSettings`, `ThankYouPageSettings`, `PrivacyPageSettings`), masing-masing di group terpisah.
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
| **Properti (Listing)** | Header | Eyebrow lokasi, judul, deskripsi, gambar, 3 statistik (angka otomatis, label bisa diubah) |
| | Toggle tampilan | Label "Cluster" & "Kawasan", tampilan default |
| | Tampilan Cluster: filter & urutan | Filter yang ditampilkan (kawasan, tipe, kamar, harga, status), rentang harga, urutan default, jumlah per halaman |
| | Tampilan Kawasan | Teks ringkasan, label tombol "Lihat kawasan", judul + deskripsi section "Cluster yang berdiri sendiri" (toggle tampil) |
| | Empty state | Teks kalau hasil filter kosong |
| | CTA, SEO | (standar; SEO terpisah untuk `/properti` dan `/properti/kawasan`) |
| **Detail Kawasan (template)** | Label section | Eyebrow hero, judul section Tentang Kawasan, Fasilitas kawasan, Cluster di kawasan, Kawasan lainnya (toggle tiap section) |
| | CTA, SEO default | Pola title/description untuk semua kawasan; konten per kawasan diatur di resource Kawasan |
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

Konten yang berupa koleksi (kawasan, cluster, tipe rumah, promo, fasilitas, pengembangan, artikel) tetap dikelola di resource masing-masing. Settings halaman hanya mengatur **teks section, tampil/tidak, dan item mana yang ditampilkan**.

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
- Produk Listing: `ItemList` berisi URL cluster (`/properti`) atau URL kawasan + cluster mandiri (`/properti/kawasan`).
- Detail Kawasan: `Place` (nama, deskripsi, `geo`, `image`, `containedInPlace` = lokasi Arunika) + `ItemList` cluster di dalamnya.
- Detail Rumah: `Residence`/`SingleFamilyResidence` (atau `House`) dengan `floorSize`, `numberOfRooms`, `numberOfBathroomsTotal`, `address`, `image`, plus `Offer` (harga mulai, `priceCurrency: IDR`, `availability`). Catatan: tidak ada jaminan rich result, tapi markup tetap valid.
- Detail Artikel: `BlogPosting`/`NewsArticle` (headline, image, `datePublished`, `dateModified`, author `Person`, publisher).
- FAQ (kalau nanti ada): `FAQPage` boleh dipasang untuk pemahaman konten; jangan berharap tampil sebagai rich result.
- Validasi semua tipe di Rich Results Test / Schema Markup Validator; tambahkan test yang mem-parse JSON-LD tiap tipe halaman.

### 8.4 Sitemap, robots, feed
- `/sitemap.xml` sebagai sitemap index → `sitemap-pages.xml`, `sitemap-properti.xml` (`/properti/kawasan`, semua detail kawasan, semua cluster), `sitemap-artikel.xml` (termasuk kategori). Isi `lastmod` dari `updated_at`. Regenerasi otomatis via observer/queue saat konten berubah + scheduler harian.
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
- Detail Rumah → link ke kawasannya (breadcrumb + label) dan "Listing lainnya" (utamakan cluster lain di kawasan yang sama); Detail Kawasan → semua clusternya + kawasan lain; Detail Artikel → artikel terkait (kategori/tag sama) + CTA ke listing relevan.
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
- Test tambahan kawasan: cluster mandiri tidak muncul di kawasan mana pun tapi muncul di section "Cluster yang berdiri sendiri"; filter `?kawasan=` benar; rentang harga/LT/KT kartu cluster sesuai tipe-tipe yang dipublikasikan; `/properti/kawasan` tidak tertangkap sebagai slug cluster.
- Seeder dummy (ikuti persis data di desain): 1 profil lokasi; 3 kawasan (Arunika Garden: Vega Garden, Lyra Residence, Orion Park; Arunika Hills: Kirana Hills, Nara Village; Arunika Lakeside: Sora Terrace, Sora Terrace II); 2 cluster mandiri (Kalea Townhouse, Hana Residence); total 20 tipe — Vega Garden 3 (Altair 6×12, Deneb 7×15, Rigel 8×15), Lyra Residence 2 (Aster 6×12, Iris 7×12), Orion Park 4 (Nova 8×15, Stella 8×16, Luna 9×18, Sol 10×18), Kirana Hills 2, Nara Village 2, Sora Terrace 2, Sora Terrace II 2, Kalea Townhouse 1, Hana Residence 2 (nama tipe dan kavling lihat `docs/design/desktop/02a-properti-cluster.html`); 9 fasilitas, 4 future development, 9 artikel di 5 kategori, 2 promo. Tandai semua data dummy dengan kurung siku `[...]` di teks yang harus diganti.
- `README.md` dengan format seperti rezabsd: Requirement, Instalasi Pertama Kali (composer install, npm install, cp .env.example .env, key:generate, buat database.sqlite, migrate --seed, storage:link), akun admin seeder, Menjalankan di Lokal (`php artisan serve` + `npm run dev`), Mode Production-like dengan SSR, Mengisi Konten (daftar menu admin), Struktur Penting, dan Deploy ke VPS (Supervisor/PM2 untuk SSR + queue worker).

## 11. Milestone (kerjakan berurutan)

1. **Setup** — Laravel 13 + React starter kit (Inertia v3, TS) + SSR aktif + Filament 5 + MySQL + Tailwind token + font self-host. Layout global (header, footer, drawer mobile) ter-render SSR.
2. **Model & admin** — migrasi, model, relasi (termasuk `kawasans` dan `clusters.kawasan_id` nullable), seeder dummy (termasuk isi awal semua settings halaman), semua Filament resource + tab SEO, Pengaturan Global, Menu Navigasi, dan satu settings page per halaman (7A).
3. **Halaman publik** — Home, Produk Listing (tampilan Cluster & Kawasan), Detail Kawasan, Detail Rumah, Fasilitas, Artikel, Kategori, Detail Artikel, Tentang, Kontak, 404, sesuai desain desktop & mobile.
4. **Lead & tracking** — form, anti-spam, UTM capture, halaman terima kasih, notifikasi, event analytics/Pixel.
5. **Technical SEO** — meta/OG, canonical, JSON-LD, sitemap, robots, RSS, redirect manager, noindex rules.
6. **Performa** — optimasi gambar, facade peta/video, code-splitting, cache, audit Lighthouse mobile ≥ 90.
7. **QA** — Pest test hijau, cek SSR tiap halaman via curl, validasi structured data, checklist aksesibilitas, README deploy.

Di akhir tiap milestone: ringkas apa yang dikerjakan, apa yang belum, dan keputusan yang butuh konfirmasi.
