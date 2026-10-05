<?php

namespace App\Settings;

/**
 * Halaman Kebijakan Privasi.
 */
class PrivacyPageSettings extends PageSettings
{
    public array $content;

    public array $seo;

    public static function group(): string
    {
        return 'page_privacy';
    }

    /**
     * Field yang bisa diubah admin; sisanya teks tetap di kode (lihat PageSettings::editable()).
     *
     * @return list<string>
     */
    public static function editable(): array
    {
        return [
            'content.effective_date',
            'content.body',
        ];
    }
}
