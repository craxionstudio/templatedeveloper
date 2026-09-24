<?php

namespace App\Models;

use App\Enums\PromoPlacement;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Promo extends Model implements HasMedia
{
    use InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'title', 'label', 'description', 'items', 'starts_at', 'ends_at', 'period_label', 'placement',
        'cta_label', 'cta_url', 'image_alt', 'sort_order', 'is_published',
    ];

    protected function casts(): array
    {
        return [
            'items' => 'array',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'placement' => PromoPlacement::class,
            'is_published' => 'boolean',
        ];
    }

    public function clusters(): BelongsToMany
    {
        return $this->belongsToMany(Cluster::class);
    }

    /**
     * Promo aktif: dipublikasikan dan berada di dalam periode. Kedaluwarsa otomatis tidak tampil.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->where('is_published', true)
            ->where(fn (Builder $q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn (Builder $q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()));
    }

    public function scopePlacement(Builder $query, PromoPlacement $placement): Builder
    {
        return $query->where('placement', $placement->value);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image_desktop')->singleFile();
        $this->addMediaCollection('image_mobile')->singleFile();
    }
}
