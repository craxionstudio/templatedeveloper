<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * Konten publik: is_published = true dan published_at (kalau ada) sudah lewat.
 */
trait HasPublishing
{
    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where($this->qualifyColumn('is_published'), true)
            ->where(fn (Builder $q) => $q
                ->whereNull($this->qualifyColumn('published_at'))
                ->orWhere($this->qualifyColumn('published_at'), '<=', now()));
    }

    public function isPubliclyVisible(): bool
    {
        return $this->is_published && ($this->published_at === null || $this->published_at->lte(now()));
    }
}
