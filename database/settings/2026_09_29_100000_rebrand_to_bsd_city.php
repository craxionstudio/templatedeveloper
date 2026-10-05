<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

/**
 * Ganti teks Arunika (data contoh) ke BSD City di database yang sudah berjalan.
 * Nilai hanya diganti kalau masih sama dengan teks lama, jadi teks yang sudah diubah admin aman.
 * Isi awal untuk instalasi baru ada di database/settings/defaults/.
 */
return new class extends SettingsMigration
{
    /** [grup.properti, key, teks lama, teks baru] */
    private const TEXTS = [
        ['global.identity', 'brand_name', 'Arunika Land', 'BSD City'],
        ['global.identity', 'tagline', 'Developer Properti', 'Kota mandiri Sinar Mas Land di Serpong'],
        ['global.contact', 'whatsapp_message', 'Halo, saya ingin info tentang properti Arunika Land.', 'Halo, saya ingin info tentang rumah di BSD City.'],
        ['global.footer', 'description', 'Developer kawasan hunian terpadu di Serpong, Tangerang Selatan.', 'Kota mandiri seluas sekitar 6.000 hektare yang dikembangkan Sinar Mas Land di Serpong, Tangerang.'],
        ['page_home.hero', 'eyebrow', 'Arunika Land · Sejak [TAHUN]', 'Serpong, Tangerang'],
        ['page_home.hero', 'title', 'Kota yang tumbuh bersama keluargamu.', 'Pilih rumah di kota seluas 6.000 hektare.'],
        ['page_home.hero', 'description', 'Kawasan hunian terpadu lengkap dengan sekolah, pusat belanja, taman, dan akses transportasi yang terus berkembang.', 'Lebih dari 20 kawasan hunian, dari cluster baru di Vireya dan Terravia sampai NavaPark. Bandingkan tipe dan harga, lalu atur jadwal survey.'],
        ['page_home.hero', 'image_alt', 'Foto aerial kota Arunika', 'Foto aerial BSD City'],
        ['page_home.hero', 'primary_label', 'Lihat Semua Listing', 'Lihat Semua Cluster'],
        ['page_home.listing', 'title', 'Temukan rumah di kawasan Arunika', 'Temukan rumah di BSD City'],
        ['page_home.listing', 'button_label', 'Lihat Semua Listing', 'Lihat Semua Cluster'],
        ['page_home.region', 'description', 'Setiap kawasan Arunika dipilih dekat dengan simpul transportasi dan pusat aktivitas, jadi waktu di jalan lebih singkat dan waktu bersama keluarga lebih banyak.', 'Kawasan-kawasan di BSD City terhubung ke tol Jakarta-Serpong, JORR, dan Commuter Line, jadi waktu di jalan lebih singkat dan waktu bersama keluarga lebih banyak.'],
        ['page_home.developments', 'description', 'Rencana pengembangan kawasan Arunika untuk beberapa tahun ke depan.', 'Rencana pengembangan BSD City untuk beberapa tahun ke depan.'],
        ['page_home.articles', 'title', 'Kabar terbaru dari Arunika', 'Kabar terbaru dari BSD City'],
        ['page_home.seo', 'meta_description', 'Kawasan hunian terpadu di Serpong lengkap dengan sekolah, pusat belanja, taman, dan akses transportasi yang terus berkembang.', 'Rumah di BSD City, dari cluster baru sampai kawasan mapan: Vireya, Terravia, Eonna, NavaPark, dan lainnya. Bandingkan tipe dan harga, lalu atur survey.'],
        ['page_listing.header', 'eyebrow', 'Serpong, Tangerang Selatan', 'Serpong, Tangerang'],
        ['page_listing.header', 'title', 'Properti Arunika', 'Properti BSD City'],
        ['page_listing.header', 'description', 'Pilih rumah berdasarkan cluster, atau jelajahi dulu kawasan-kawasan di dalam kota Arunika.', 'Pilih rumah berdasarkan cluster, atau jelajahi dulu kawasan-kawasan di BSD City.'],
        ['page_listing.header', 'image_alt', 'Foto aerial kota Arunika', 'Foto aerial BSD City'],
        ['page_listing.seo_cluster', 'meta_title', 'Properti di Kota Arunika', 'Daftar Cluster Rumah di BSD City'],
        ['page_listing.seo_cluster', 'meta_description', 'Pilih rumah berdasarkan cluster di kota Arunika, Serpong: bandingkan tipe, luas tanah, kamar tidur, dan harga.', 'Semua cluster rumah di BSD City dalam satu halaman. Saring per kawasan, tipe, jumlah kamar, dan harga. Info LT, LB, dan cicilan tiap tipe.'],
        ['page_listing.seo_kawasan', 'meta_title', 'Kawasan di Kota Arunika', 'Kawasan Hunian di BSD City'],
        ['page_listing.seo_kawasan', 'meta_description', 'Jelajahi kawasan-kawasan di kota Arunika, Serpong, beserta cluster di dalamnya dan cluster mandiri.', 'Kenali kawasan di BSD City sebelum memilih rumah: fasilitas, akses tol dan stasiun, dan cluster di tiap kawasan.'],
        ['page_kawasan_detail.hero', 'eyebrow', 'Kawasan di kota Arunika', 'Kawasan di BSD City'],
        ['page_cluster_detail.form', 'marketing_title', 'Marketing Arunika Land', 'Marketing BSD City'],
        ['page_facility.header', 'eyebrow', 'Fasilitas Developer', 'Fasilitas Kota'],
        ['page_facility.header', 'description', 'Fasilitas di kawasan Arunika dibangun dan dikelola langsung oleh developer, terbuka untuk seluruh penghuni dan terus bertambah seiring pengembangan kawasan.', 'Sekolah, kampus, rumah sakit, pusat belanja, dan ruang terbuka di BSD City, terus bertambah seiring pengembangan kota.'],
        ['page_facility.seo', 'meta_title', 'Fasilitas Kawasan', 'Fasilitas Kota BSD City'],
        ['page_facility.seo', 'meta_description', 'Fasilitas di kota Arunika dibangun dan dikelola langsung oleh developer: taman, clubhouse, sekolah, klinik, area komersial, dan keamanan 24 jam.', 'Sekolah, kampus, rumah sakit, mal, stasiun, dan akses tol di sekitar BSD City. Semua yang dekat dari rumah kamu nanti.'],
        ['page_article_index.header', 'title', 'Kabar & inspirasi dari Arunika', 'Kabar & inspirasi dari BSD City'],
        ['page_article_index.seo', 'meta_title', 'Artikel & Berita', 'Info & Tips Properti BSD City'],
        ['page_article_index.seo', 'meta_description', 'Berita kawasan Arunika, tips membeli rumah dan KPR, serta cerita keseharian para penghuni.', 'Kabar terbaru BSD City, tips KPR, dan panduan memilih cluster yang pas untuk keluarga.'],
        ['page_about.hero', 'image_alt', 'Foto kantor Arunika Land', 'Foto kantor pemasaran BSD City'],
        ['page_about.seo', 'meta_title', 'Tentang Kami', 'Tentang BSD City dan Sinar Mas Land'],
        ['page_contact.seo', 'meta_title', 'Kontak', 'Kantor Pemasaran BSD City'],
        ['page_contact.seo', 'meta_description', 'Hubungi kantor pemasaran Arunika Land: alamat, jam buka, WhatsApp, dan form konsultasi.', 'Tanya harga, tipe rumah, atau jadwal survey cluster di BSD City. Hubungi kantor pemasaran lewat WhatsApp atau isi form ini.'],
    ];

    /** Section Beranda yang dimatikan (datanya masih contoh dan tidak dipublikasikan). */
    private const HOME_SECTIONS_OFF = ['facilities', 'developments', 'articles'];

    public function up(): void
    {
        foreach (collect(self::TEXTS)->groupBy(0) as $property => $rows) {
            // Settings yang sudah dihapus belakangan (mis. halaman Kontak) dilewati kalau migrasi dijalankan ulang.
            if (! $this->migrator->exists($property)) {
                continue;
            }

            $this->migrator->update($property, function (array|object $value) use ($rows): array {
                // Nilai tersimpan di-decode sebagai objek; ubah ke array dulu.
                $value = json_decode(json_encode($value), true);

                foreach ($rows as [, $key, $old, $new]) {
                    if (($value[$key] ?? null) === $old || ! array_key_exists($key, $value)) {
                        $value[$key] = $new;
                    }
                }

                return $value;
            });
        }

        foreach (self::HOME_SECTIONS_OFF as $section) {
            $this->migrator->update('page_home.'.$section, function (array|object $value): array {
                return [...json_decode(json_encode($value), true), 'enabled' => false];
            });
        }
    }
};
