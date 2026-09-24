<?php

namespace App\Settings;

/**
 * Halaman Tentang Kami.
 */
class AboutPageSettings extends PageSettings
{
    public array $hero;

    public array $history;

    public array $vision;

    public array $stats;

    public array $timeline;

    public array $team;

    public array $awards;

    public array $cta;

    public array $seo;

    public static function group(): string
    {
        return 'page_about';
    }
}
