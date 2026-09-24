<?php

namespace App\Support;

/**
 * Ikon yang bisa dipilih admin (nama ikon lucide). Frontend memetakan key ini ke komponen ikon.
 */
class IconOptions
{
    /**
     * @return array<string, string>
     */
    public static function all(): array
    {
        return [
            'sprout' => 'Taman / hijau',
            'trees' => 'Pepohonan',
            'waves' => 'Danau / air',
            'house' => 'Rumah / clubhouse',
            'shield' => 'Keamanan',
            'route' => 'Jalan tol / akses',
            'train-front' => 'Kereta / stasiun',
            'bus' => 'Bus / shuttle',
            'building-2' => 'Gedung / bisnis',
            'store' => 'Toko / komersial',
            'shopping-cart' => 'Belanja',
            'graduation-cap' => 'Pendidikan',
            'stethoscope' => 'Kesehatan',
            'dumbbell' => 'Olahraga',
            'church' => 'Ibadah',
            'tag' => 'Diskon / harga',
            'gift' => 'Hadiah / gratis',
            'zap' => 'Listrik',
            'check' => 'Centang',
            'map-pin' => 'Lokasi',
        ];
    }
}
