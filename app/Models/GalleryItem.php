<?php

namespace App\Models;

use App\Models\Concerns\HasResponsiveImages;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * Satu foto galeri (cluster / kawasan) beserta alt text & caption wajibnya.
 */
class GalleryItem extends Model implements HasMedia
{
    use HasResponsiveImages, InteractsWithMedia {
        HasResponsiveImages::registerMediaConversions insteadof InteractsWithMedia;
    }

    protected $fillable = ['alt', 'caption', 'sort_order'];

    public function galleryable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return list<string>
     */
    public function responsiveImageCollections(): array
    {
        return ['image'];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')->singleFile();
    }
}
