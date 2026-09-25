<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Spatie\Image\Enums\Fit;
use Spatie\Image\Image as SpatieImage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Throwable;

/**
 * Gambar responsif (brief 8.6): varian WebP + AVIF di beberapa lebar, tidak pernah diperbesar.
 * - Media library: konversi "{lebar}-{format}" (lihat Concerns\HasResponsiveImages), lewat queue.
 * - Gambar dari settings halaman (FileUpload di disk public): varian di `_variants/…`, dibuat oleh
 *   job GenerateImageVariants setelah settings disimpan (atau `php artisan images:variants`).
 * Frontend hanya menerima varian yang sudah jadi; selama belum jadi, gambar asli yang dipakai.
 */
class ResponsiveImages
{
    public const WIDTHS = [480, 960, 1600];

    /**
     * AVIF dulu (paling kecil), lalu WebP. Browser memilih yang pertama didukung.
     */
    public const FORMATS = ['avif' => 'image/avif', 'webp' => 'image/webp'];

    public const QUALITY = ['avif' => 55, 'webp' => 78];

    public const VARIANT_DIR = '_variants';

    /**
     * @return array{width: ?int, height: ?int, sources: list<array{type: string, srcset: string}>}
     */
    public static function forMedia(Media $media): array
    {
        [$width, $height] = self::mediaDimensions($media);
        $sources = [];

        foreach (self::FORMATS as $format => $type) {
            $srcset = collect(self::WIDTHS)
                ->filter(fn (int $w) => $media->hasGeneratedConversion("{$w}-{$format}"))
                ->mapWithKeys(fn (int $w) => [min($w, $width ?: $w) => $media->getUrl("{$w}-{$format}")])
                ->map(fn (string $url, int $w) => "{$url} {$w}w")
                ->values();

            if ($srcset->isNotEmpty()) {
                $sources[] = ['type' => $type, 'srcset' => $srcset->implode(', ')];
            }
        }

        return ['width' => $width, 'height' => $height, 'sources' => $sources];
    }

    /**
     * @return array{width: ?int, height: ?int, sources: list<array{type: string, srcset: string}>}
     */
    public static function forPath(string $path): array
    {
        $disk = Storage::disk('public');
        $path = ltrim($path, '/');

        if (! $disk->exists($path)) {
            return ['width' => null, 'height' => null, 'sources' => []];
        }

        [$width, $height] = self::fileDimensions($disk->path($path));
        $sources = [];

        foreach (self::FORMATS as $format => $type) {
            $srcset = collect(self::WIDTHS)
                ->filter(fn (int $w) => $disk->exists(self::variantPath($path, $w, $format)))
                ->mapWithKeys(fn (int $w) => [min($w, $width ?: $w) => $disk->url(self::variantPath($path, $w, $format))])
                ->map(fn (string $url, int $w) => "{$url} {$w}w")
                ->values();

            if ($srcset->isNotEmpty()) {
                $sources[] = ['type' => $type, 'srcset' => $srcset->implode(', ')];
            }
        }

        return ['width' => $width, 'height' => $height, 'sources' => $sources];
    }

    public static function variantPath(string $path, int $width, string $format): string
    {
        $info = pathinfo(ltrim($path, '/'));
        $dir = ($info['dirname'] ?? '.') === '.' ? '' : $info['dirname'].'/';

        return self::VARIANT_DIR.'/'.$dir.$info['filename'].'-'.$width.'.'.$format;
    }

    /**
     * Buat varian untuk gambar di disk public (dipanggil dari job, bukan saat request).
     */
    public static function generateForPath(string $path): void
    {
        $disk = Storage::disk('public');
        $path = ltrim($path, '/');

        if (! self::isImage($path) || ! $disk->exists($path)) {
            return;
        }

        foreach (self::WIDTHS as $width) {
            foreach (array_keys(self::FORMATS) as $format) {
                $target = self::variantPath($path, $width, $format);

                if ($disk->exists($target) && $disk->lastModified($target) >= $disk->lastModified($path)) {
                    continue;
                }

                $disk->makeDirectory(dirname($target));
                SpatieImage::load($disk->path($path))
                    ->fit(Fit::Max, $width, $width * 4)
                    ->quality(self::QUALITY[$format])
                    ->format($format)
                    ->save($disk->path($target));
            }
        }
    }

    public static function isImage(string $path): bool
    {
        return (bool) preg_match('/\.(jpe?g|png|webp|avif)$/i', $path) && ! str_starts_with(ltrim($path, '/'), self::VARIANT_DIR.'/');
    }

    /**
     * @return array{0: ?int, 1: ?int}
     */
    private static function mediaDimensions(Media $media): array
    {
        if ($media->hasCustomProperty('width')) {
            return [(int) $media->getCustomProperty('width'), (int) $media->getCustomProperty('height')];
        }

        [$width, $height] = self::fileDimensions($media->getPath());

        if ($width) {
            // Disimpan sekali supaya tidak membaca file di setiap request.
            $media->setCustomProperty('width', $width)->setCustomProperty('height', $height)->saveQuietly();
        }

        return [$width, $height];
    }

    /**
     * @return array{0: ?int, 1: ?int}
     */
    private static function fileDimensions(string $file): array
    {
        if (! is_file($file)) {
            return [null, null];
        }

        return Cache::rememberForever('img-dim:'.md5($file.'|'.filemtime($file)), function () use ($file): array {
            try {
                $size = getimagesize($file);
            } catch (Throwable) {
                $size = false;
            }

            return $size ? [(int) $size[0], (int) $size[1]] : [null, null];
        });
    }
}
