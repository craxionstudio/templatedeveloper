<?php

namespace App\Presenters;

use App\Support\ResponsiveImages;
use Spatie\MediaLibrary\HasMedia;

/**
 * Gambar untuk React: {url, alt, width, height, sources} atau {url: null, alt} (frontend
 * menampilkan placeholder bergaris dengan label = alt text selama foto belum diunggah).
 * `sources` = varian AVIF/WebP responsif (srcset) yang sudah dibuat.
 */
class Image
{
    /**
     * @return array{url: string|null, alt: string, width?: ?int, height?: ?int, sources?: list<array{type: string, srcset: string}>}
     */
    public static function media(?HasMedia $model, string $collection, ?string $alt, string $fallbackAlt = ''): array
    {
        $media = $model?->getFirstMedia($collection);
        $alt = (string) ($alt ?: $fallbackAlt);

        if (! $media) {
            return ['url' => null, 'alt' => $alt];
        }

        return ['url' => $media->getUrl(), 'alt' => $alt, ...ResponsiveImages::forMedia($media)];
    }

    /**
     * Gambar dari settings halaman (path di disk public).
     *
     * @return array{url: string|null, alt: string, width?: ?int, height?: ?int, sources?: list<array{type: string, srcset: string}>}
     */
    public static function path(?string $path, ?string $alt, string $fallbackAlt = ''): array
    {
        $alt = (string) ($alt ?: $fallbackAlt);

        if (! $path) {
            return ['url' => null, 'alt' => $alt];
        }

        return ['url' => asset('storage/'.ltrim($path, '/')), 'alt' => $alt, ...ResponsiveImages::forPath($path)];
    }
}
