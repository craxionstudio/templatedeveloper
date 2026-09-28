<?php

use App\Support\DummyContent;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Data yang sudah berjalan: teks Arunika di Profil Developer, Profil Lokasi, dan kategori artikel
 * diganti BSD City (hanya kalau masih teks contoh), lalu konten contoh (fasilitas, pengembangan
 * mendatang, artikel, promo) dinonaktifkan. Pasangan settings-nya: database/settings/2026_09_29_100000_rebrand_to_bsd_city.php.
 */
return new class extends Migration
{
    /** [tabel, kolom, teks lama, teks baru] */
    private const TEXTS = [
        ['developer_profiles', 'description', 'Arunika Land adalah pengembang kawasan hunian terpadu yang berdiri sejak [TAHUN]. Kami merencanakan setiap kawasan dari nol: jaringan jalan, ruang hijau, fasilitas pendidikan dan kesehatan, sampai pusat komersial, supaya penghuni tidak perlu jauh-jauh untuk kebutuhan sehari-hari.', 'BSD City adalah kota terencana seluas sekitar 6.000 hektare yang dikembangkan Sinar Mas Land. Di dalamnya ada puluhan kawasan hunian, dari kawasan awal seperti Giri Loka dan Nusa Loka sampai kawasan baru seperti Vireya, Terravia, dan The Armont, bersama sekolah, kampus, rumah sakit, pusat belanja, dan kawasan bisnis.'],
        ['developer_profiles', 'photo_alt', 'Kantor pemasaran Arunika Land', 'Kantor pemasaran BSD City'],
        ['developer_profiles', 'secondary_photo_alt', 'Tim Arunika Land', 'Tim pemasaran BSD City'],
        ['areas', 'name', 'Kota Arunika', 'BSD City'],
        ['areas', 'hero_alt', 'Foto aerial kota Arunika', 'Foto aerial BSD City'],
        ['article_categories', 'description', 'Kabar terbaru pembangunan dan kegiatan di kota Arunika.', 'Kabar terbaru pembangunan dan kegiatan di BSD City.'],
        ['article_categories', 'description', 'Cerita keseharian dan agenda warga kota Arunika.', 'Cerita keseharian dan agenda warga BSD City.'],
    ];

    public function up(): void
    {
        foreach (self::TEXTS as [$table, $column, $old, $new]) {
            DB::table($table)->where($column, $old)->update([$column => $new]);
        }

        DummyContent::unpublish();
    }
};
