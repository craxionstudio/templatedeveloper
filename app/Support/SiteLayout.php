<?php

namespace App\Support;

use App\Models\Kawasan;
use App\Settings\GlobalSettings;
use App\Settings\NavigationSettings;

/**
 * Menyusun data layout global (header, drawer mobile, footer) yang dibagikan ke semua
 * halaman Inertia. Sumber: Pengaturan Global, Menu Navigasi, dan kawasan yang dipublikasikan.
 */
class SiteLayout
{
    /**
     * @return array<string, mixed>
     */
    public static function data(): array
    {
        // Sama untuk semua pengunjung; di-cache per versi konten (dibuang saat konten/settings berubah).
        return PageCache::store()->remember(
            'site-layout:'.PageCache::store()->get(PageCache::VERSION_KEY, 0),
            3600,
            fn (): array => self::build(),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private static function build(): array
    {
        $global = app(GlobalSettings::class);
        $identity = $global->section('identity');
        $contact = $global->section('contact');
        $header = $global->section('header');
        $footer = $global->section('footer');

        $whatsappUrl = self::whatsappUrl($contact['whatsapp'], $contact['whatsapp_message']) ?? '/kontak';

        return [
            'brand' => [
                'name' => $identity['brand_name'],
                'tagline' => $identity['tagline'],
                'company' => $identity['company_name'],
            ],
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
                'showHotline' => (bool) $header['show_hotline'],
                'ctaLabel' => $header['cta_label'],
                'ctaUrl' => $header['cta_url'] ?: $whatsappUrl,
            ],
            'mobile' => [
                'showWhatsappIcon' => (bool) $global->section('mobile')['show_whatsapp_icon'],
            ],
            'navigation' => array_values(app(NavigationSettings::class)->header_items),
            'footer' => [
                'description' => $footer['description'],
                'columns' => [self::propertyColumn($footer['property_title']), ...$footer['columns']],
                'socialTitle' => $footer['social_title'],
                'social' => $footer['social'],
                'officeTitle' => $footer['office_title'],
                'copyright' => str_replace('{year}', (string) now()->year, $footer['copyright']),
                'disclaimer' => $footer['disclaimer'],
            ],
            'labels' => $global->section('labels'),
            'tracking' => Tracking::browser(),
            'leadModal' => self::leadModal($global->section('cta')),
        ];
    }

    /**
     * Form lead singkat di modal (tombol "Jadwalkan Kunjungan/Survey"); null = dimatikan.
     *
     * @param  array<string, mixed>  $cta
     * @return array{title: string, description: string, submitLabel: string}|null
     */
    public static function leadModal(array $cta): ?array
    {
        return $cta['modal_enabled'] ? [
            'title' => $cta['modal_title'],
            'description' => $cta['modal_description'],
            'submitLabel' => $cta['modal_submit_label'],
        ] : null;
    }

    /**
     * Kolom Properti di footer: otomatis berisi kawasan yang tampil di publik
     * (dipublikasikan dan punya minimal 1 cluster yang dipublikasikan).
     *
     * @return array{title: string, links: list<array{label: string, url: string}>}
     */
    public static function propertyColumn(string $title): array
    {
        return [
            'title' => $title,
            'links' => Kawasan::query()->visible()->ordered()->get(['name', 'slug'])
                ->map(fn (Kawasan $kawasan): array => ['label' => $kawasan->name, 'url' => $kawasan->publicPath()])
                ->values()
                ->all(),
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
