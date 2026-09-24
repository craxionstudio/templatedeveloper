<?php

namespace App\Models;

use App\Enums\DevelopmentStatus;
use App\Models\Concerns\HasPublishing;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class FutureDevelopment extends Model implements HasMedia
{
    use HasPublishing, InteractsWithMedia, SoftDeletes;

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

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')->singleFile();
    }
}
