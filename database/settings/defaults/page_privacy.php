<?php

$shared = require __DIR__.'/_shared.php';

return [
    'content' => [
        'title' => 'Kebijakan Privasi',
        'effective_date' => null,
        'effective_label' => 'Berlaku sejak',
        // Draf berdasarkan cara kerja situs (tanpa form, tanpa Meta): wajib ditinjau bagian legal (UU PDP).
        'body' => '<p>Kebijakan ini menjelaskan data apa yang kami proses saat kamu mengunjungi situs ini dan menghubungi kami. [WAJIB DITINJAU BAGIAN LEGAL SEBELUM DIPUBLIKASIKAN]</p>'
            .'<h2>Data yang kami proses</h2>'
            .'<p>Situs ini tidak memiliki formulir pendaftaran atau formulir kontak. Kalau kamu menekan tombol WhatsApp, percakapan berlangsung di aplikasi WhatsApp: nomor dan isi pesan yang kamu kirim diterima tim marketing kami untuk menjawab pertanyaanmu.</p>'
            .'<p>Kami memakai Google Analytics 4 untuk memahami cara situs dipakai, misalnya halaman yang dibuka, perangkat dan browser, perkiraan lokasi tingkat kota, sumber kunjungan, dan klik tombol WhatsApp. Google Analytics memakai cookie dan tidak menerima nama atau nomor teleponmu dari situs ini.</p>'
            .'<h2>Tujuan penggunaan</h2>'
            .'<p>Menjawab pertanyaan tentang properti yang kamu minati, mengatur jadwal survey, dan memperbaiki isi serta kinerja situs.</p>'
            .'<h2>Penyimpanan &amp; keamanan</h2>'
            .'<p>Percakapan WhatsApp disimpan oleh tim marketing selama masih diperlukan untuk melayani kamu. Data Google Analytics disimpan sesuai pengaturan retensi akun Google Analytics kami.</p>'
            .'<h2>Hak kamu</h2>'
            .'<p>Kamu bisa meminta akses, koreksi, atau penghapusan data sesuai UU Pelindungan Data Pribadi dengan menghubungi kami lewat WhatsApp atau email di bagian bawah situs. Kamu juga bisa menolak cookie analitik lewat pengaturan browser atau add-on penonaktifan Google Analytics.</p>',
    ],

    'seo' => [
        ...$shared['seo'],
        'meta_title' => 'Kebijakan Privasi',
    ],
];
