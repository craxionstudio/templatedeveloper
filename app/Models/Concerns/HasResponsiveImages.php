<?php

namespace App\Models\Concerns;

use App\Support\ResponsiveImages;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Konversi WebP + AVIF (AVIF hanya kalau didukung server, lihat ResponsiveImages::formats()) (480/960/1600 px, tidak diperbesar) untuk koleksi gambar model.
 * Model menentukan koleksinya lewat responsiveImageCollections().
 */
trait HasResponsiveImages
{
    /**
     * @return list<string>
     */
    abstract public function responsiveImageCollections(): array;

    public function registerMediaConversions(?Media $media = null): void
    {
        foreach (ResponsiveImages::WIDTHS as $width) {
            foreach (ResponsiveImages::formats() as $format) {
                $this->addMediaConversion("{$width}-{$format}")
                    ->fit(Fit::Max, $width, $width * 4)
                    ->format($format)
                    ->quality(ResponsiveImages::QUALITY[$format])
                    ->performOnCollections(...$this->responsiveImageCollections());
            }
        }
    }
}
