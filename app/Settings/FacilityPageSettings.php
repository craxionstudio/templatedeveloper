<?php

namespace App\Settings;

/**
 * Halaman Fasilitas.
 */
class FacilityPageSettings extends PageSettings
{
    public array $header;

    public array $list;

    public array $cta;

    public array $seo;

    public static function group(): string
    {
        return 'page_facility';
    }
}
