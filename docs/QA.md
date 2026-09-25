# Laporan QA (Milestone 7)

Hasil pemeriksaan sebelum serah terima. Semua dijalankan ulang dari nol di sesi build: clone bersih → `composer install` → `npm ci` → `migrate:fresh --seed` → `npm run build` → seluruh test.

## 1. Test otomatis (Pest)

**261 test, semua lolos** (`composer test` = Pint + Pest). Pemetaan ke brief bagian 10:

| Wajib di brief | File test |
|---|---|
| Section yang dimatikan di settings tidak dirender | `HomePageTest`, `PublicPagesTest` |
| Perubahan teks settings langsung tampil (cache ter-invalidate) | `HomePageTest`, `PerformanceTest` (cache halaman), `SeoTest` (sitemap) |
| Route publik 200 + SSR berisi H1 | `PublicPagesTest` (termasuk cek HTML SSR: satu H1, canonical, JSON-LD, og:image) |
| Halaman unpublished 404 | `PublicPagesTest`, `SeoTest` (pratinjau), 410 untuk konten dihapus |
| Redirect bekerja | `SeoTest` (Redirect Manager 301/302/410, slug lama, trailing slash, huruf kapital, host kanonik) |
| Sitemap hanya berisi URL yang dipublikasikan | `SeoTest` |
| robots berbeda prod/non-prod | `SeoTest` |
| JSON-LD valid per tipe halaman | `SeoTest` (Organization, WebSite, RealEstateAgent, BreadcrumbList, ItemList, Place, Residence + Offer, BlogPosting) |
| Submit lead tersimpan dengan UTM | `LeadTest` (UTM terakhir & first-touch, fbclid, gclid, spam, rate limit, CAPI event_id) |
| Test tambahan kawasan (mandiri, filter, rentang kartu, `/properti/kawasan`) | `PublicPagesTest` |
| Sanitasi HTML rich text | `PropertyModelTest`, `SecurityTest` |
| Security headers & CSP | `SecurityTest` |
| Akses admin per role | `AdminPanelTest`, `AdminFormsTest`, `AdminPagesTest` |

## 2. SSR tiap halaman (tanpa JavaScript)

`php artisan qa:pages --base=<url>` mengambil **setiap URL di sitemap** lewat HTTP (seperti crawler) dan memeriksa: status 200, tepat satu `<h1>`, `<title>`, canonical, meta robots, og:image, serta JSON-LD. Setiap blok JSON-LD harus berupa JSON valid, ber-`@context` schema.org, dan semua `@type`-nya dikenal.

Hasil di lingkungan build: **34 URL dicek, 0 bermasalah** (7 halaman, `/properti/kawasan`, 3 kawasan, 9 cluster, 5 kategori, 9 artikel).

## 3. Structured data

- Dibangun dengan `spatie/schema-org` (tipe & properti sesuai kosakata schema.org).
- Divalidasi otomatis oleh test per tipe halaman dan oleh `qa:pages` (JSON valid + `@type` dikenal).
- Validasi manual dengan Rich Results Test / Schema Markup Validator dijadwalkan setelah deploy, karena alat Google butuh URL publik (lihat `docs/CHECKLIST-LAUNCH.md` bagian 7).

## 4. Aksesibilitas

| Pemeriksaan | Hasil |
|---|---|
| axe-core 4 (WCAG 2.0/2.1 A & AA + best practice), 16 halaman × desktop & mobile | **0 pelanggaran** |
| Lighthouse Accessibility (mobile, 10 halaman) | **100** |
| Kontras warna | Terakota `#9A4524` (≥ 5:1 di semua latar), lihat `docs/CHANGES.md` |
| Skip link "Langsung ke konten utama" | Elemen fokus pertama, memindahkan fokus ke `<main>` |
| Menu mobile (drawer) | Fokus masuk ke tombol Tutup; Esc menutup dan fokus kembali ke tombol menu |
| Modal "Jadwalkan Kunjungan/Survey" | `<dialog>` native: fokus terkunci, Esc menutup, fokus kembali ke pemicu |
| Lightbox galeri | Fokus ke tombol Tutup, Tab berputar di dalam lightbox, panah kiri/kanan, Esc menutup, fokus kembali ke foto |
| Carousel geser (fasilitas Beranda) | Region bisa difokus dan digulir dengan keyboard |
| Form | Setiap input punya label; error ditautkan (`aria-describedby`, `aria-invalid`) dan diumumkan (`role="alert"`) |
| Gambar | Alt text wajib; placeholder foto memakai `role="img"` + `aria-label` |
| Heading | Satu H1 per halaman, urutan h1 → h2 → h3 (judul section tersembunyi `sr-only` bila perlu) |
| Bahasa & fokus | `lang="id"`, outline fokus terakota di semua elemen interaktif, touch target ≥ 44 px |
| Gerakan | `prefers-reduced-motion` mematikan smooth scroll |

## 5. Performa (Lighthouse mobile, lab)

Kondisi: `APP_ENV=production`, gzip/brotli (seperti nginx), cache halaman & CSP aktif, foto masih placeholder. LCP lab 2,3–2,6 dtk memakai simulasi 4G lambat Lighthouse (lebih berat dari kondisi lapangan rata-rata).

| Halaman | Performance | Accessibility | Best Practices | SEO | LCP | CLS |
|---|---:|---:|---:|---:|---:|---:|
| `/` | 95 | 100 | 100 | 100 | 2,6 dtk | 0 |
| `/properti` | 95 | 100 | 100 | 100 | 2,5 dtk | 0 |
| `/properti/kawasan` | 97 | 100 | 100 | 100 | 2,3 dtk | 0 |
| `/properti/kawasan/arunika-garden` | 95 | 100 | 100 | 100 | 2,6 dtk | 0 |
| `/properti/vega-garden` | 94 | 100 | 100 | 100 | 2,6 dtk | 0 |
| `/fasilitas` | 97 | 100 | 100 | 100 | 2,3 dtk | 0 |
| `/artikel` | 97 | 100 | 100 | 100 | 2,3 dtk | 0 |
| Detail artikel | 95 | 100 | 100 | 100 | 2,6 dtk | 0 |
| `/tentang-kami` | 97 | 100 | 100 | 100 | 2,3 dtk | 0 |
| `/kontak` | 96 | 100 | 100 | 100 | 2,3 dtk | 0 |

Ukur ulang dengan PageSpeed Insights setelah foto asli dan ID tracking diisi (akses ke Google/Facebook diblok di lingkungan build, jadi dampak GTM/Pixel belum terukur).

## 6. Keamanan

- Header di semua respons (termasuk admin): `X-Content-Type-Options: nosniff`, `X-Frame-Options: SAMEORIGIN`, `Referrer-Policy: strict-origin-when-cross-origin`, `Permissions-Policy`; HSTS di production (HTTPS).
- **CSP** halaman publik: nonce per request + `'strict-dynamic'`, `object-src 'none'`, `base-uri 'self'`, `form-action 'self'`, `frame-ancestors 'self'`. Nonce diganti baru juga untuk halaman dari cache. Diuji di browser: 0 pelanggaran CSP di semua halaman, navigasi client, modal, dan submit form (dengan GTM/Pixel aktif). Admin Filament tidak diberi CSP (Alpine/Livewire).
- Rich text disanitasi saat disimpan **dan** saat dikirim ke browser (artikel, deskripsi cluster/kawasan, sejarah, kebijakan privasi).
- Form publik: CSRF, honeypot, rate limit (IP + nomor WA), Turnstile; data rahasia (token CAPI, secret Turnstile) terenkripsi dan tidak pernah dikirim ke browser.
- Akun seeder di production tidak memakai password `password`, dan password tidak ter-reset kalau seeder dijalankan ulang.
- Backup harian database + file unggahan (`spatie/laravel-backup`), email hanya kalau gagal.
