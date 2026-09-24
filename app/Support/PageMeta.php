<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Meta dasar halaman (title, description, robots). Canonical, OG, dan JSON-LD lengkap
 * dikerjakan di Milestone 5.
 */
class PageMeta
{
    /**
     * @param  array<string, mixed>  $seo  field SEO dari settings / seo_meta (boleh kosong)
     * @return array{title: string, description: string|null, noindex: bool}
     */
    public static function make(string $title, ?string $description = null, array $seo = [], bool $noindex = false, bool $isHome = false): array
    {
        $metaTitle = filled($seo['meta_title'] ?? null)
            ? (string) $seo['meta_title']
            : ($isHome ? PageTitle::home() : PageTitle::make($title));

        return [
            'title' => $metaTitle,
            'description' => self::description(filled($seo['meta_description'] ?? null) ? $seo['meta_description'] : $description),
            'noindex' => $noindex || (bool) ($seo['noindex'] ?? false),
        ];
    }

    /**
     * Potong ±155 karakter di batas kata, tanpa tag HTML.
     */
    public static function description(?string $text): ?string
    {
        $plain = trim(preg_replace('/\s+/', ' ', strip_tags((string) $text)));

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
