<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

/**
 * Sistem promo lama dihapus (6 Okt 2026): settings banner promo Beranda, section "Promo di kawasan ini",
 * dan section promo lama Detail Rumah dibuang. "Promo & Benefit" (Bank Benefit) tidak disentuh.
 */
return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->deleteIfExists('page_home.promo');
        $this->migrator->deleteIfExists('page_kawasan_detail.promos');

        if ($this->migrator->exists('page_cluster_detail.sections')) {
            $this->migrator->update('page_cluster_detail.sections', function (array|object $sections): array {
                $sections = json_decode(json_encode($sections), true);
                unset($sections['promo']);

                return $sections;
            });
        }
    }
};
