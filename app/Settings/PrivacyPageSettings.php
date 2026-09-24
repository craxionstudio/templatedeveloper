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
}
