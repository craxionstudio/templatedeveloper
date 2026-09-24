<?php

$shared = require __DIR__.'/_shared.php';

return [
    'header' => [
        'eyebrow' => 'Fasilitas Developer',
        'title' => 'Semua yang kamu butuhkan, dalam jarak jalan kaki.',
        'description' => 'Fasilitas di kawasan Arunika dibangun dan dikelola langsung oleh developer, terbuka untuk seluruh penghuni dan terus bertambah seiring pengembangan kawasan.',
        'stats' => [
            ['value' => '[XX]+', 'label' => 'Fasilitas aktif'],
            ['value' => '[XX] ha', 'label' => 'Ruang terbuka hijau'],
            ['value' => '24 jam', 'label' => 'Keamanan terpadu'],
        ],
        // 3 gambar: utama + 2 pendamping.
        'images' => [
            ['image' => null, 'alt' => 'Central Park'],
            ['image' => null, 'alt' => 'Clubhouse'],
            ['image' => null, 'alt' => 'Sekolah'],
        ],
    ],

    'list' => [
        // Kosong = semua kategori yang punya fasilitas.
        'categories' => [],
        'all_label' => 'Semua',
        'show_kawasan_filter' => true,
        'kawasan_filter_label' => 'Kawasan',
        'all_kawasan_label' => 'Semua kawasan',
        'everywhere_label' => 'Semua kawasan',
    ],

    'cta' => $shared['cta'],

    'seo' => [
        ...$shared['seo'],
        'meta_title' => 'Fasilitas Kawasan',
        'meta_description' => 'Fasilitas di kota Arunika dibangun dan dikelola langsung oleh developer: taman, clubhouse, sekolah, klinik, area komersial, dan keamanan 24 jam.',
    ],
];
