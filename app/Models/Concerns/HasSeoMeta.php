<?php

namespace App\Models\Concerns;

use App\Models\SeoMeta;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * Tab SEO per konten (meta title/description, canonical, OG image, noindex).
 */
trait HasSeoMeta
{
    public function seo(): MorphOne
    {
        return $this->morphOne(SeoMeta::class, 'seoable');
    }

    /**
     * Tidak ditandai noindex di tab SEO (untuk sitemap).
     */
    public function scopeIndexable(Builder $query): Builder
    {
        return $query->whereDoesntHave('seo', fn (Builder $seo) => $seo->where('noindex', true));
    }
}
