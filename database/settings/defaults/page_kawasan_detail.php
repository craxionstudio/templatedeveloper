<?php

$shared = require __DIR__.'/_shared.php';

return [
    'hero' => [
        'eyebrow' => 'Kawasan di BSD City',
        'stat_area_label' => 'Luas kawasan',
        'stat_price_label' => 'Harga mulai',
    ],

    'about' => [
        'enabled' => true,
        'eyebrow' => 'Tentang Kawasan',
        'brochure_label' => 'Unduh Brosur Kawasan',
        'map_label' => 'Lihat Peta',
    ],

    'facilities' => [
        'enabled' => true,
        'title' => 'Fasilitas kawasan',
        // Daftar "Lokasi & akses" per kawasan (kosong = tidak tampil).
        'access_title' => 'Lokasi & akses',
    ],

    // Promo aktif yang terhubung ke kawasan + promo aktif cluster-cluster di kawasan. Kosong = tidak tampil.
    'promos' => [
        'enabled' => true,
        'eyebrow' => 'Promo',
        'title' => 'Promo di kawasan ini',
        'period_prefix' => 'Berlaku s.d.',
    ],

    'clusters' => [
        'enabled' => true,
        // {kawasan} diganti otomatis. Tanpa jumlah cluster/tipe (Update 3).
        'eyebrow' => 'Cluster di {kawasan}',
        'title' => 'Pilihan rumah di {kawasan}',
        // Cluster tanpa halaman sendiri di kawasan ini: chip nama saja. Kosong = section tidak tampil.
        'other_title' => 'Cluster lain di kawasan ini',
        'link_label' => 'Semua cluster',
        'link_url' => '/properti',
    ],

    'others' => [
        'enabled' => true,
        'eyebrow' => 'Kawasan lainnya',
        'title' => 'Jelajahi kawasan lain',
        'link_label' => 'Semua kawasan',
        'link_url' => '/properti/kawasan',
        'limit' => 2,
    ],

    'cta' => $shared['cta'],

    // Pola untuk semua kawasan. Isi per kawasan (tab SEO di resource Kawasan) menimpa pola ini.
    'seo' => [
        'title_pattern' => 'Kawasan {name}',
        'description_pattern' => '{summary}',
    ],
];
