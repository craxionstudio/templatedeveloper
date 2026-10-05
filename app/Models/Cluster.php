<?php

namespace App\Models;

use App\Enums\ClusterBadge;
use App\Enums\ClusterStatus;
use App\Enums\PropertyType;
use App\Models\Concerns\FillsSlugAutomatically;
use App\Models\Concerns\HasPublishing;
use App\Models\Concerns\HasResponsiveImages;
use App\Models\Concerns\HasSeoMeta;
use App\Models\Concerns\RedirectsOldSlug;
use App\Support\RichText;
use App\Support\Summary;
use Database\Factories\ClusterFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Cluster = satu halaman Detail Rumah (/properti/{slug}).
 * kawasan_id null = cluster mandiri.
 */
class Cluster extends Model implements HasMedia
{
    /** @use HasFactory<ClusterFactory> */
    use FillsSlugAutomatically, HasFactory, HasPublishing, HasResponsiveImages, HasSeoMeta, InteractsWithMedia, RedirectsOldSlug, SoftDeletes {
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
        'legality', 'video_url', 'tour_360_url', 'marketing_name', 'marketing_title',
        'is_featured', 'sort_order', 'is_published', 'published_at',
        'facilities', 'launch_year', 'tanggal_launching', 'prioritas', 'catatan_internal', 'perlu_dilengkapi',
    ];

    protected function casts(): array
    {
        return [
            'property_type' => PropertyType::class,
            'badge' => ClusterBadge::class,
            'status' => ClusterStatus::class,
            'booking_fee' => 'integer',
            'specifications' => 'array',
            'facilities' => 'array',
            'perlu_dilengkapi' => 'array',
            'launch_year' => 'integer',
            'tanggal_launching' => 'date',
            'prioritas' => 'integer',
            'perlu_dilengkapi_count' => 'integer',
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

            $cluster->description = RichText::sanitize($cluster->description);
            // Terisi otomatis: ringkasan dari deskripsi, tahun launching dari tanggal launching.
            if (blank($cluster->summary)) {
                $cluster->summary = Summary::from($cluster->description);
            }
            if ($cluster->tanggal_launching) {
                $cluster->launch_year = $cluster->tanggal_launching->year;
            }
            $cluster->perlu_dilengkapi_count = collect($cluster->perlu_dilengkapi ?? [])
                ->filter(fn ($item) => filled($item['item'] ?? null) && ! ($item['selesai'] ?? false))
                ->count();
        });
    }

    /**
     * Salinan cluster untuk admin ("Duplikat cluster"): semua isi, tipe rumah, benefit, foto, dan
     * file ikut disalin. Salinan belum dipublikasikan, namanya diberi akhiran "(salinan)", slug baru
     * dibuat otomatis, dan data internal (catatan, prioritas) tidak ikut.
     */
    public function duplicate(): self
    {
        return DB::transaction(function (): self {
            $copy = $this->replicate(['slug', 'published_at', 'house_types_count', 'catatan_internal', 'prioritas', 'perlu_dilengkapi', 'perlu_dilengkapi_count']);
            $copy->name = $this->name.' (salinan)';
            $copy->is_published = false;
            $copy->is_featured = false;
            $copy->save();

            foreach ($this->houseTypes()->get() as $type) {
                $newType = $type->replicate(['cluster_id']);
                $newType->cluster_id = $copy->id;
                $newType->is_published = $type->is_published;
                $newType->save();
                $type->getMedia('floorplan')->each(fn (Media $media) => $media->copy($newType, 'floorplan'));
            }

            foreach ($this->clusterBenefits()->get() as $benefit) {
                $copy->clusterBenefits()->create($benefit->only(['benefit_id', 'teks_tampil', 'urutan']));
            }

            foreach ($this->galleryItems()->get() as $item) {
                $newItem = $copy->galleryItems()->create($item->only(['alt', 'caption', 'sort_order']));
                $item->getMedia('image')->each(fn (Media $media) => $media->copy($newItem, 'image'));
            }

            foreach (['brochure', 'pricelist', 'marketing_photo'] as $collection) {
                $this->getMedia($collection)->each(fn (Media $media) => $media->copy($copy, $collection));
            }

            $copy->refreshAggregates();

            return $copy;
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

    /**
     * Benefit yang dicentang di cluster ini (urutan dari admin). Kartu & Detail Rumah memakai
     * activeBenefits (benefit nonaktif di Bank Benefit tidak tampil).
     */
    public function benefits(): BelongsToMany
    {
        return $this->belongsToMany(Benefit::class)
            ->using(BenefitCluster::class)
            ->withPivot(['id', 'teks_tampil', 'urutan'])
            ->withTimestamps()
            ->orderByPivot('urutan')
            ->orderByPivot('id');
    }

    public function activeBenefits(): BelongsToMany
    {
        return $this->benefits()->active();
    }

    /**
     * Baris pivot benefit (repeater "Promo & Benefit" di admin).
     */
    public function clusterBenefits(): HasMany
    {
        return $this->hasMany(BenefitCluster::class)->orderBy('urutan')->orderBy('id');
    }

    /**
     * Cluster yang punya SEMUA benefit aktif ini (filter ?benefit=a,b).
     *
     * @param  list<string>  $slugs
     */
    public function scopeWithBenefits(Builder $query, array $slugs): Builder
    {
        foreach ($slugs as $slug) {
            $query->whereHas('benefits', fn (Builder $q) => $q->active()->where('slug', $slug));
        }

        return $query;
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

    /**
     * Urutan "Terbaru": tanggal launching terbaru dulu (kosong paling bawah), lalu prioritas
     * (kosong paling bawah), lalu nama.
     */
    public function scopeLatestLaunched(Builder $query): Builder
    {
        $launch = $this->qualifyColumn('tanggal_launching');
        $priority = $this->qualifyColumn('prioritas');

        return $query
            ->orderByRaw("CASE WHEN {$launch} IS NULL THEN 1 ELSE 0 END")
            ->orderByDesc($launch)
            ->orderByRaw("CASE WHEN {$priority} IS NULL THEN 1 ELSE 0 END")
            ->orderBy($priority)
            ->orderBy($this->qualifyColumn('name'));
    }

    /**
     * Cluster yang sudah punya harga di atas, yang belum punya harga di bawah (urutan listing publik).
     */
    public function scopePricedFirst(Builder $query): Builder
    {
        $column = $this->qualifyColumn('price_min');

        return $query->orderByRaw("CASE WHEN {$column} IS NULL OR {$column} <= 0 THEN 1 ELSE 0 END");
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
        // Nilai kosong / 0 = belum diisi, tidak ikut rentang kartu (hindari "Rp 0", "0 KT").
        $values = fn (string $column) => $types->pluck($column)->filter(fn ($v) => $v !== null && $v > 0);

        $this->forceFill([
            'house_types_count' => $types->count(),
            'price_min' => $values('price_from')->min(),
            'price_max' => $values('price_from')->max(),
            'installment_min' => $values('installment_from')->min(),
            'land_area_min' => $values('land_area')->min(),
            'land_area_max' => $values('land_area')->max(),
            'bedrooms_min' => $values('bedrooms')->min(),
            'bedrooms_max' => $values('bedrooms')->max(),
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
