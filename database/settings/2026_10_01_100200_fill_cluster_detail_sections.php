<?php

use App\Settings\PageSettings;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

/**
 * Form settings di admin diisi dari nilai tersimpan apa adanya: section baru di defaults
 * (sections.benefits, sections.facilities) yang belum tersimpan akan tampil dengan toggle mati dan
 * ikut tersimpan mati. Lengkapi key yang belum ada dari defaults (nilai yang sudah ada tidak diubah).
 */
return new class extends SettingsMigration
{
    public function up(): void
    {
        $defaults = (require PageSettings::defaultsPath('page_cluster_detail'))['sections'];

        $this->migrator->update('page_cluster_detail.sections', function (array|object $sections) use ($defaults): array {
            return array_replace_recursive($defaults, json_decode(json_encode($sections), true));
        });
    }
};
