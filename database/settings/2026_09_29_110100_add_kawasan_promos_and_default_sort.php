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
        // Instalasi baru: sudah ditambahkan dari defaults oleh migrasi pembuatnya.
        if (! $this->migrator->exists('page_kawasan_detail.promos')) {
            $this->migrator->add('page_kawasan_detail.promos', (require PageSettings::defaultsPath('page_kawasan_detail'))['promos']);
        }

        $this->migrator->update('page_listing.cluster_view', function (array|object $view): array {
            $view = json_decode(json_encode($view), true);

            return [...$view, 'default_sort' => 'terbaru'];
        });
    }
};
