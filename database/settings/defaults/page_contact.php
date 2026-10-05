<?php

// Halaman Kontak sudah dihapus (Okt 2026): info kontak pindah ke footer, menu Kontak membuka WhatsApp.
// File ini hanya dipakai migrasi settings lama (2026_09_24_100110); propertinya dihapus lagi oleh
// 2026_10_06_100000_remove_contact_page.

$shared = require __DIR__.'/_shared.php';

return [
    'header' => [
        'eyebrow' => 'Kontak',
        'title' => 'Ngobrol langsung dengan tim marketing',
        'description' => 'Datang ke kantor pemasaran atau chat WhatsApp, tim marketing kami siap membantu.',
    ],

    'info' => [
        'enabled' => true,
        'title' => 'Kantor Pemasaran',
        // Alamat, telepon, email, jam buka diambil dari Pengaturan Global → Kontak.
        'hours_label' => 'Jam buka',
        'phone_label' => 'Telepon',
        'email_label' => 'Email',
        'whatsapp_label' => 'WhatsApp',
    ],

    'map' => [
        'enabled' => true,
        'embed_url' => '',
        'image' => null,
        'image_alt' => 'Peta lokasi kantor pemasaran',
        'button_label' => 'Buka peta',
    ],

    'cta' => [...$shared['cta'], 'enabled' => false],

    'seo' => [
        ...$shared['seo'],
        'meta_title' => 'Kantor Pemasaran BSD City',
        'meta_description' => 'Tanya harga, tipe rumah, atau jadwal survey cluster di BSD City. Hubungi kantor pemasaran lewat WhatsApp.',
    ],
];
