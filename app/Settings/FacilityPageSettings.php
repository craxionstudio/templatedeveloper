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

    /**
     * Field yang bisa diubah admin; sisanya teks tetap di kode (lihat PageSettings::editable()).
     *
     * @return list<string>
     */
    public static function editable(): array
    {
        return [
            'header.title',
            'header.description',
            'seo.meta_title',
            'seo.meta_description',
        ];
    }
}
