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
}
