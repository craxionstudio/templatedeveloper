<?php

namespace App\Models;

use App\Models\Concerns\FillsSlugAutomatically;
use App\Models\Concerns\HasResponsiveImages;
use Database\Factories\HouseTypeFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class HouseType extends Model implements HasMedia
{
    /** @use HasFactory<HouseTypeFactory> */
    use FillsSlugAutomatically, HasFactory, HasResponsiveImages, InteractsWithMedia {
        HasResponsiveImages::registerMediaConversions insteadof InteractsWithMedia;
    }

    protected $fillable = [
        'cluster_id', 'name', 'slug', 'lot_size', 'land_area', 'building_area', 'bedrooms', 'extra_bedrooms',
        'bathrooms', 'floors', 'carports', 'price_from', 'installment_from', 'units_available', 'floorplan_alt',
        'sort_order', 'is_published', 'catatan_internal',
    ];

    protected function casts(): array
    {
        return [
            'land_area' => 'integer',
            'building_area' => 'integer',
            'bedrooms' => 'integer',
            'extra_bedrooms' => 'integer',
            'bathrooms' => 'integer',
            'floors' => 'integer',
            'carports' => 'integer',
            'price_from' => 'integer',
            'installment_from' => 'integer',
            'units_available' => 'integer',
            'is_published' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        // Rentang di kartu cluster dihitung ulang setiap tipe berubah.
        $refresh = fn (self $type) => $type->cluster?->refreshAggregates();

        static::saved($refresh);
        static::deleted($refresh);
    }

    public function cluster(): BelongsTo
    {
        return $this->belongsTo(Cluster::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where($this->qualifyColumn('is_published'), true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy($this->qualifyColumn('sort_order'))->orderBy($this->qualifyColumn('id'));
    }

    /**
     * Nama untuk teks (judul, WA, JSON-LD). Tipe tanpa nama (harga "mulai" tingkat cluster) → null.
     */
    public function displayName(): ?string
    {
        return filled($this->name) ? $this->name : null;
    }

    /**
     * "3+1" untuk kamar tidur tambahan.
     */
    public function bedroomsLabel(): string
    {
        return $this->bedrooms.($this->extra_bedrooms > 0 ? '+'.$this->extra_bedrooms : '');
    }

    /**
     * Nilai urut kamar tidur (3+1 dihitung sedikit di atas 3, di bawah 4).
     */
    public function bedroomsSortValue(): float
    {
        return $this->bedrooms + ($this->extra_bedrooms > 0 ? 0.5 : 0);
    }

    /**
     * @return list<string>
     */
    public function responsiveImageCollections(): array
    {
        return ['floorplan'];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('floorplan')->singleFile();
    }

    /**
     * Slug tipe unik per cluster.
     */
    protected function slugQuery(): Builder
    {
        return static::query()->where('cluster_id', $this->cluster_id);
    }

    protected function slugSourceColumn(): string
    {
        return 'name';
    }
}
