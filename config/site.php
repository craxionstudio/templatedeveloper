<?php

return [
    /*
    | Boleh diindeks mesin pencari? Terpisah dari APP_ENV: production di domain sementara tetap
    | noindex. false (bawaan) = X-Robots-Tag & meta robots "noindex, nofollow" di semua halaman dan
    | robots.txt "Disallow: /". true = normal (index, follow) + sitemap di robots.txt.
    | Ubah ke true HANYA saat domain final sudah siap (tidak pernah diubah otomatis oleh deploy).
    */
    'indexable' => (bool) env('SITE_INDEXABLE', false),

    /*
    | Cache halaman publik (HTML awal hasil SSR) untuk tamu. Dibuang otomatis setiap konten,
    | media, atau settings berubah; TTL jadi pengaman untuk konten terjadwal (published_at).
    */
    'page_cache' => [
        'enabled' => (bool) env('PAGE_CACHE_ENABLED', env('APP_ENV') === 'production'),
        'ttl' => (int) env('PAGE_CACHE_TTL', 3600),
        'store' => env('PAGE_CACHE_STORE'),
    ],

    /*
    | Content-Security-Policy halaman publik (nonce + strict-dynamic). Default mati di lokal
    | supaya dev server Vite (HMR) tidak terblok.
    */
    'csp' => [
        'enabled' => (bool) env('CSP_ENABLED', env('APP_ENV') !== 'local'),
    ],
];
