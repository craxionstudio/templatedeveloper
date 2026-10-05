<?php

namespace App\Models;

use App\Models\Concerns\FillsSlugAutomatically;
use App\Models\Concerns\HasPublishing;
use App\Models\Concerns\HasResponsiveImages;
use App\Models\Concerns\HasSeoMeta;
use App\Models\Concerns\RedirectsOldSlug;
use App\Support\OtherClusters;
use App\Support\RichText;
use App\Support\Summary;
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
    use FillsSlugAutomatically, HasFactory, HasPublishing, HasResponsiveImages, HasSeoMeta, InteractsWithMedia, RedirectsOldSlug, SoftDeletes {
        HasResponsiveImages::registerMediaConversions insteadof InteractsWithMedia;
    }

    protected $fillable = [
        'name', 'slug', 'summary', 'about_title', 'description', 'area_ha', 'hero_alt', 'facilities',
        'map_embed_url', 'latitude', 'longitude', 'is_featured', 'sort_order', 'is_published', 'published_at',
        'access', 'opened_year', 'punya_halaman',
    ];

    protected function casts(): array
    {
        return [
            'area_ha' => 'decimal:2',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'facilities' => 'array',
            'access' => 'array',
            'opened_year' => 'integer',
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
            'punya_halaman' => 'boolean',
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
        static::saving(function (self $kawasan): void {
            $kawasan->description = RichText::sanitize($kawasan->description);
            // Ringkasan terisi otomatis dari deskripsi kalau dikosongkan.
            $kawasan->summary = filled($kawasan->summary) ? $kawasan->summary : (Summary::from($kawasan->description) ?? $kawasan->name);
        });
    }

    public function publishedClusters(): HasMany
    {
        return $this->clusters()->published()->latestLaunched();
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
     * Kawasan hanya tampil di publik (halaman detail, daftar kawasan, footer, sitemap) kalau dipublikasikan,
     * punya halaman sendiri (punya_halaman), DAN punya minimal 1 cluster dengan halaman yang dipublikasikan
     * (CHANGES.md Revisi 2 & Update 3).
     */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->published()
            ->where($this->qualifyColumn('punya_halaman'), true)
            ->whereHas('clusters', fn (Builder $q) => $q->published());
    }

    /**
     * Cluster yang hanya tampil sebagai nama ("Cluster lain di kawasan ini" & /properti/cluster-lainnya).
     */
    public function listedClusters(): HasMany
    {
        return $this->clusters()->listedOnly()->orderBy('name');
    }

    /**
     * Link kawasan untuk breadcrumb/kartu cluster: halaman detail, atau grupnya di Cluster Lainnya
     * kalau kawasan tidak punya halaman sendiri.
     */
    public function pagePath(): string
    {
        return $this->punya_halaman ? $this->publicPath() : self::otherClustersPath($this->slug);
    }

    /**
     * Anchor grup kawasan di /properti/cluster-lainnya.
     */
    public static function otherClustersPath(?string $slug): string
    {
        return '/properti/cluster-lainnya#'.($slug ?: OtherClusters::STANDALONE_ANCHOR);
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
