<?php

// Halaman Terima Kasih sudah dihapus (Okt 2026). File ini hanya dipakai migrasi settings lama
// (2026_09_24_100120); propertinya dihapus lagi oleh 2026_10_05_200000_whatsapp_only_and_ga4.

return [
    'content' => [
        'title' => 'Terima kasih, data kamu sudah kami terima',
        'message' => 'Tim marketing kami akan menghubungi kamu lewat WhatsApp dalam 1×24 jam kerja.',
        'whatsapp_label' => 'Lanjut chat WhatsApp',
        // {name} dan {cluster} diganti otomatis.
        'whatsapp_message' => 'Halo, saya {name}. Saya baru saja mengisi form untuk {cluster}.',
        'links' => [
            ['label' => 'Lihat semua listing', 'url' => '/properti'],
            ['label' => 'Baca artikel', 'url' => '/artikel'],
        ],
    ],

    // noindex dikunci untuk halaman ini.
    'seo' => [
        'meta_title' => 'Terima kasih',
        'meta_description' => '',
    ],
];
