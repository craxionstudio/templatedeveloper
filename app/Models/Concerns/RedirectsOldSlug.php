<?php

namespace App\Models\Concerns;

use App\Models\Redirect;

/**
 * Saat slug konten berubah, buat redirect 301 otomatis dari URL lama ke URL baru.
 * Model wajib punya method publicPath(string $slug): string.
 */
trait RedirectsOldSlug
{
    public static function bootRedirectsOldSlug(): void
    {
        static::updated(function (self $model): void {
            if (! $model->wasChanged('slug')) {
                return;
            }

            $from = $model->publicPath($model->getOriginal('slug'));
            $to = $model->publicPath($model->slug);

            Redirect::remember($from, $to);
        });
    }

    abstract public function publicPath(?string $slug = null): string;
}
