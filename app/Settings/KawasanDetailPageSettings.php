<?php

namespace App\Settings;

/**
 * Template Detail Kawasan.
 */
class KawasanDetailPageSettings extends PageSettings
{
    public array $hero;

    public array $about;

    public array $facilities;

    public array $clusters;

    public array $promos;

    public array $others;

    public array $cta;

    public array $seo;

    public static function group(): string
    {
        return 'page_kawasan_detail';
    }

    /**
     * Field yang bisa diubah admin; sisanya teks tetap di kode (lihat PageSettings::editable()).
     *
     * @return list<string>
     */
    public static function editable(): array
    {
        return [];
    }
}
