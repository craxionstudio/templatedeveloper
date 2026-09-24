<?php

namespace App\Settings;

/**
 * Halaman Kontak.
 */
class ContactPageSettings extends PageSettings
{
    public array $header;

    public array $info;

    public array $map;

    public array $form;

    public array $cta;

    public array $seo;

    public static function group(): string
    {
        return 'page_contact';
    }
}
