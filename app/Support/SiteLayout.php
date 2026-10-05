<?php

namespace App\Support;

use App\Models\Kawasan;
use App\Settings\GlobalSettings;
use App\Settings\NavigationSettings;
use Illuminate\Database\Eloquent\Builder;

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
                'showHotline' => (bool) $header['show_hotline'] && self::telUrl($contact['hotline']) !== null,
                'ctaLabel' => $header['cta_label'],
                'ctaUrl' => $header['cta_url'] ?: $whatsappUrl,
            ],
            'mobile' => [
                'showWhatsappIcon' => (bool) $global->section('mobile')['show_whatsapp_icon'],
            ],
            'navigation' => array_values(app(NavigationSettings::class)->header_items),
            'footer' => [
                'description' => $footer['description'],
                'columns' => [self::propertyColumn($footer), ...$footer['columns']],
                'socialTitle' => $footer['social_title'],
                'social' => collect($footer['social'] ?? [])->filter(fn ($item): bool => is_array($item) && filled($item['url'] ?? null) && $item['url'] !== '#')->values()->all(),
                'officeTitle' => $footer['office_title'],
                'copyright' => str_replace(['{year}', '{company}'], [(string) now()->year, Content::filled($identity['company_name']) ? $identity['company_name'] : $identity['brand_name']], $footer['copyright']),
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
    /**
     * Kolom Properti di footer: maksimal N kawasan (Pengaturan Global → Footer). Urutan: kawasan yang
     * punya cluster Prioritas 1–10 dulu (prioritas terkecil di atas), lalu urutan kawasan.
     * Ditutup link "Semua kawasan".
     *
     * @param  array<string, mixed>  $footer
     * @return array<string, mixed>
     */
    public static function propertyColumn(array $footer): array
    {
        $limit = max(1, (int) ($footer['property_limit'] ?? 8));

        $kawasans = Kawasan::query()->visible()
            ->withMin(['clusters as top_priority' => fn (Builder $q) => $q->published()->whereNotNull('prioritas')], 'prioritas')
            ->orderByRaw('CASE WHEN top_priority IS NULL THEN 1 ELSE 0 END')
            ->orderBy('top_priority')
            ->ordered()
            ->limit($limit)
            ->get(['id', 'name', 'slug', 'sort_order']);

        $links = $kawasans->map(fn (Kawasan $kawasan): array => ['label' => $kawasan->name, 'url' => $kawasan->publicPath()]);

        if (filled($footer['property_all_label'] ?? null)) {
            $links->push(['label' => $footer['property_all_label'], 'url' => $footer['property_all_url'] ?: '/properti/kawasan']);
        }

        return ['title' => $footer['property_title'], 'links' => $links->values()->all()];
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
