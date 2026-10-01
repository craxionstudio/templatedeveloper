<?php

$shared = require __DIR__.'/_shared.php';

return [
    'header' => [
        'eyebrow' => 'Serpong, Tangerang',
        'title' => 'Properti BSD City',
        'description' => 'Pilih rumah berdasarkan cluster, atau jelajahi dulu kawasan-kawasan di BSD City.',
        'image' => null,
        'image_mobile' => null,
        'image_alt' => 'Foto aerial BSD City',
        // Angka statistik dihitung otomatis; hanya label yang bisa diubah.
        'stat_kawasan_label' => 'Kawasan',
        'stat_cluster_label' => 'Cluster',
        'stat_price_label' => 'Harga mulai',
    ],

    // /properti = tampilan Cluster, /properti/kawasan = tampilan Kawasan (dua URL terpisah).
    'toggle' => [
        'cluster_label' => 'Cluster',
        'kawasan_label' => 'Kawasan',
    ],

    'cluster_view' => [
        // Filter yang ditampilkan: kawasan, tipe, kamar, harga, status.
        'filters' => ['kawasan', 'tipe', 'kamar', 'harga', 'benefit'],
        'filter_labels' => [
            'kawasan' => 'Kawasan',
            'tipe' => 'Tipe properti',
            'kamar' => 'Kamar tidur',
            'harga' => 'Kisaran harga',
            'status' => 'Status',
            'benefit' => 'Promo & benefit',
        ],
        'all_kawasan_label' => 'Semua kawasan',
        'standalone_label' => 'Cluster mandiri',
        'all_label' => 'Semua',
        'apply_label' => 'Terapkan',
        'mobile_filter_label' => 'Filter',
        'bedroom_options' => [2, 3, 4, 5],
        'price_ranges' => [
            ['label' => '< Rp 1 M', 'min' => null, 'max' => 1_000_000_000],
            ['label' => 'Rp 1 – 2 M', 'min' => 1_000_000_000, 'max' => 2_000_000_000],
            ['label' => 'Rp 2 – 3 M', 'min' => 2_000_000_000, 'max' => 3_000_000_000],
            ['label' => '> Rp 3 M', 'min' => 3_000_000_000, 'max' => null],
        ],
        'sort_label' => 'Urutkan',
        'sort_options' => [
            'terbaru' => 'Terbaru',
            'harga-terendah' => 'Harga terendah',
            'harga-tertinggi' => 'Harga tertinggi',
            // Cluster dengan benefit terbanyak di atas.
            'promo' => 'Promo',
        ],
        'default_sort' => 'terbaru',
        'per_page' => 9,
        // {clusters} dan {types} diganti angka.
        'result_template' => 'Menampilkan {clusters} cluster · {types} tipe rumah',
    ],

    'kawasan_view' => [
        // {kawasan}, {clusters}, {standalone} diganti angka.
        'summary_template' => '{kawasan} kawasan dengan total {clusters} cluster, ditambah {standalone} cluster mandiri.',
        'kawasan_eyebrow' => 'Kawasan',
        'view_button_label' => 'Lihat kawasan',
        'standalone' => [
            'enabled' => true,
            'eyebrow' => 'Cluster mandiri',
            'title' => 'Cluster yang berdiri sendiri',
            'description' => 'Tidak termasuk kawasan mana pun, dengan fasilitas dan gerbang sendiri.',
        ],
    ],

    'empty_state' => [
        'title' => 'Belum ada cluster yang cocok',
        'description' => 'Coba ubah atau hapus sebagian filter untuk melihat pilihan lain.',
        'button_label' => 'Reset filter',
    ],

    'cta' => $shared['cta'],

    'seo_cluster' => [
        ...$shared['seo'],
        'meta_title' => 'Daftar Cluster Rumah di BSD City',
        'meta_description' => 'Semua cluster rumah di BSD City dalam satu halaman. Saring per kawasan, tipe, jumlah kamar, dan harga. Info LT, LB, dan cicilan tiap tipe.',
        // /properti?benefit=tanpa-dp (satu benefit) boleh diindex dengan judul sendiri; {benefit} = nama benefit.
        'benefit_title_pattern' => 'Rumah {benefit} di BSD City',
        'benefit_description_pattern' => 'Daftar cluster rumah di BSD City dengan promo {benefit}. Bandingkan tipe, luas, dan harga, lalu tanya syarat lengkapnya ke marketing.',
    ],

    'seo_kawasan' => [
        ...$shared['seo'],
        'meta_title' => 'Kawasan Hunian di BSD City',
        'meta_description' => 'Kenali kawasan di BSD City sebelum memilih rumah: fasilitas, akses tol dan stasiun, dan cluster di tiap kawasan.',
    ],
];
