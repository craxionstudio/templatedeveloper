<?php

/*
| Isi awal Pengaturan Global. Teks dalam [...] = data dummy yang wajib diganti.
*/

return [
    'identity' => [
        'brand_name' => 'Arunika Land',
        'company_name' => '[NAMA PT DEVELOPER]',
        'tagline' => 'Developer Properti',
        'logo_light' => null,
        'logo_dark' => null,
        'favicon' => null,
    ],

    'contact' => [
        'hotline' => '[NO. HOTLINE]',
        // Format 62xxxxxxxxxx. Kosong = tombol WA diarahkan ke halaman kontak.
        'whatsapp' => '',
        'whatsapp_message' => 'Halo, saya ingin info tentang properti Arunika Land.',
        'phone' => '[NO. TELEPON]',
        'email' => '[EMAIL]',
        'office_address' => '[ALAMAT KANTOR PEMASARAN]',
        'latitude' => null,
        'longitude' => null,
        'opening_hours' => 'Setiap hari, 09.00–17.00',
    ],

    'header' => [
        'cta_label' => 'Hubungi Marketing',
        // Kosong = pakai link WhatsApp.
        'cta_url' => '',
        'show_hotline' => true,
    ],

    'footer' => [
        'description' => 'Developer kawasan hunian terpadu di Serpong, Tangerang Selatan.',
        // Kolom Properti otomatis berisi kawasan yang dipublikasikan.
        'property_title' => 'Properti',
        'columns' => [
            [
                'title' => 'Perusahaan',
                'links' => [
                    ['label' => 'Tentang Kami', 'url' => '/tentang-kami', 'new_tab' => false],
                    ['label' => 'Fasilitas', 'url' => '/fasilitas', 'new_tab' => false],
                    ['label' => 'Artikel', 'url' => '/artikel', 'new_tab' => false],
                    ['label' => 'Kontak', 'url' => '/kontak', 'new_tab' => false],
                ],
            ],
        ],
        'social_title' => 'Ikuti Kami',
        'social' => [
            ['label' => 'Instagram', 'url' => '#'],
            ['label' => 'TikTok', 'url' => '#'],
            ['label' => 'YouTube', 'url' => '#'],
            ['label' => 'Facebook', 'url' => '#'],
        ],
        'office_title' => 'Kantor Pemasaran',
        // {year} diganti tahun berjalan.
        'copyright' => '© {year} [NAMA PT DEVELOPER]. Hak cipta dilindungi.',
        'disclaimer' => 'Gambar, harga, dan spesifikasi bersifat ilustrasi dan dapat berubah sewaktu-waktu tanpa pemberitahuan.',
    ],

    'cta' => [
        'eyebrow' => 'Konsultasi gratis',
        'title' => 'Bingung pilih cluster? Ngobrol dulu dengan tim marketing kami.',
        'description' => 'Kami bantu hitung cicilan, cek unit yang masih tersedia, dan atur jadwal kunjungan ke rumah contoh.',
        'whatsapp_label' => 'Chat via WhatsApp',
        'visit_label' => 'Jadwalkan Kunjungan',
        'visit_url' => '/kontak',
    ],

    'mobile' => [
        'show_whatsapp_icon' => true,
        'sticky_price_label' => 'Mulai',
        'sticky_whatsapp_label' => 'Chat WhatsApp',
        'sticky_survey_label' => 'Jadwalkan Survey',
    ],

    'tracking' => [
        'gtm_id' => '',
        'ga4_id' => '',
        'meta_pixel_id' => '',
        'google_verification' => '',
        'bing_verification' => '',
        'turnstile_site_key' => '',
    ],

    'labels' => [
        'skip_to_content' => 'Langsung ke konten utama',
        'main_menu' => 'Menu utama',
        'open_menu' => 'Buka menu',
        'close_menu' => 'Tutup menu',
        'chat_whatsapp' => 'Chat WhatsApp',
        'call_hotline' => 'Telepon hotline',
        'home' => 'Beranda',
        'breadcrumb' => 'Breadcrumb',
        'load_more' => 'Muat lagi',
        'see_all' => 'Lihat semua',
        'view_floorplan' => 'Lihat denah',
        'read_article' => 'Baca artikel',
        'share' => 'Bagikan',
        'download_brochure' => 'Unduh Brosur',
        'download_pricelist' => 'Unduh Pricelist',
        'kawasan' => 'Kawasan',
        'standalone_cluster' => 'Cluster mandiri',
        'house_types' => 'tipe rumah',
        'clusters' => 'cluster',
        'price' => 'Harga',
        'price_from' => 'Harga mulai',
        'installment_from' => 'Cicilan mulai',
        'per_month' => '/bln',
        'reading_time' => 'menit baca',
        'previous_page' => 'Halaman sebelumnya',
        'next_page' => 'Halaman berikutnya',
    ],

    'seo' => [
        'title_pattern' => '{title} | {brand}',
        'home_title_pattern' => '{brand} — {tagline}',
        'default_og_image' => null,
        'same_as' => [],
    ],
];
