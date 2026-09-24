<?php

$shared = require __DIR__.'/_shared.php';

return [
    'hero' => [
        'eyebrow' => 'Kawasan di kota Arunika',
        'stat_area_label' => 'Luas kawasan',
        'stat_cluster_label' => 'Cluster',
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
    ],

    'clusters' => [
        'enabled' => true,
        // {kawasan}, {clusters}, {types} diganti otomatis.
        'eyebrow' => 'Cluster di {kawasan}',
        'title' => '{clusters} cluster, {types} tipe rumah',
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
