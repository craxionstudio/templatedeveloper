<?php

$shared = require __DIR__.'/_shared.php';

return [
    'content' => [
        'title' => 'Kebijakan Privasi',
        'effective_date' => null,
        'effective_label' => 'Berlaku sejak',
        // [...] = draf struktur, isi final wajib ditinjau bagian legal (UU PDP).
        'body' => '<p>[ISI KEBIJAKAN PRIVASI — WAJIB DITINJAU BAGIAN LEGAL SEBELUM DIPUBLIKASIKAN]</p>'
            .'<h2>Data yang kami kumpulkan</h2><p>[Nama, nomor WhatsApp, email, minat cluster, dan data teknis kunjungan seperti sumber iklan.]</p>'
            .'<h2>Tujuan penggunaan</h2><p>[Menghubungi kamu terkait informasi properti yang diminta.]</p>'
            .'<h2>Penyimpanan &amp; keamanan</h2><p>[Lama penyimpanan dan cara data dilindungi.]</p>'
            .'<h2>Hak kamu</h2><p>[Hak akses, koreksi, dan penghapusan data sesuai UU PDP, beserta kontak yang bisa dihubungi.]</p>',
    ],

    'seo' => [
        ...$shared['seo'],
        'meta_title' => 'Kebijakan Privasi',
    ],
];
