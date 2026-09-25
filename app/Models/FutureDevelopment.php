<?php

namespace App\Models;

use App\Enums\DevelopmentStatus;
use App\Models\Concerns\HasPublishing;
use App\Models\Concerns\HasResponsiveImages;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class FutureDevelopment extends Model implements HasMedia
{
    use HasPublishing, HasResponsiveImages, InteractsWithMedia, SoftDeletes {
        HasResponsiveImages::registerMediaConversions insteadof InteractsWithMedia;
    }

    protected $fillable = ['target', 'title', 'description', 'status', 'image_alt', 'sort_order', 'is_published', 'published_at'];

    protected function casts(): array
    {
        return [
            'status' => DevelopmentStatus::class,
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy($this->qualifyColumn('sort_order'))->orderBy($this->qualifyColumn('target'));
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
