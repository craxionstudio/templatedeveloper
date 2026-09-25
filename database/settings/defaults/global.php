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
        // Tombol kunjungan membuka form singkat (modal); visit_url tetap jadi link cadangan tanpa JavaScript.
        'modal_enabled' => true,
        'modal_title' => 'Jadwalkan kunjungan',
        'modal_description' => 'Tinggalkan nama dan nomor WhatsApp, tim marketing kami akan menghubungi kamu untuk mengatur jadwal.',
        'modal_submit_label' => 'Kirim',
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
        // Rahasia disimpan terenkripsi (App\Support\Secret) dan tidak pernah dikirim ke browser.
        'turnstile_secret_key' => '',
        // Kosong = pakai meta_pixel_id (Pixel yang dipasang langsung).
        'meta_capi_pixel_id' => '',
        'meta_capi_token' => '',
        // Opsional: kode "Test events" dari Meta Events Manager untuk uji coba CAPI.
        'meta_test_event_code' => '',
    ],

    'notifications' => [
        // Satu atau lebih email penerima notifikasi lead.
        'emails' => [],
        // Kosong = webhook tidak dikirim.
        'webhook_url' => '',
    ],

    'labels' => [
        'skip_to_content' => 'Langsung ke konten utama',
        'address' => 'Alamat',
        'facility_list' => 'Daftar fasilitas',
        'kawasan_list' => 'Daftar kawasan',
        'article_list' => 'Daftar artikel',
        'preview_notice' => 'Pratinjau: hanya terlihat oleh admin dan tidak diindeks mesin pencari.',
        'form_name' => 'Nama',
        'form_name_placeholder' => 'Nama lengkap',
        'form_whatsapp' => 'WhatsApp',
        'form_whatsapp_placeholder' => '08xx xxxx xxxx',
        'form_email' => 'Email',
        'form_consent' => 'Saya setuju data saya diproses sesuai Kebijakan Privasi.',
        'form_submit' => 'Kirim',
        'form_sending' => 'Mengirim…',
        'form_error' => 'Periksa kembali isian yang ditandai.',
        'newsletter_success' => 'Terima kasih! Email kamu sudah terdaftar.',
        'privacy_policy' => 'Kebijakan Privasi',
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
        'pagination' => 'Halaman',
        'cluster_prefix' => 'Cluster',
        'type_prefix' => 'Tipe',
        'land_area_short' => 'LT',
        'bedrooms_short' => 'KT',
        'area_unit' => 'm²',
        'floors_unit' => 'lantai',
        'carport_unit' => 'mobil',
        'unit' => 'unit',
        'minutes' => 'menit',
        'gallery_all' => 'Lihat {count} foto',
        'gallery_photo' => 'Foto',
        'gallery_video' => 'Video',
        'gallery_tour' => 'Virtual tour 360°',
        'gallery_floorplan' => 'Denah',
        'previous_photo' => 'Foto sebelumnya',
        'next_photo' => 'Foto berikutnya',
        'close' => 'Tutup',
        'back' => 'Kembali',
        'open_map' => 'Buka peta',
        'filter' => 'Filter',
        'search' => 'Cari',
        'previous_slide' => 'Promo sebelumnya',
        'next_slide' => 'Promo berikutnya',
        'slide' => 'Slide',
        'send' => 'Kirim',
        'listing_view' => 'Tampilan listing',
    ],

    // Halaman 404 (ter-render SSR, status 404 asli).
    'not_found' => [
        'eyebrow' => '404',
        'title' => 'Halaman tidak ditemukan',
        'message' => 'Halaman yang kamu cari mungkin sudah dipindah atau tidak tersedia lagi. Coba mulai dari daftar properti atau artikel terbaru.',
        'links' => [
            ['label' => 'Lihat semua properti', 'url' => '/properti'],
            ['label' => 'Baca artikel', 'url' => '/artikel'],
            ['label' => 'Kembali ke Beranda', 'url' => '/'],
        ],
    ],

    'seo' => [
        'title_pattern' => '{title} | {brand}',
        'home_title_pattern' => '{brand} — {tagline}',
        'default_og_image' => null,
        'same_as' => [],
    ],
];
