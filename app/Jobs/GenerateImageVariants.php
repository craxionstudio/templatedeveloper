<?php

namespace App\Jobs;

use App\Support\ResponsiveImages;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Varian WebP/AVIF untuk gambar yang diunggah lewat settings halaman.
 */
class GenerateImageVariants implements ShouldQueue
{
    use Queueable;

    /**
     * @param  list<string>  $paths
     */
    public function __construct(public array $paths) {}

    public function handle(): void
    {
        foreach ($this->paths as $path) {
            ResponsiveImages::generateForPath($path);
        }
    }

    /**
     * Semua path gambar di payload settings (rekursif).
     *
     * @param  array<mixed>  $values
     * @return list<string>
     */
    public static function pathsIn(array $values): array
    {
        $paths = [];

        array_walk_recursive($values, function (mixed $value) use (&$paths): void {
            if (is_string($value) && ResponsiveImages::isImage($value) && ! str_contains($value, '://')) {
                $paths[] = ltrim($value, '/');
            }
        });

        return array_values(array_unique($paths));
    }
}
