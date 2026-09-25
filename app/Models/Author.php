<?php

namespace App\Models;

use App\Models\Concerns\HasResponsiveImages;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Author extends Model implements HasMedia
{
    use HasResponsiveImages, InteractsWithMedia {
        HasResponsiveImages::registerMediaConversions insteadof InteractsWithMedia;
    }

    protected $fillable = ['name', 'slug', 'job_title', 'bio', 'photo_alt'];

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }

    /**
     * @return list<string>
     */
    public function responsiveImageCollections(): array
    {
        return ['photo'];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('photo')->singleFile();
    }
}
