<?php

namespace App\Models;

use App\Models\Concerns\HasResponsiveImages;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * Profil lokasi / kota mandiri (satu baris). Dipakai di Home (Keunggulan Wilayah)
 * dan header Produk Listing.
 */
class Area extends Model implements HasMedia
{
    use HasResponsiveImages, InteractsWithMedia {
        HasResponsiveImages::registerMediaConversions insteadof InteractsWithMedia;
    }

    protected $fillable = [
        'name', 'location', 'description', 'area_ha', 'hero_alt', 'map_embed_url',
        'latitude', 'longitude', 'map_badge_label', 'map_badge_value', 'advantages',
    ];

    protected function casts(): array
    {
        return [
            'area_ha' => 'decimal:2',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'advantages' => 'array',
        ];
    }

    public static function current(): self
    {
        return static::query()->firstOrCreate([], ['name' => config('app.name')]);
    }

    /**
     * @return list<string>
     */
    public function responsiveImageCollections(): array
    {
        return ['hero', 'map'];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('hero')->singleFile();
        $this->addMediaCollection('map')->singleFile();
    }
}
