<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Slug terisi otomatis dari nama/judul kalau dikosongkan di admin, dan dibuat unik (-2, -3, …).
 * Slug yang diisi manual tidak diubah.
 */
trait FillsSlugAutomatically
{
    public static function bootFillsSlugAutomatically(): void
    {
        static::saving(function (Model $model): void {
            if (filled($model->slug)) {
                return;
            }

            $base = Str::slug((string) $model->{$model->slugSourceColumn()}) ?: Str::lower(class_basename($model));
            $reserved = defined(static::class.'::RESERVED_SLUGS') ? static::RESERVED_SLUGS : [];
            $slug = $base;

            for ($i = 2; in_array($slug, $reserved, true) || $model->slugQuery()->where('slug', $slug)->whereKeyNot($model->getKey())->exists(); $i++) {
                $slug = "{$base}-{$i}";
            }

            $model->slug = $slug;
        });
    }

    protected function slugSourceColumn(): string
    {
        return 'name';
    }

    /**
     * Cakupan keunikan slug (mis. per cluster untuk tipe rumah).
     */
    protected function slugQuery(): Builder
    {
        $query = static::query();

        return method_exists($query, 'withTrashed') ? $query->withTrashed() : $query;
    }
}
