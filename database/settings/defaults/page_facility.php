<?php

$shared = require __DIR__.'/_shared.php';

return [
    'header' => [
        'eyebrow' => 'Fasilitas Kota',
        'title' => 'Semua yang kamu butuhkan, dalam jarak jalan kaki.',
        'description' => 'Sekolah, kampus, rumah sakit, pusat belanja, dan ruang terbuka di BSD City, terus bertambah seiring pengembangan kota.',
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
        // Tampil kalau belum ada fasilitas yang dipublikasikan (stat, foto header, dan filter disembunyikan).
        'empty_title' => 'Daftar fasilitas sedang kami lengkapi',
        'empty_description' => 'Sementara itu, fasilitas tiap kawasan bisa kamu lihat di halaman kawasan, atau tanyakan langsung ke tim marketing.',
        'empty_button_label' => 'Lihat kawasan',
        'empty_button_url' => '/properti/kawasan',
    ],

    'cta' => $shared['cta'],

    'seo' => [
        ...$shared['seo'],
        'meta_title' => 'Fasilitas Kota BSD City',
        'meta_description' => 'Sekolah, kampus, rumah sakit, mal, stasiun, dan akses tol di sekitar BSD City. Semua yang dekat dari rumah kamu nanti.',
    ],
];
