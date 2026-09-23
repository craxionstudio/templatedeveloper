<?php

/*
|--------------------------------------------------------------------------
| Isi awal layout global (header, footer, drawer mobile)
|--------------------------------------------------------------------------
|
| Milestone 1: data layout global dibaca dari file ini. Di Milestone 2 semua
| nilai di sini dipindah ke GlobalSettings + Menu Navigasi (spatie/laravel-settings)
| dan file ini hanya dipakai sebagai isi awal seeder/fallback.
|
| Teks dalam kurung siku [...] = data dummy yang WAJIB diganti dengan data asli.
|
*/

return [

    'brand' => [
        'name' => env('APP_NAME', 'Arunika Land'),
        'tagline' => 'Developer Properti',
        'company' => '[NAMA PT DEVELOPER]',
    ],

    'contact' => [
        // Teks yang tampil di header. Link tel: dibuat dari digit di dalamnya
        // (tidak ada link selama masih placeholder).
        'hotline' => '[NO. HOTLINE]',
        // Format 62xxxxxxxxxx. Kosong = tombol WA diarahkan ke halaman kontak.
        'whatsapp' => '',
        'whatsapp_message' => 'Halo, saya ingin info tentang properti Arunika Land.',
        'phone' => '[NO. TELEPON]',
        'email' => '[EMAIL]',
        'office_address' => '[ALAMAT KANTOR PEMASARAN]',
        'opening_hours' => 'Setiap hari, 09.00–17.00',
        'fallback_url' => '/kontak',
    ],

    'header' => [
        'show_hotline' => true,
        'cta_label' => 'Hubungi Marketing',
        // Kosong = pakai link WhatsApp.
        'cta_url' => '',
    ],

    'mobile' => [
        'show_whatsapp_icon' => true,
    ],

    // Menu header & drawer mobile.
    'navigation' => [
        ['label' => 'Beranda', 'url' => '/'],
        ['label' => 'Properti', 'url' => '/properti'],
        ['label' => 'Fasilitas', 'url' => '/fasilitas'],
        ['label' => 'Artikel', 'url' => '/artikel'],
        ['label' => 'Tentang Kami', 'url' => '/tentang-kami'],
    ],

    'footer' => [
        'description' => 'Developer kawasan hunian terpadu di [Serpong, Tangerang Selatan].',
        'columns' => [
            [
                'title' => 'Properti',
                'links' => [
                    ['label' => 'Semua listing', 'url' => '/properti'],
                    ['label' => 'Vega Garden', 'url' => '/properti/vega-garden'],
                    ['label' => 'Lyra Residence', 'url' => '/properti/lyra-residence'],
                    ['label' => 'Orion Park', 'url' => '/properti/orion-park'],
                ],
            ],
            [
                'title' => 'Perusahaan',
                'links' => [
                    ['label' => 'Tentang Kami', 'url' => '/tentang-kami'],
                    ['label' => 'Fasilitas', 'url' => '/fasilitas'],
                    ['label' => 'Artikel', 'url' => '/artikel'],
                    ['label' => 'Kontak', 'url' => '/kontak'],
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

    // Label kecil (aksesibilitas & tombol) supaya tidak ada teks hardcode di React.
    'labels' => [
        'skip_to_content' => 'Langsung ke konten utama',
        'main_menu' => 'Menu utama',
        'open_menu' => 'Buka menu',
        'close_menu' => 'Tutup menu',
        'chat_whatsapp' => 'Chat WhatsApp',
        'call_hotline' => 'Telepon hotline',
        'home' => 'Beranda',
    ],

];
