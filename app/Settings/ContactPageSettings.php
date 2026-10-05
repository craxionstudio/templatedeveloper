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

    public array $cta;

    public array $seo;

    public static function group(): string
    {
        return 'page_contact';
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
            'map.embed_url',
            'seo.meta_title',
            'seo.meta_description',
        ];
    }
}
