<?php

namespace App\Support;

use App\Settings\GlobalSettings;

/**
 * Title halaman sesuai pola di Pengaturan Global → SEO default.
 */
class PageTitle
{
    public static function make(string $title): string
    {
        $global = app(GlobalSettings::class);

        return strtr($global->section('seo')['title_pattern'], [
            '{title}' => $title,
            '{brand}' => $global->section('identity')['brand_name'],
        ]);
    }

    public static function home(): string
    {
        $global = app(GlobalSettings::class);
        $identity = $global->section('identity');

        return strtr($global->section('seo')['home_title_pattern'], [
            '{brand}' => $identity['brand_name'],
            '{tagline}' => $identity['tagline'],
        ]);
    }
}
