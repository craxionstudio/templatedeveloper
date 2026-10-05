<?php

$shared = require __DIR__.'/_shared.php';

return [
    'hero' => [
        'enabled' => true,
        'eyebrow' => 'Serpong, Tangerang',
        'title' => 'Pilih rumah di kota seluas 6.000 hektare.',
        'description' => 'Dari cluster baru di Vireya dan Terravia sampai NavaPark, temukan rumah yang pas untuk keluarga. Bandingkan tipe dan harga, lalu atur jadwal survey.',
        'image' => null,
        'image_mobile' => null,
        'image_alt' => 'Foto aerial BSD City',
        'video_url' => '',
        'primary_label' => 'Lihat Semua Cluster',
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

    'listing' => [
        'enabled' => true,
        'eyebrow' => 'Pilihan Properti',
        'title' => 'Temukan rumah di BSD City',
        'source' => 'auto',
        'items' => [],
        'limit' => 4,
        'button_label' => 'Lihat Semua Cluster',
        'button_url' => '/properti',
    ],

    'region' => [
        'enabled' => true,
        'eyebrow' => 'Keunggulan Wilayah',
        'title' => 'Lokasi yang terhubung ke pusat kota',
        'description' => 'Kawasan-kawasan di BSD City terhubung ke tol Jakarta-Serpong, JORR, dan Commuter Line, jadi waktu di jalan lebih singkat dan waktu bersama keluarga lebih banyak.',
        // true = peta, badge, dan poin keunggulan diambil dari Profil Lokasi.
        'use_area' => true,
    ],

    'facilities' => [
        'enabled' => false,
        'eyebrow' => 'Fasilitas Developer',
        'title' => 'Semua kebutuhan harian, ada di dalam kawasan',
        'source' => 'auto',
        'items' => [],
        'limit' => 4,
        'link_label' => 'Lihat semua fasilitas',
        'link_url' => '/fasilitas',
    ],

    'developments' => [
        'enabled' => false,
        'eyebrow' => 'Pengembangan Mendatang',
        'title' => 'Kawasan yang terus bertumbuh, nilai properti ikut naik',
        'description' => 'Rencana pengembangan BSD City untuk beberapa tahun ke depan.',
        'disclaimer' => 'Jadwal bersifat estimasi dan dapat berubah mengikuti perizinan.',
        'source' => 'auto',
        'items' => [],
        'limit' => 4,
    ],

    'articles' => [
        'enabled' => false,
        'eyebrow' => 'Artikel & Berita',
        'title' => 'Kabar terbaru dari BSD City',
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
        'meta_description' => 'Rumah di BSD City, dari cluster baru sampai kawasan mapan: Vireya, Terravia, Eonna, NavaPark, dan lainnya. Bandingkan tipe dan harga, lalu atur survey.',
    ],
];
