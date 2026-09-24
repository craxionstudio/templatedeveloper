<?php

namespace App\Models;

use App\Models\Concerns\HasSeoMeta;
use App\Models\Concerns\RedirectsOldSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ArticleCategory extends Model
{
    use HasSeoMeta, RedirectsOldSlug;

    protected $fillable = ['name', 'slug', 'description', 'sort_order'];

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }

    public function publicPath(?string $slug = null): string
    {
        return '/artikel/kategori/'.($slug ?? $this->slug);
    }
}
