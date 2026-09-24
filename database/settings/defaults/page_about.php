<?php

$shared = require __DIR__.'/_shared.php';

return [
    'hero' => [
        'enabled' => true,
        'eyebrow' => 'Tentang Kami',
        // Kosong = pakai headline & deskripsi Profil Developer.
        'title' => '',
        'description' => '',
        'image' => null,
        'image_alt' => 'Foto kantor Arunika Land',
    ],

    'history' => [
        'enabled' => true,
        'title' => 'Sejarah singkat',
        // Kosong = pakai sejarah di Profil Developer.
        'body' => '',
    ],

    'vision' => [
        'enabled' => true,
        'title' => 'Visi & Misi',
        'vision_label' => 'Visi',
        'vision' => '[VISI PERUSAHAAN]',
        'mission_label' => 'Misi',
        'missions' => [
            ['text' => '[MISI 1]'],
            ['text' => '[MISI 2]'],
            ['text' => '[MISI 3]'],
        ],
    ],

    'stats' => [
        'enabled' => true,
        'title' => 'Dalam angka',
        // true = statistik dari Profil Developer.
        'use_profile' => true,
    ],

    'timeline' => [
        'enabled' => true,
        'title' => 'Perjalanan kami',
        'items' => [
            ['year' => '[TAHUN]', 'title' => '[TONGGAK PERUSAHAAN 1]', 'description' => '[KETERANGAN SINGKAT]'],
            ['year' => '[TAHUN]', 'title' => '[TONGGAK PERUSAHAAN 2]', 'description' => '[KETERANGAN SINGKAT]'],
            ['year' => '[TAHUN]', 'title' => '[TONGGAK PERUSAHAAN 3]', 'description' => '[KETERANGAN SINGKAT]'],
        ],
    ],

    'team' => [
        'enabled' => false,
        'title' => 'Manajemen',
        'items' => [],
    ],

    'awards' => [
        'enabled' => false,
        'title' => 'Penghargaan',
        'items' => [],
    ],

    'cta' => $shared['cta'],

    'seo' => [
        ...$shared['seo'],
        'meta_title' => 'Tentang Kami',
    ],
];
