<?php

namespace App\Settings;

/**
 * Pengaturan global (identitas, kontak, header, footer, CTA, tracking, label, SEO default).
 */
class GlobalSettings extends PageSettings
{
    public array $identity;

    public array $contact;

    public array $header;

    public array $footer;

    public array $cta;

    public array $mobile;

    public array $tracking;

    public array $notifications;

    public array $labels;

    public array $not_found;

    public array $seo;

    public static function group(): string
    {
        return 'global';
    }
}
