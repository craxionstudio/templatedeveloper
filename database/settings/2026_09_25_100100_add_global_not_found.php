<?php

use App\Settings\PageSettings;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

/**
 * Milestone 3: teks halaman 404 di Pengaturan Global.
 */
return new class extends SettingsMigration
{
    public function up(): void
    {
        if ($this->migrator->exists('global.not_found')) {
            return;
        }

        $this->migrator->add('global.not_found', (require PageSettings::defaultsPath('global'))['not_found']);
    }
};
