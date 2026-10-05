<?php

namespace App\Settings;

/**
 * Pengaturan global (identitas, kontak & WhatsApp, header, footer, CTA, GA4, label, SEO default).
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

    public array $labels;

    public array $not_found;

    public array $seo;

    public static function group(): string
    {
        return 'global';
    }

    /**
     * Field yang bisa diubah admin; sisanya teks tetap di kode (lihat PageSettings::editable()).
     *
     * @return list<string>
     */
    public static function editable(): array
    {
        return [
            'identity.company_name',
            'identity.logo_light',
            'identity.logo_dark',
            'identity.favicon',
            'contact.whatsapp',
            'contact.whatsapp_message',
            'contact.hotline',
            'contact.phone',
            'contact.email',
            'contact.office_address',
            'contact.opening_hours',
            'contact.latitude',
            'contact.longitude',
            'contact.maps_url',
            'footer.social',
            'tracking.ga4_id',
            'tracking.google_verification',
            // Tidak tampil di admin; nilai lama (kalau ada) tetap dipasang sebagai meta tag.
            'tracking.bing_verification',
            'contact.whatsapp_cluster_message',
            'contact.whatsapp_promo_message',
            'contact.whatsapp_survey_message',
            'contact.whatsapp_kawasan_message',
            'seo.default_og_image',
        ];
    }
}
