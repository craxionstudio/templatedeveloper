<?php

$shared = require __DIR__.'/_shared.php';

return [
    'header' => [
        'eyebrow' => 'Artikel & Berita',
        'title' => 'Kabar & inspirasi dari Arunika',
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
        'meta_title' => 'Artikel & Berita',
        'meta_description' => 'Berita kawasan Arunika, tips membeli rumah dan KPR, serta cerita keseharian para penghuni.',
    ],
];
