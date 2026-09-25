<?php

namespace App\Models;

use App\Enums\ClusterBadge;
use App\Enums\ClusterStatus;
use App\Enums\PropertyType;
use App\Models\Concerns\HasPublishing;
use App\Models\Concerns\HasResponsiveImages;
use App\Models\Concerns\HasSeoMeta;
use App\Models\Concerns\RedirectsOldSlug;
use Database\Factories\ClusterFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use InvalidArgumentException;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * Cluster = satu halaman Detail Rumah (/properti/{slug}).
 * kawasan_id null = cluster mandiri.
 */
class Cluster extends Model implements HasMedia
{
    /** @use HasFactory<ClusterFactory> */
    use HasFactory, HasPublishing, HasResponsiveImages, HasSeoMeta, InteractsWithMedia, RedirectsOldSlug, SoftDeletes {
        HasResponsiveImages::registerMediaConversions insteadof InteractsWithMedia;
    }

    /**
     * Slug yang bentrok dengan route /properti/{...} lain.
     */
    public const RESERVED_SLUGS = ['kawasan'];

    /**
     * Nilai filter ?kawasan= untuk cluster tanpa kawasan.
     */
    public const STANDALONE_FILTER = 'mandiri';

    protected $fillable = [
        'kawasan_id', 'name', 'slug', 'building_type', 'property_type', 'summary', 'description', 'address',
        'badge', 'status', 'booking_fee', 'price_note', 'installment_note', 'booking_fee_note', 'specifications',
        'legality', 'video_url', 'tour_360_url', 'marketing_name', 'marketing_title', 'marketing_whatsapp',
        'is_featured', 'sort_order', 'is_published', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'property_type' => PropertyType::class,
            'badge' => ClusterBadge::class,
            'status' => ClusterStatus::class,
            'booking_fee' => 'integer',
            'specifications' => 'array',
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
            'published_at' => 'datetime',
            'house_types_count' => 'integer',
            'price_min' => 'integer',
            'price_max' => 'integer',
            'installment_min' => 'integer',
            'land_area_min' => 'integer',
            'land_area_max' => 'integer',
            'bedrooms_min' => 'integer',
            'bedrooms_max' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $cluster): void {
            if (in_array($cluster->slug, self::RESERVED_SLUGS, true)) {
                throw new InvalidArgumentException("Slug cluster \"{$cluster->slug}\" dipakai sistem, pilih slug lain.");
            }
        });
    }

    public function kawasan(): BelongsTo
    {
        return $this->belongsTo(Kawasan::class);
    }

    public function houseTypes(): HasMany
    {
        return $this->hasMany(HouseType::class)->orderBy('sort_order')->orderBy('id');
    }

    public function publishedHouseTypes(): HasMany
    {
        return $this->houseTypes()->where('is_published', true);
    }

    public function promos(): BelongsToMany
    {
        return $this->belongsToMany(Promo::class);
    }

    public function galleryItems(): MorphMany
    {
        return $this->morphMany(GalleryItem::class, 'galleryable')->orderBy('sort_order');
    }

    public function isStandalone(): bool
    {
        return $this->kawasan_id === null;
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy($this->qualifyColumn('sort_order'))->orderBy($this->qualifyColumn('name'));
    }

    public function scopeStandalone(Builder $query): Builder
    {
        return $query->whereNull($this->qualifyColumn('kawasan_id'));
    }

    /**
     * Filter listing ?kawasan={slug} atau ?kawasan=mandiri.
     */
    public function scopeInKawasan(Builder $query, ?string $kawasan): Builder
    {
        return match (true) {
            blank($kawasan) => $query,
            $kawasan === self::STANDALONE_FILTER => $query->standalone(),
            default => $query->whereHas('kawasan', fn (Builder $q) => $q->where('slug', $kawasan)->published()),
        };
    }

    /**
     * Hitung ulang kolom turunan (jumlah tipe, rentang harga/LT/KT, cicilan mulai)
     * dari tipe rumah yang dipublikasikan.
     */
    public function refreshAggregates(): void
    {
        $types = $this->publishedHouseTypes()->get();

        $this->forceFill([
            'house_types_count' => $types->count(),
            'price_min' => $types->min('price_from'),
            'price_max' => $types->max('price_from'),
            'installment_min' => $types->min('installment_from'),
            'land_area_min' => $types->min('land_area'),
            'land_area_max' => $types->max('land_area'),
            'bedrooms_min' => $types->min('bedrooms'),
            'bedrooms_max' => $types->max('bedrooms'),
        ])->saveQuietly();
    }

    public function publicPath(?string $slug = null): string
    {
        return '/properti/'.($slug ?? $this->slug);
    }

    /**
     * @return list<string>
     */
    public function responsiveImageCollections(): array
    {
        return ['marketing_photo'];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('brochure')->singleFile()->acceptsMimeTypes(['application/pdf']);
        $this->addMediaCollection('pricelist')->singleFile()->acceptsMimeTypes(['application/pdf']);
        $this->addMediaCollection('marketing_photo')->singleFile();
    }
}
