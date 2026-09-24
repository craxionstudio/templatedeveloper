<?php

$shared = require __DIR__.'/_shared.php';

return [
    'hero' => [
        'enabled' => true,
        'eyebrow' => 'Arunika Land · Sejak [TAHUN]',
        'title' => 'Kota yang tumbuh bersama keluargamu.',
        'description' => 'Kawasan hunian terpadu lengkap dengan sekolah, pusat belanja, taman, dan akses transportasi yang terus berkembang.',
        'image' => null,
        'image_mobile' => null,
        'image_alt' => 'Foto aerial kota Arunika',
        'video_url' => '',
        'primary_label' => 'Lihat Semua Listing',
        'primary_url' => '/properti',
        'secondary_label' => 'Chat Marketing',
        // Kosong = link WhatsApp.
        'secondary_url' => '',
    ],

    'about' => [
        'enabled' => true,
        'eyebrow' => 'Tentang Developer',
        // true = judul, isi, statistik, dan foto diambil dari Profil Developer.
        'use_profile' => true,
        'title' => '',
        'description' => '',
        'link_label' => 'Profil lengkap perusahaan',
        'link_url' => '/tentang-kami',
    ],

    'promo' => [
        'enabled' => true,
        'source' => 'auto',
        'items' => [],
        'autoplay' => false,
    ],

    'listing' => [
        'enabled' => true,
        'eyebrow' => 'Pilihan Properti',
        'title' => 'Temukan rumah di kawasan Arunika',
        'source' => 'auto',
        'items' => [],
        'limit' => 4,
        'button_label' => 'Lihat Semua Listing',
        'button_url' => '/properti',
    ],

    'region' => [
        'enabled' => true,
        'eyebrow' => 'Keunggulan Wilayah',
        'title' => 'Lokasi yang terhubung ke pusat kota',
        'description' => 'Setiap kawasan Arunika dipilih dekat dengan simpul transportasi dan pusat aktivitas, jadi waktu di jalan lebih singkat dan waktu bersama keluarga lebih banyak.',
        // true = peta, badge, dan poin keunggulan diambil dari Profil Lokasi.
        'use_area' => true,
    ],

    'facilities' => [
        'enabled' => true,
        'eyebrow' => 'Fasilitas Developer',
        'title' => 'Semua kebutuhan harian, ada di dalam kawasan',
        'source' => 'auto',
        'items' => [],
        'limit' => 4,
        'link_label' => 'Lihat semua fasilitas',
        'link_url' => '/fasilitas',
    ],

    'developments' => [
        'enabled' => true,
        'eyebrow' => 'Pengembangan Mendatang',
        'title' => 'Kawasan yang terus bertumbuh, nilai properti ikut naik',
        'description' => 'Rencana pengembangan kawasan Arunika untuk beberapa tahun ke depan.',
        'disclaimer' => 'Jadwal bersifat estimasi dan dapat berubah mengikuti perizinan.',
        'source' => 'auto',
        'items' => [],
        'limit' => 4,
    ],

    'articles' => [
        'enabled' => true,
        'eyebrow' => 'Artikel & Berita',
        'title' => 'Kabar terbaru dari Arunika',
        // null = artikel highlight terbaru.
        'main_article_id' => null,
        'source' => 'auto',
        'items' => [],
        'limit' => 3,
        'link_label' => 'Semua artikel',
        'link_url' => '/artikel',
    ],

    'cta' => $shared['cta'],

    'seo' => [
        ...$shared['seo'],
        'meta_description' => 'Kawasan hunian terpadu di Serpong lengkap dengan sekolah, pusat belanja, taman, dan akses transportasi yang terus berkembang.',
    ],
];
