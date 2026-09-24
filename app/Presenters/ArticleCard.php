<?php

namespace App\Presenters;

use App\Models\Article;

class ArticleCard
{
    /**
     * @return array<string, mixed>
     */
    public static function make(Article $article): array
    {
        return [
            'id' => $article->id,
            'title' => $article->title,
            'url' => $article->publicPath(),
            'excerpt' => $article->excerpt,
            'category' => $article->category ? ['name' => $article->category->name, 'url' => $article->category->publicPath()] : null,
            'date' => $article->published_at?->translatedFormat('j M Y'),
            'dateIso' => $article->published_at?->toIso8601String(),
            'readingMinutes' => $article->reading_minutes,
            'image' => Image::media($article, 'cover', $article->cover_alt, 'Foto artikel'),
        ];
    }

    /**
     * @param  iterable<Article>  $articles
     * @return list<array<string, mixed>>
     */
    public static function collection(iterable $articles): array
    {
        return collect($articles)->map(fn (Article $article) => self::make($article))->values()->all();
    }

    /**
     * @return list<string>
     */
    public static function with(): array
    {
        return ['category', 'media'];
    }
}
