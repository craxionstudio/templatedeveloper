<?php

namespace App\Presenters;

use Spatie\MediaLibrary\HasMedia;

/**
 * Gambar untuk React: {url, alt} atau {url: null, alt} (frontend menampilkan placeholder
 * bergaris dengan label = alt text selama foto belum diunggah).
 */
class Image
{
    /**
     * @return array{url: string|null, alt: string}
     */
    public static function media(?HasMedia $model, string $collection, ?string $alt, string $fallbackAlt = ''): array
    {
        $url = $model?->getFirstMediaUrl($collection);

        return ['url' => $url ?: null, 'alt' => (string) ($alt ?: $fallbackAlt)];
    }

    /**
     * Gambar dari settings halaman (path di disk public).
     *
     * @return array{url: string|null, alt: string}
     */
    public static function path(?string $path, ?string $alt, string $fallbackAlt = ''): array
    {
        return ['url' => $path ? asset('storage/'.ltrim($path, '/')) : null, 'alt' => (string) ($alt ?: $fallbackAlt)];
    }
}
