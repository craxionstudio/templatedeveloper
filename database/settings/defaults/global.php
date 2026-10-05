<?php

/*
| Isi awal Pengaturan Global. Teks dalam [...] = data dummy yang wajib diganti.
*/

return [
    'identity' => [
        'brand_name' => 'BSD City',
        'company_name' => '[NAMA PT DEVELOPER]',
        'tagline' => 'Kota mandiri Sinar Mas Land di Serpong',
        'logo_light' => null,
        'logo_dark' => null,
        'favicon' => null,
    ],

    'contact' => [
        'hotline' => '[NO. HOTLINE]',
        // Format 62xxxxxxxxxx, wajib diisi di Pengaturan Umum. Satu nomor untuk semua tombol WhatsApp.
        'whatsapp' => '',
        // Template pesan WhatsApp per konteks. Placeholder: {nama_cluster}, {nama_kawasan}, {judul_halaman},
        // {link_halaman} (lihat App\Support\WhatsApp). "\n" = baris baru (%0A di link wa.me).
        'whatsapp_message' => "Halo, saya ingin konsultasi rumah di BSD City.\n{link_halaman}",
        'whatsapp_cluster_message' => "Halo, saya tertarik dengan {nama_cluster}. Boleh minta info harga & brosurnya?\n{link_halaman}",
        'whatsapp_promo_message' => "Halo, saya tertarik dengan promo di {nama_cluster}. Boleh minta informasi lengkapnya?\n{link_halaman}",
        'whatsapp_survey_message' => "Halo, saya ingin jadwalkan survey ke {nama_cluster}.\n{link_halaman}",
        'whatsapp_kawasan_message' => "Halo, saya ingin tahu cluster di kawasan {nama_kawasan}.\n{link_halaman}",
        'phone' => '[NO. TELEPON]',
        'email' => '[EMAIL]',
        'office_address' => '[ALAMAT KANTOR PEMASARAN]',
        'latitude' => null,
        'longitude' => null,
        // Link Google Maps kantor pemasaran di footer. Kosong = dari latitude/longitude, kalau ada.
        'maps_url' => '',
        'opening_hours' => 'Setiap hari, 09.00–17.00',
    ],

    'header' => [
        'cta_label' => 'Hubungi Marketing',
        // Kosong = pakai link WhatsApp.
        'cta_url' => '',
        'show_hotline' => true,
    ],

    'footer' => [
        'description' => 'Kota mandiri seluas sekitar 6.000 hektare yang dikembangkan Sinar Mas Land di Serpong, Tangerang.',
        // Kolom Properti otomatis berisi kawasan yang dipublikasikan (maksimal property_limit,
        // kawasan dari cluster Prioritas 1–10 dulu), ditutup link "Semua kawasan".
        'property_title' => 'Properti',
        'property_limit' => 8,
        'property_all_label' => 'Semua kawasan',
        'property_all_url' => '/properti/kawasan',
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
        // {year} = tahun berjalan, {company} = nama perusahaan (Pengaturan Umum), atau nama brand kalau kosong.
        'copyright' => '© {year} {company}. Hak cipta dilindungi.',
        'disclaimer' => 'Gambar, harga, dan spesifikasi bersifat ilustrasi dan dapat berubah sewaktu-waktu tanpa pemberitahuan.',
    ],

    'cta' => [
        'eyebrow' => 'Konsultasi gratis',
        'title' => 'Bingung pilih cluster? Ngobrol dulu dengan tim marketing kami.',
        'description' => 'Kami bantu hitung cicilan, cek unit yang masih tersedia, dan atur jadwal kunjungan ke rumah contoh.',
        'whatsapp_label' => 'Chat via WhatsApp',
        // Tombol kedua: chat WhatsApp dengan template "jadwal survey".
        'visit_label' => 'Jadwalkan Survey',
    ],

    'mobile' => [
        'show_whatsapp_icon' => true,
        'sticky_price_label' => 'Mulai',
        'sticky_whatsapp_label' => 'Chat WhatsApp',
        'sticky_survey_label' => 'Jadwalkan Survey',
    ],

    'tracking' => [
        // GA4 via gtag.js. Kosong = tidak ada script tracking yang dimuat.
        'ga4_id' => '',
        'google_verification' => '',
        'bing_verification' => '',
    ],

    'labels' => [
        'skip_to_content' => 'Langsung ke konten utama',
        'address' => 'Alamat',
        // Kartu & Detail Rumah untuk cluster yang belum punya harga.
        'price_on_request' => 'Hubungi kami untuk harga',
        'benefits' => 'Promo & benefit',
        'facility_list' => 'Daftar fasilitas',
        'kawasan_list' => 'Daftar kawasan',
        'article_list' => 'Daftar artikel',
        'preview_notice' => 'Pratinjau: hanya terlihat oleh admin dan tidak diindeks mesin pencari.',
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
