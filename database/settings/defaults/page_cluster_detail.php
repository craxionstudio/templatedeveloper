<?php

$shared = require __DIR__.'/_shared.php';

return [
    'sections' => [
        // Sistem promo lama (tabel promos), disembunyikan dari website sejak Bank Benefit.
        'promo' => ['enabled' => true, 'title' => 'Promo rumah ini', 'period_prefix' => 'Berlaku s.d.'],
        // Bank Benefit: benefit yang dicentang di cluster. Tidak tampil kalau cluster tanpa benefit.
        // {cluster} di whatsapp_message diganti nama cluster.
        'benefits' => [
            'enabled' => true,
            'title' => 'Promo & Benefit',
            'disclaimer' => '*Syarat dan ketentuan berlaku dan dapat berubah sewaktu-waktu.',
            'button_label' => 'Dapatkan informasi lengkapnya via WhatsApp',
            'whatsapp_message' => 'Halo, saya tertarik dengan promo di {cluster}. Boleh minta informasi lengkapnya?',
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
        'name_label' => 'Nama',
        'name_placeholder' => 'Nama lengkap',
        'whatsapp_label' => 'WhatsApp',
        'whatsapp_placeholder' => '08xx xxxx xxxx',
        'submit_label' => 'Minta Pricelist',
        'whatsapp_button_label' => 'WhatsApp',
        'survey_button_label' => 'Survey',
        // {cluster} dan {type} diganti otomatis.
        'whatsapp_message' => 'Halo, saya tertarik dengan {cluster} tipe {type}. Boleh minta info harga dan unit yang tersedia?',
        // Dipakai kalau cluster tidak punya marketing sendiri.
        'marketing_name' => '[NAMA MARKETING]',
        'marketing_title' => 'Marketing BSD City',
        'marketing_whatsapp' => '',
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
