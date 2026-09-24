<?php

use App\Settings\PageSettings;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        foreach (require PageSettings::defaultsPath('navigation') as $key => $value) {
            $this->migrator->add('navigation.'.$key, $value);
        }
    }
};
