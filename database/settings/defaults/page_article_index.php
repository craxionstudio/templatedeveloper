<?php

$shared = require __DIR__.'/_shared.php';

return [
    'header' => [
        'eyebrow' => 'Artikel & Berita',
        'title' => 'Kabar & inspirasi dari BSD City',
        'description' => 'Berita kawasan, tips membeli rumah, dan cerita keseharian para penghuni.',
        // null = artikel highlight terbaru.
        'highlight_article_id' => null,
        'highlight_badge' => 'Highlight',
    ],

    'list' => [
        'per_page' => 9,
        // Kosong = semua kategori.
        'categories' => [],
        'all_label' => 'Semua',
        'show_search' => true,
        'search_placeholder' => 'Cari artikel',
        'count_suffix' => 'artikel',
        'empty_text' => 'Belum ada artikel yang cocok.',
        // Tampil kalau belum ada artikel yang dipublikasikan sama sekali (filter & pencarian disembunyikan).
        'empty_title' => 'Artikel segera hadir',
        'empty_description' => 'Kami sedang menyiapkan kabar BSD City, tips KPR, dan panduan memilih cluster. Sambil menunggu, lihat dulu daftar cluster.',
        'empty_button_label' => 'Lihat semua cluster',
        'empty_button_url' => '/properti',
    ],

    'newsletter' => [
        'enabled' => true,
        'title' => 'Dapatkan info promo & progres kawasan lebih dulu',
        'description' => 'Satu email per bulan. Bisa berhenti kapan saja.',
        'email_placeholder' => 'Email',
        'button_label' => 'Langganan',
    ],

    'seo' => [
        ...$shared['seo'],
        'meta_title' => 'Info & Tips Properti BSD City',
        'meta_description' => 'Kabar terbaru BSD City, tips KPR, dan panduan memilih cluster yang pas untuk keluarga.',
    ],
];
