<?php

namespace App\Models;

use App\Support\RichText;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * Profil developer (satu baris). Dipakai di Home (Tentang Developer) dan Tentang Kami.
 */
class DeveloperProfile extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = ['headline', 'description', 'history', 'vision_quote', 'stats', 'photo_alt', 'secondary_photo_alt'];

    protected function casts(): array
    {
        return ['stats' => 'array'];
    }

    protected static function booted(): void
    {
        static::saving(fn (self $profile) => $profile->history = RichText::sanitize($profile->history));
    }

    public static function current(): self
    {
        return static::query()->firstOrCreate([], ['headline' => config('app.name')]);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('photo')->singleFile();
        $this->addMediaCollection('secondary_photo')->singleFile();
    }
}
