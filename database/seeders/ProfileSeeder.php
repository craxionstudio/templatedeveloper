<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\DeveloperProfile;
use Illuminate\Database\Seeder;

/**
 * Profil Lokasi & Profil Developer (masing-masing satu baris).
 */
class ProfileSeeder extends Seeder
{
    public function run(): void
    {
        Area::query()->updateOrCreate([], [
            'name' => 'BSD City',
            'location' => 'Serpong, Tangerang Selatan dan Kabupaten Tangerang, Banten',
            'description' => 'Kota mandiri di Serpong dengan beberapa kawasan hunian, fasilitas kawasan, dan akses transportasi yang terus berkembang.',
            'area_ha' => null, // [XX] ha — isi dengan data asli
            'hero_alt' => 'Foto aerial BSD City',
            'map_badge_label' => 'Akses tol langsung',
            'map_badge_value' => '± 5 menit',
            'advantages' => [
                ['icon' => 'route', 'title' => 'Akses tol langsung', 'description' => 'Gerbang tol [NAMA TOL] terhubung ke boulevard utama kawasan.', 'distance' => '± 5 menit'],
                ['icon' => 'train-front', 'title' => 'Dekat transportasi publik', 'description' => 'Stasiun KRL [NAMA] dan halte bus terintegrasi dengan shuttle kawasan.', 'distance' => ''],
                ['icon' => 'building-2', 'title' => 'Pusat bisnis & belanja', 'description' => 'Mall, perkantoran, dan kuliner dalam radius [X] km.', 'distance' => ''],
                ['icon' => 'graduation-cap', 'title' => 'Pendidikan & kesehatan', 'description' => 'Sekolah internasional dan rumah sakit tipe A di sekitar kawasan.', 'distance' => ''],
            ],
        ]);

        DeveloperProfile::query()->updateOrCreate([], [
            'headline' => 'Bukan sekadar membangun rumah, kami merancang kota.',
            'description' => 'BSD City adalah kota terencana seluas sekitar 6.000 hektare yang dikembangkan Sinar Mas Land. Di dalamnya ada puluhan kawasan hunian, dari kawasan awal seperti Giri Loka dan Nusa Loka sampai kawasan baru seperti Vireya, Terravia, dan The Armont, bersama sekolah, kampus, rumah sakit, pusat belanja, dan kawasan bisnis.',
            'history' => '<p>[TAMBAHKAN SEJARAH SINGKAT, GRUP USAHA INDUK, ATAU PENGHARGAAN YANG PERNAH DITERIMA.]</p>',
            'vision_quote' => '[KUTIPAN VISI / FILOSOFI DEVELOPER]',
            'stats' => [
                ['value' => '[XX]', 'label' => 'Tahun berkarya'],
                ['value' => '[XX] ha', 'label' => 'Total lahan dikembangkan'],
                ['value' => '[XX]', 'label' => 'Cluster terbangun'],
                ['value' => '[XX]rb', 'label' => 'Keluarga penghuni'],
            ],
            'photo_alt' => 'Kantor pemasaran BSD City',
            'secondary_photo_alt' => 'Tim pemasaran BSD City',
        ]);
    }
}
