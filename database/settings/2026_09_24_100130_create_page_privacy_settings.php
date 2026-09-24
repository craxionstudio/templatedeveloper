<?php

use App\Settings\PageSettings;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        foreach (require PageSettings::defaultsPath('page_privacy') as $key => $value) {
            $this->migrator->add('page_privacy.'.$key, $value);
        }
    }
};
