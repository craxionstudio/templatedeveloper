<?php

use App\Settings\PageSettings;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

/**
 * Milestone 4: penerima notifikasi lead (email) dan webhook opsional.
 */
return new class extends SettingsMigration
{
    public function up(): void
    {
        if ($this->migrator->exists('global.notifications')) {
            return;
        }

        $this->migrator->add('global.notifications', (require PageSettings::defaultsPath('global'))['notifications']);
    }
};
