<?php

namespace App\Models;

use App\Models\Concerns\HasPublishing;
use App\Models\Concerns\HasResponsiveImages;
use App\Models\Concerns\HasSeoMeta;
use App\Models\Concerns\RedirectsOldSlug;
use App\Support\RichText;
use Database\Factories\ArticleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Article extends Model implements HasMedia
{
    /** @use HasFactory<ArticleFactory> */
    use HasFactory, HasPublishing, HasResponsiveImages, HasSeoMeta, InteractsWithMedia, RedirectsOldSlug, SoftDeletes {
        HasResponsiveImages::registerMediaConversions insteadof InteractsWithMedia;
    }

    protected $fillable = [
        'article_category_id', 'author_id', 'title', 'slug', 'excerpt', 'body', 'cover_alt',
        'is_highlight', 'is_published', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'is_highlight' => 'boolean',
            'is_published' => 'boolean',
            'published_at' => 'datetime',
            'reading_minutes' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $article): void {
            $article->body = RichText::sanitize($article->body);
            $article->reading_minutes = RichText::readingMinutes($article->body);
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ArticleCategory::class, 'article_category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function publicPath(?string $slug = null): string
    {
        return '/artikel/'.($slug ?? $this->slug);
    }

    /**
     * @return list<string>
     */
    public function responsiveImageCollections(): array
    {
        return ['cover'];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')->singleFile();
    }
}
