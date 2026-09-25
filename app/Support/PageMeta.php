<?php

namespace App\Support;

use App\Settings\GlobalSettings;
use Illuminate\Support\Str;

/**
 * Meta lengkap halaman (brief 8.2 & 8.3): title, description, robots, canonical absolut,
 * Open Graph/Twitter, dan JSON-LD. Dirender oleh resources/js/components/site/page-head.tsx
 * lewat <Head> Inertia sehingga ikut di HTML SSR.
 */
class PageMeta
{
    /**
     * OG image default per tipe halaman (public/og/{section}.png, 1200×630).
     */
    public const OG_SECTIONS = ['home', 'properti', 'kawasan', 'rumah', 'fasilitas', 'artikel', 'tentang', 'kontak', 'default'];

    /**
     * @param  array<string, mixed>  $seo  field SEO dari settings / seo_meta (boleh kosong)
     * @param  string|null  $image  gambar konten (URL absolut) untuk og:image
     * @param  string  $section  tipe halaman untuk OG image default
     * @param  list<array{label: string, url: ?string}>  $breadcrumbs  → JSON-LD BreadcrumbList
     * @param  list<array<string, mixed>|null>  $schema  JSON-LD tambahan khusus halaman
     * @param  array{publishedTime?: ?string, modifiedTime?: ?string, section?: ?string}  $article
     * @return array<string, mixed>
     */
    public static function make(
        string $title,
        ?string $description = null,
        array $seo = [],
        bool $noindex = false,
        bool $isHome = false,
        ?string $image = null,
        string $section = 'default',
        array $breadcrumbs = [],
        array $schema = [],
        string $ogType = 'website',
        array $article = [],
    ): array {
        $metaTitle = filled($seo['meta_title'] ?? null)
            ? (string) $seo['meta_title']
            : ($isHome ? PageTitle::home() : PageTitle::make($title));
        $metaDescription = self::description(filled($seo['meta_description'] ?? null) ? $seo['meta_description'] : $description);
        $noindex = $noindex || (bool) ($seo['noindex'] ?? false);
        $canonical = self::canonical($seo['canonical_url'] ?? null);
        $og = self::image($seo['og_image'] ?? null, $image, $section);
        $global = app(GlobalSettings::class);

        return [
            'title' => $metaTitle,
            'description' => $metaDescription,
            'noindex' => $noindex,
            // Non-production selalu noindex, nofollow (brief 8.2).
            'robots' => match (true) {
                ! app()->isProduction() => 'noindex, nofollow',
                $noindex => 'noindex, follow',
                default => 'index, follow, max-image-preview:large',
            },
            'canonical' => $canonical,
            'og' => [
                'type' => $ogType,
                'title' => $metaTitle,
                'description' => $metaDescription,
                'url' => $canonical,
                'siteName' => $global->section('identity')['brand_name'],
                'locale' => 'id_ID',
                ...$og,
            ],
            'article' => $ogType === 'article' ? array_filter($article) : null,
            'jsonLd' => array_values(array_filter([
                StructuredData::organization(),
                StructuredData::website(),
                $isHome ? null : StructuredData::breadcrumbs($breadcrumbs, $canonical),
                ...$schema,
            ])),
        ];
    }

    /**
     * Canonical absolut tanpa query (filter, urut, pencarian, ?tipe=), kecuali pagination:
     * ?page=2 dst. menunjuk ke dirinya sendiri. Override dari tab SEO kalau diisi.
     */
    public static function canonical(?string $override = null): string
    {
        if (filled($override)) {
            return StructuredData::url((string) $override);
        }

        $request = request();
        $path = '/'.ltrim($request->path(), '/');
        $page = (int) $request->query('page');

        return StructuredData::url($path).($page > 1 ? '?page='.$page : '');
    }

    /**
     * og:image: gambar SEO halaman > gambar konten > OG default admin > OG default per tipe halaman.
     *
     * @return array{image: string, imageWidth: ?int, imageHeight: ?int}
     */
    public static function image(?string $seoImage, ?string $contentImage, string $section): array
    {
        $default = app(GlobalSettings::class)->section('seo')['default_og_image'] ?? null;

        if (filled($seoImage) || filled($contentImage) || filled($default)) {
            $url = filled($seoImage) ? asset('storage/'.ltrim((string) $seoImage, '/'))
                : (filled($contentImage) ? (string) $contentImage : asset('storage/'.ltrim((string) $default, '/')));

            return ['image' => $url, 'imageWidth' => null, 'imageHeight' => null];
        }

        $section = in_array($section, self::OG_SECTIONS, true) && is_file(public_path("og/{$section}.png")) ? $section : 'default';

        return ['image' => StructuredData::url("/og/{$section}.png"), 'imageWidth' => 1200, 'imageHeight' => 630];
    }

    /**
     * Potong ±155 karakter di batas kata, tanpa tag HTML.
     */
    public static function description(?string $text): ?string
    {
        $plain = trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags((string) $text), ENT_QUOTES | ENT_HTML5)));

        if ($plain === '') {
            return null;
        }

        return mb_strlen($plain) <= 155 ? $plain : rtrim(Str::of($plain)->limit(155, '', preserveWords: true)->toString(), ' ,.;:').'…';
    }

    /**
     * Ganti placeholder {key} di pola settings.
     *
     * @param  array<string, string|int|null>  $values
     */
    public static function fill(string $pattern, array $values): string
    {
        return trim(strtr($pattern, collect($values)->mapWithKeys(fn ($v, $k) => ['{'.$k.'}' => (string) $v])->all()), ' ,-—|');
    }
}
