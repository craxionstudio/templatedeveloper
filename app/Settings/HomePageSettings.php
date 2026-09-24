<?php

namespace App\Settings;

/**
 * Halaman Beranda.
 */
class HomePageSettings extends PageSettings
{
    public array $hero;

    public array $about;

    public array $promo;

    public array $listing;

    public array $region;

    public array $facilities;

    public array $developments;

    public array $articles;

    public array $cta;

    public array $seo;

    public static function group(): string
    {
        return 'page_home';
    }
}
