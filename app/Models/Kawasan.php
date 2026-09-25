<?php

namespace App\Models;

use App\Models\Concerns\HasPublishing;
use App\Models\Concerns\HasResponsiveImages;
use App\Models\Concerns\HasSeoMeta;
use App\Models\Concerns\RedirectsOldSlug;
use App\Support\RichText;
use Database\Factories\KawasanFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * Kawasan di dalam kota mandiri. Jumlah cluster & harga mulai DIHITUNG dari cluster
 * yang dipublikasikan, tidak diisi manual.
 */
class Kawasan extends Model implements HasMedia
{
    /** @use HasFactory<KawasanFactory> */
    use HasFactory, HasPublishing, HasResponsiveImages, HasSeoMeta, InteractsWithMedia, RedirectsOldSlug, SoftDeletes {
        HasResponsiveImages::registerMediaConversions insteadof InteractsWithMedia;
    }

    protected $fillable = [
        'name', 'slug', 'summary', 'about_title', 'description', 'area_ha', 'hero_alt', 'facilities',
        'map_embed_url', 'latitude', 'longitude', 'is_featured', 'sort_order', 'is_published', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'area_ha' => 'decimal:2',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'facilities' => 'array',
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function clusters(): HasMany
    {
        return $this->hasMany(Cluster::class);
    }

    protected static function booted(): void
    {
        // Rich text dari admin: hanya tag yang diizinkan (brief 10).
        static::saving(fn (self $kawasan) => $kawasan->description = RichText::sanitize($kawasan->description));
    }

    public function publishedClusters(): HasMany
    {
        return $this->clusters()->published()->ordered();
    }

    public function galleryItems(): MorphMany
    {
        return $this->morphMany(GalleryItem::class, 'galleryable')->orderBy('sort_order');
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy($this->qualifyColumn('sort_order'))->orderBy($this->qualifyColumn('name'));
    }

    /**
     * Kawasan hanya tampil di publik kalau dipublikasikan DAN punya minimal 1 cluster
     * yang dipublikasikan (CHANGES.md Revisi 2).
     */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->published()->whereHas('clusters', fn (Builder $q) => $q->published());
    }

    public function publicPath(?string $slug = null): string
    {
        return '/properti/kawasan/'.($slug ?? $this->slug);
    }

    /**
     * @return list<string>
     */
    public function responsiveImageCollections(): array
    {
        return ['hero'];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('hero')->singleFile();
        $this->addMediaCollection('brochure')->singleFile()->acceptsMimeTypes(['application/pdf']);
    }
}
