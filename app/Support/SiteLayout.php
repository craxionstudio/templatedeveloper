<?php

namespace App\Support;

/**
 * Menyusun data layout global (header, drawer mobile, footer) yang dibagikan
 * ke semua halaman Inertia.
 *
 * Sumber data saat ini config/site.php; di Milestone 2 diganti GlobalSettings
 * + Menu Navigasi tanpa mengubah bentuk array yang dikirim ke React.
 */
class SiteLayout
{
    /**
     * @return array<string, mixed>
     */
    public static function data(): array
    {
        $site = config('site');
        $contact = $site['contact'];

        $whatsappUrl = self::whatsappUrl($contact['whatsapp'], $contact['whatsapp_message'])
            ?? $contact['fallback_url'];

        return [
            'brand' => $site['brand'],
            'contact' => [
                'hotline' => $contact['hotline'],
                'hotlineUrl' => self::telUrl($contact['hotline']),
                'whatsappUrl' => $whatsappUrl,
                'phone' => $contact['phone'],
                'email' => $contact['email'],
                'officeAddress' => $contact['office_address'],
                'openingHours' => $contact['opening_hours'],
            ],
            'header' => [
                'showHotline' => (bool) $site['header']['show_hotline'],
                'ctaLabel' => $site['header']['cta_label'],
                'ctaUrl' => $site['header']['cta_url'] ?: $whatsappUrl,
            ],
            'mobile' => [
                'showWhatsappIcon' => (bool) $site['mobile']['show_whatsapp_icon'],
            ],
            'navigation' => $site['navigation'],
            'footer' => [
                'description' => $site['footer']['description'],
                'columns' => $site['footer']['columns'],
                'socialTitle' => $site['footer']['social_title'],
                'social' => $site['footer']['social'],
                'officeTitle' => $site['footer']['office_title'],
                'copyright' => str_replace('{year}', (string) now()->year, $site['footer']['copyright']),
                'disclaimer' => $site['footer']['disclaimer'],
            ],
            'labels' => $site['labels'],
        ];
    }

    /**
     * Nomor WA Indonesia dinormalisasi ke 62…; null bila nomor belum diisi.
     */
    public static function whatsappUrl(?string $number, ?string $message = null): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $number);

        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '0')) {
            $digits = '62'.substr($digits, 1);
        }

        $url = 'https://wa.me/'.$digits;

        return filled($message) ? $url.'?text='.rawurlencode($message) : $url;
    }

    /**
     * Link tel: dari teks hotline; null selama hotline masih placeholder.
     */
    public static function telUrl(?string $number): ?string
    {
        $value = preg_replace('/[^\d+]+/', '', (string) $number);

        return preg_match('/\d{6,}/', $value) ? 'tel:'.$value : null;
    }
}
