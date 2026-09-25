<?php

return [
    /*
    | Cache halaman publik (HTML awal hasil SSR) untuk tamu. Dibuang otomatis setiap konten,
    | media, atau settings berubah; TTL jadi pengaman untuk konten terjadwal (published_at).
    */
    'page_cache' => [
        'enabled' => (bool) env('PAGE_CACHE_ENABLED', env('APP_ENV') === 'production'),
        'ttl' => (int) env('PAGE_CACHE_TTL', 3600),
        'store' => env('PAGE_CACHE_STORE'),
    ],
];
