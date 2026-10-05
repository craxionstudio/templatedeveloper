<?php

namespace App\Settings;

/**
 * Halaman Terima Kasih (selalu noindex).
 */
class ThankYouPageSettings extends PageSettings
{
    public array $content;

    public array $seo;

    public static function group(): string
    {
        return 'page_thank_you';
    }

    /**
     * Field yang bisa diubah admin; sisanya teks tetap di kode (lihat PageSettings::editable()).
     *
     * @return list<string>
     */
    public static function editable(): array
    {
        return [
            'content.title',
            'content.message',
        ];
    }
}
