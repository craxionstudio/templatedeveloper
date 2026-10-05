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

    /**
     * Field yang bisa diubah admin; sisanya teks tetap di kode (lihat PageSettings::editable()).
     *
     * @return list<string>
     */
    public static function editable(): array
    {
        return [
            'hero.eyebrow',
            'hero.title',
            'hero.description',
            'hero.image',
            'listing.title',
            'region.title',
            'region.description',
            'facilities.title',
            'developments.title',
            'developments.description',
            'articles.title',
            'seo.meta_title',
            'seo.meta_description',
        ];
    }
}
