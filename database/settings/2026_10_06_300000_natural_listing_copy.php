<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

/**
 * Update 3 (6 Okt 2026): bahasa natural untuk pembeli rumah, tanpa jumlah cluster/kawasan.
 * - Judul /properti jadi "Temukan Rumah Anda di BSD City" (permintaan pemilik; tetap bisa diubah di admin).
 * - Meta /properti dan deskripsi hero Beranda yang masih teks lama (menyebut "semua cluster" / jumlah kawasan)
 *   diganti; teks yang sudah diubah admin tidak disentuh.
 */
return new class extends SettingsMigration
{
    private const LISTING_TITLE = 'Temukan Rumah Anda di BSD City';

    private const OLD_LISTING_META_DESCRIPTION = 'Semua cluster rumah di BSD City dalam satu halaman. Saring per kawasan, tipe, jumlah kamar, dan harga. Info LT, LB, dan cicilan tiap tipe.';

    private const OLD_HOME_DESCRIPTION = 'Lebih dari 20 kawasan hunian, dari cluster baru di Vireya dan Terravia sampai NavaPark. Bandingkan tipe dan harga, lalu atur jadwal survey.';

    public function up(): void
    {
        $this->patch('page_listing.header', fn (array $header): array => [...$header, 'title' => self::LISTING_TITLE]);

        $this->patch('page_listing.seo_cluster', function (array $seo): array {
            if (in_array($seo['meta_title'] ?? '', ['', 'Daftar Cluster Rumah di BSD City', 'Properti BSD City'], true)) {
                $seo['meta_title'] = self::LISTING_TITLE;
            }

            if (($seo['meta_description'] ?? '') === self::OLD_LISTING_META_DESCRIPTION) {
                $seo['meta_description'] = (require database_path('settings/defaults/page_listing.php'))['seo_cluster']['meta_description'];
            }

            return $seo;
        });

        $this->patch('page_home.hero', function (array $hero): array {
            if (($hero['description'] ?? '') === self::OLD_HOME_DESCRIPTION) {
                $hero['description'] = (require database_path('settings/defaults/page_home.php'))['hero']['description'];
            }

            return $hero;
        });
    }

    /**
     * @param  callable(array<string, mixed>): array<string, mixed>  $callback
     */
    private function patch(string $property, callable $callback): void
    {
        if ($this->migrator->exists($property)) {
            $this->migrator->update($property, fn (array|object $value): array => $callback(json_decode(json_encode($value), true)));
        }
    }
};
