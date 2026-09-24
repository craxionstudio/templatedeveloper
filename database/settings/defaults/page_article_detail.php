<?php

$shared = require __DIR__.'/_shared.php';

return [
    'display' => [
        'show_author' => true,
        'show_reading_time' => true,
        'show_share' => true,
        'show_tags' => true,
        'updated_label' => 'Diperbarui',
    ],

    'related' => [
        'enabled' => true,
        'title' => 'Artikel terkait',
        'limit' => 3,
        'link_label' => 'Semua artikel',
        'link_url' => '/artikel',
    ],

    'cta' => $shared['cta'],

    'seo' => [
        'title_pattern' => '{title}',
        'description_pattern' => '{excerpt}',
    ],
];
