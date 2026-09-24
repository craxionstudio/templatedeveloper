<?php

use App\Settings\PageSettings;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        foreach (require PageSettings::defaultsPath('page_thank_you') as $key => $value) {
            $this->migrator->add('page_thank_you.'.$key, $value);
        }
    }
};
