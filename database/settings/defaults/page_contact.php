<?php

$shared = require __DIR__.'/_shared.php';

return [
    'header' => [
        'eyebrow' => 'Kontak',
        'title' => 'Ngobrol langsung dengan tim marketing',
        'description' => 'Datang ke kantor pemasaran atau tinggalkan nomor WhatsApp, tim kami akan menghubungi kamu.',
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

    'form' => [
        'enabled' => true,
        'title' => 'Kirim pesan',
        'name_label' => 'Nama',
        'whatsapp_label' => 'WhatsApp',
        'email_label' => 'Email (opsional)',
        'interest_label' => 'Minat cluster',
        'interest_placeholder' => 'Belum tahu / ingin konsultasi',
        'payment_label' => 'Rencana pembayaran',
        'payment_options' => [
            ['label' => 'KPR'],
            ['label' => 'Cash bertahap'],
            ['label' => 'Cash keras'],
        ],
        'message_label' => 'Pesan',
        'consent_label' => 'Saya setuju data saya diproses sesuai Kebijakan Privasi.',
        'submit_label' => 'Kirim',
    ],

    'cta' => [...$shared['cta'], 'enabled' => false],

    'seo' => [
        ...$shared['seo'],
        'meta_title' => 'Kontak',
        'meta_description' => 'Hubungi kantor pemasaran Arunika Land: alamat, jam buka, WhatsApp, dan form konsultasi.',
    ],
];
