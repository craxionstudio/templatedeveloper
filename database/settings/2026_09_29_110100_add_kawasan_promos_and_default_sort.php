<?php

use App\Settings\PageSettings;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

/**
 * Update 2: section "Promo di kawasan ini" di Detail Kawasan, dan "Terbaru" (tanggal launching)
 * sebagai urutan default /properti.
 */
return new class extends SettingsMigration
{
    public function up(): void
    {
        // Section promo kawasan sudah dihapus lagi (2026_10_06_400000_remove_old_promo_settings): instalasi baru
        // tidak lagi menambahkannya. Database lama tetap ditambahkan supaya urutan migrasi tetap sama.
        $promos = (require PageSettings::defaultsPath('page_kawasan_detail'))['promos'] ?? ['enabled' => false];

        if (! $this->migrator->exists('page_kawasan_detail.promos')) {
            $this->migrator->add('page_kawasan_detail.promos', $promos);
        }

        $this->migrator->update('page_listing.cluster_view', function (array|object $view): array {
            $view = json_decode(json_encode($view), true);

            return [...$view, 'default_sort' => 'terbaru'];
        });
    }
};
