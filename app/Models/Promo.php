<?php

namespace App\Models;

use App\Enums\PromoPlacement;
use App\Models\Concerns\HasResponsiveImages;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Promo extends Model implements HasMedia
{
    use HasResponsiveImages, InteractsWithMedia, SoftDeletes {
        HasResponsiveImages::registerMediaConversions insteadof InteractsWithMedia;
    }

    protected $fillable = [
        'title', 'label', 'description', 'items', 'starts_at', 'ends_at', 'period_label', 'placement',
        'cta_label', 'cta_url', 'image_alt', 'sort_order', 'is_published', 'catatan_internal',
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

    protected static function booted(): void
    {
        // Tanggal berakhir tanpa jam (00:00) = berlaku sampai akhir hari itu.
        static::saving(function (self $promo): void {
            if ($promo->ends_at && $promo->ends_at->format('H:i:s') === '00:00:00') {
                $promo->ends_at = $promo->ends_at->copy()->endOfDay();
            }
        });
    }

    public function clusters(): BelongsToMany
    {
        return $this->belongsToMany(Cluster::class);
    }

    public function kawasans(): BelongsToMany
    {
        return $this->belongsToMany(Kawasan::class);
    }

    /**
     * Waktu terdekat (mulai atau berakhir) sebuah promo yang dipublikasikan berganti status.
     * Cache halaman tidak boleh hidup melewati waktu ini supaya promo tampil/hilang tepat waktu.
     */
    public static function nextBoundary(): ?CarbonInterface
    {
        $now = now();
        $published = static::query()->where('is_published', true);

        $dates = array_filter([
            (clone $published)->where('starts_at', '>', $now)->min('starts_at'),
            (clone $published)->where('ends_at', '>', $now)->min('ends_at'),
        ]);

        return $dates === [] ? null : Carbon::parse(min($dates));
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

    /**
     * @return list<string>
     */
    public function responsiveImageCollections(): array
    {
        return ['image_desktop', 'image_mobile'];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image_desktop')->singleFile();
        $this->addMediaCollection('image_mobile')->singleFile();
    }
}
