<?php

$shared = require __DIR__.'/_shared.php';

return [
    'sections' => [
        // Sistem promo lama (tabel promos), disembunyikan dari website sejak Bank Benefit.
        'promo' => ['enabled' => true, 'title' => 'Promo rumah ini', 'period_prefix' => 'Berlaku s.d.'],
        // Bank Benefit: benefit yang dicentang di cluster. Tidak tampil kalau cluster tanpa benefit.
        // Pesan WhatsApp tombol: template "promo" di Pengaturan Umum.
        'benefits' => [
            'enabled' => true,
            'title' => 'Promo & Benefit',
            'disclaimer' => '*Syarat dan ketentuan berlaku dan dapat berubah sewaktu-waktu.',
            'button_label' => 'Dapatkan informasi lengkapnya via WhatsApp',
        ],
        'specs' => ['enabled' => true, 'title' => 'Spesifikasi rumah'],
        // {cluster} diganti nama cluster.
        'types' => ['enabled' => true, 'title' => 'Tipe-tipe rumah di {cluster}'],
        'description' => ['enabled' => true, 'title' => 'Deskripsi rumah'],
        // {cluster} diganti nama cluster. Kosong (cluster tanpa fasilitas) = tidak tampil.
        'facilities' => ['enabled' => true, 'title' => 'Fasilitas {cluster}'],
        'others' => [
            'enabled' => true,
            'eyebrow' => 'Listing lainnya',
            'title' => 'Rumah lain yang mungkin kamu suka',
            'link_label' => 'Lihat semua',
            'link_url' => '/properti',
        ],
    ],

    'pricing' => [
        'price_label' => 'Harga mulai',
        'price_note' => 'Belum termasuk PPN & BPHTB',
        'installment_label' => 'Cicilan mulai',
        'installment_note' => 'Simulasi KPR 20 tahun, DP 10%',
        'booking_fee_label' => 'Booking fee',
        'booking_fee_note' => 'Dapat dikembalikan*',
        'kpr_link_label' => 'Hitung simulasi KPR sendiri',
        'kpr_link_url' => '',
    ],

    'spec_labels' => [
        'land_area' => 'Luas tanah',
        'building_area' => 'Luas bangunan',
        'bedrooms' => 'Kamar tidur',
        'bathrooms' => 'Kamar mandi',
        'floors' => 'Lantai',
        'carports' => 'Carport',
        'lot' => 'Kavling',
    ],

    'form' => [
        // Tombol kartu marketing; pesan WhatsApp dari template di Pengaturan Umum.
        'whatsapp_button_label' => 'Minta info harga via WhatsApp',
        'survey_button_label' => 'Jadwalkan Survey',
        // Dipakai kalau cluster tidak punya marketing sendiri. Nomor WA: nomor cluster, kalau kosong nomor global.
        'marketing_name' => 'Tim Marketing BSD City',
        'marketing_title' => 'Marketing BSD City',
        'marketing_photo' => null,
    ],

    'others' => [
        // auto = utamakan cluster lain di kawasan yang sama.
        'source' => 'auto',
        'items' => [],
        'limit' => 3,
    ],

    'cta' => $shared['cta'],

    'seo' => [
        'title_pattern' => '{name}, {building_type}',
        'description_pattern' => '{summary}',
    ],
];
