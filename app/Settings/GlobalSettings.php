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
            'footer.social',
            'tracking.ga4_id',
            'tracking.google_verification',
            // Sampai Tahap C (dihapus bersama form lead & Meta): tidak tampil di form, nilai lama tetap dipakai.
            'tracking.gtm_id',
            'tracking.meta_pixel_id',
            'tracking.meta_capi_pixel_id',
            'tracking.meta_capi_token',
            'tracking.meta_test_event_code',
            'tracking.turnstile_site_key',
            'tracking.turnstile_secret_key',
            'tracking.csp_extra',
            'tracking.bing_verification',
            'notifications.emails',
            'seo.default_og_image',
        ];
    }
}
