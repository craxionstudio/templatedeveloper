<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * Satu foto galeri (cluster / kawasan) beserta alt text & caption wajibnya.
 */
class GalleryItem extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = ['alt', 'caption', 'sort_order'];

    public function galleryable(): MorphTo
    {
        return $this->morphTo();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')->singleFile();
    }
}
