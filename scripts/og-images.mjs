/**
 * Bangun ulang OG image bawaan (public/og/{section}.png, 1200×630) dengan font & warna situs.
 * Dipakai kalau halaman tidak punya OG image sendiri (lihat App\Support\PageMeta::OG_SECTIONS).
 *
 * Jalankan: npm i --no-save playwright && node scripts/og-images.mjs
 * (atau set PLAYWRIGHT_CHROMIUM=/path/ke/chrome kalau Chromium bawaan Playwright tidak ada)
 */
import { readFileSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import { chromium } from 'playwright';

const root = fileURLToPath(new URL('..', import.meta.url));

const brand = {
    name: 'BSD City',
    tagline: 'Kota mandiri Sinar Mas Land',
    footer: 'bsd city · serpong, tangerang',
};

const images = {
    default: ['BSD City', 'Kota mandiri Sinar Mas Land di Serpong, Tangerang.'],
    home: ['BSD City', 'Pilih rumah di kota seluas 6.000 hektare.'],
    properti: [
        'Properti BSD City',
        'Cluster rumah dari Vireya, Terravia, sampai NavaPark.',
    ],
    kawasan: [
        'Kawasan di BSD City',
        'Fasilitas, akses, dan cluster di tiap kawasan.',
    ],
    rumah: ['Detail Rumah', 'Tipe, denah, spesifikasi, dan harga mulai.'],
    fasilitas: [
        'Fasilitas Kota',
        'Sekolah, kampus, rumah sakit, dan pusat belanja di BSD City.',
    ],
    artikel: [
        'Artikel & Berita',
        'Kabar BSD City, tips KPR, dan panduan memilih cluster.',
    ],
    tentang: [
        'Tentang BSD City',
        'Kota terencana seluas sekitar 6.000 hektare dari Sinar Mas Land.',
    ],
    kontak: [
        'Kantor Pemasaran',
        'Tanya harga, tipe rumah, atau jadwal survey.',
    ],
};

const font = (file) =>
    `data:font/woff2;base64,${readFileSync(`${root}docs/design/fonts/${file}`).toString('base64')}`;
const escape = (text) => text.replace(/&/g, '&amp;').replace(/</g, '&lt;');

const html = (
    title,
    subtitle,
) => `<!doctype html><html><head><meta charset="utf-8"><style>
@font-face { font-family: Fraunces; font-weight: 500; src: url(${font('fraunces-latin-500-normal.woff2')}); }
@font-face { font-family: Fraunces; font-weight: 600; src: url(${font('fraunces-latin-600-normal.woff2')}); }
@font-face { font-family: Jakarta; font-weight: 400; src: url(${font('plus-jakarta-sans-latin-400-normal.woff2')}); }
@font-face { font-family: Jakarta; font-weight: 600; src: url(${font('plus-jakarta-sans-latin-600-normal.woff2')}); }
* { margin: 0; box-sizing: border-box; }
body { width: 1200px; height: 630px; background: #23392e; font-family: Jakarta; color: #f4f1ea; position: relative; overflow: hidden; }
.side { position: absolute; top: 0; right: 0; width: 420px; height: 616px; background: repeating-linear-gradient(135deg, #2c4538 0 14px, #2f493b 14px 28px); }
.bar { position: absolute; left: 0; right: 0; bottom: 0; height: 14px; background: #a94f2a; }
.content { position: absolute; left: 80px; top: 76px; bottom: 76px; width: 660px; display: flex; flex-direction: column; }
.logo { display: flex; gap: 16px; align-items: center; }
.logo svg { width: 44px; height: 44px; color: #e9a07f; }
.logo b { display: block; font-family: Fraunces; font-weight: 600; font-size: 32px; line-height: 1.1; }
.logo small { display: block; margin-top: 6px; font-size: 14px; letter-spacing: .12em; text-transform: uppercase; color: #a9b8ae; }
.main { margin: auto 0; }
h1 { font-family: Fraunces; font-weight: 500; font-size: 76px; line-height: 1.08; }
p { margin-top: 24px; font-size: 30px; line-height: 1.35; color: #c9d6cd; }
.foot { font-size: 21px; font-weight: 600; color: #e9a07f; }
</style></head><body>
<div class="side"></div><div class="bar"></div>
<div class="content">
  <div class="logo"><svg viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 28V14L16 4l12 10v14"/><path d="M12 28v-8h8v8"/></svg>
    <span><b>${escape(brand.name)}</b><small>${escape(brand.tagline)}</small></span></div>
  <div class="main"><h1>${escape(title)}</h1><p>${escape(subtitle)}</p></div>
  <div class="foot">${escape(brand.footer)}</div>
</div></body></html>`;

const browser = await chromium.launch(
    process.env.PLAYWRIGHT_CHROMIUM
        ? { executablePath: process.env.PLAYWRIGHT_CHROMIUM }
        : {},
);
const page = await browser.newPage({ viewport: { width: 1200, height: 630 } });

for (const [section, [title, subtitle]] of Object.entries(images)) {
    await page.setContent(html(title, subtitle));
    await page.evaluate(() => document.fonts.ready);
    await page.screenshot({ path: `${root}public/og/${section}.png` });
    console.log(`public/og/${section}.png`);
}

await browser.close();
