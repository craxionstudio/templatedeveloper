<?php

namespace App\Support;

use App\Models\Kawasan;
use App\Settings\GlobalSettings;
use App\Settings\NavigationSettings;
use Illuminate\Database\Eloquent\Builder;
use Throwable;

/**
 * Menyusun data layout global (header, drawer mobile, footer) yang dibagikan ke semua
 * halaman Inertia. Sumber: Pengaturan Global, Menu Navigasi, dan kawasan yang dipublikasikan.
 */
class SiteLayout
{
    /**
     * Penanda link WhatsApp umum di data layout yang di-cache. Diganti per request dengan link wa.me
     * berisi {judul_halaman}/{link_halaman} halaman yang sedang dibuka (lihat data()).
     */
    private const WHATSAPP = '__whatsapp_url__';

    /**
     * @return array<string, mixed>
     */
    public static function data(): array
    {
        // Sama untuk semua pengunjung; di-cache per versi konten (dibuang saat konten/settings berubah).
        // Cache gagal (izin file, store mati) = susun langsung, bukan error 500.
        try {
            $layout = PageCache::store()->remember(
                // "v2": format dengan penanda link WhatsApp; cache format lama tidak dipakai lagi.
                'site-layout:v2:'.PageCache::store()->get(PageCache::VERSION_KEY, 0),
                3600,
                fn (): array => self::build(),
            );
        } catch (Throwable) {
            $layout = self::build();
        }

        // Dipanggil saat respons dirender (shared prop lazy), jadi judul halaman sudah diketahui.
        $whatsappUrl = WhatsApp::url();
        array_walk_recursive($layout, function (mixed &$value) use ($whatsappUrl): void {
            if ($value === self::WHATSAPP) {
                $value = $whatsappUrl;
            }
        });

        return $layout;
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

        $whatsappUrl = self::WHATSAPP;
        // Halaman Kontak dihapus: menu "Kontak" (header, drawer, footer) langsung membuka WhatsApp di tab baru.
        $contactLink = ['label' => 'Kontak', 'url' => $whatsappUrl, 'new_tab' => true, 'position' => 'menu_kontak'];

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
            // Info kontak di footer semua halaman (pengganti halaman Kontak). Teks contoh [..] / kosong tidak tampil.
            'footerContact' => self::footerContact($contact),
            'header' => [
                'showHotline' => (bool) $header['show_hotline'] && self::telUrl($contact['hotline']) !== null,
                'ctaLabel' => $header['cta_label'],
                'ctaUrl' => $header['cta_url'] ?: $whatsappUrl,
            ],
            'mobile' => [
                'showWhatsappIcon' => (bool) $global->section('mobile')['show_whatsapp_icon'],
            ],
            'navigation' => [
                ...collect(app(NavigationSettings::class)->header_items)->reject(fn (array $item): bool => self::isContactPage($item['url'] ?? ''))->values()->all(),
                $contactLink,
            ],
            'footer' => [
                'description' => $footer['description'],
                'columns' => [self::propertyColumn($footer), ...array_map(fn (array $column): array => [
                    ...$column,
                    'links' => array_map(fn (array $link): array => self::isContactPage($link['url'] ?? '') ? [...$contactLink, 'label' => $link['label']] : $link, $column['links'] ?? []),
                ], $footer['columns'])],
                'socialTitle' => $footer['social_title'],
                'social' => collect($footer['social'] ?? [])->filter(fn ($item): bool => is_array($item) && filled($item['url'] ?? null) && $item['url'] !== '#')->values()->all(),
                'officeTitle' => $footer['office_title'],
                'copyright' => str_replace(['{year}', '{company}'], [(string) now()->year, Content::filled($identity['company_name']) ? $identity['company_name'] : $identity['brand_name']], $footer['copyright']),
                'disclaimer' => $footer['disclaimer'],
            ],
            'labels' => $global->section('labels'),
            'tracking' => Tracking::browser(),
        ];
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
     * @param  array<string, mixed>  $contact  section "contact" Pengaturan Umum
     * @return array<string, array{value: string, url: ?string}|null>
     */
    private static function footerContact(array $contact): array
    {
        $filled = fn (string $key): ?string => Content::filled($contact[$key] ?? null) ? trim((string) $contact[$key]) : null;
        $whatsapp = $filled('whatsapp');
        $phone = $filled('phone');
        $email = $filled('email');
        $maps = $filled('maps_url');

        if (! $maps && is_numeric($contact['latitude'] ?? null) && is_numeric($contact['longitude'] ?? null)) {
            $maps = 'https://www.google.com/maps/search/?api=1&query='.$contact['latitude'].','.$contact['longitude'];
        }

        return [
            'address' => ($address = $filled('office_address')) ? ['value' => $address, 'url' => null] : null,
            'phone' => $phone ? ['value' => $phone, 'url' => self::telUrl($phone)] : null,
            'whatsapp' => $whatsapp ? ['value' => self::displayPhone($whatsapp), 'url' => self::WHATSAPP] : null,
            'email' => $email ? ['value' => $email, 'url' => filter_var($email, FILTER_VALIDATE_EMAIL) ? 'mailto:'.$email : null] : null,
            'hours' => ($hours = $filled('opening_hours')) ? ['value' => $hours, 'url' => null] : null,
            'maps' => $maps && filter_var($maps, FILTER_VALIDATE_URL) ? ['value' => 'Lihat di Google Maps', 'url' => $maps] : null,
        ];
    }

    /**
     * 6281234567890 → +62 812-3456-7890 (untuk dibaca; link tetap wa.me).
     */
    public static function displayPhone(string $number): string
    {
        $digits = preg_replace('/\D+/', '', $number);

        if (str_starts_with($digits, '0')) {
            $digits = '62'.substr($digits, 1);
        }

        if (! str_starts_with($digits, '62') || strlen($digits) < 10) {
            return $number;
        }

        $local = substr($digits, 2);

        return '+62 '.implode('-', array_filter([substr($local, 0, 3), substr($local, 3, 4), substr($local, 7)], 'strlen'));
    }

    private static function isContactPage(string $url): bool
    {
        return (bool) preg_match('#^(https?://[^/]+)?/kontak/?(\?.*|\#.*)?$#', trim($url));
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
