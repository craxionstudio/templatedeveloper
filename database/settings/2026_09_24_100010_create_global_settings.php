<?php

use App\Settings\PageSettings;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        foreach (require PageSettings::defaultsPath('global') as $key => $value) {
            $this->migrator->add('global.'.$key, $value);
        }
    }
};
