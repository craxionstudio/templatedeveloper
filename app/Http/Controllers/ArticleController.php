<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Tag;
use App\Presenters\ArticleCard;
use App\Presenters\Image;
use App\Settings\ArticleDetailPageSettings;
use App\Settings\ArticleIndexPageSettings;
use App\Settings\GlobalSettings;
use App\Support\Breadcrumbs;
use App\Support\Cta;
use App\Support\PageMeta;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ArticleController extends Controller
{
    public function index(Request $request, ArticleIndexPageSettings $settings): Response
    {
        return $this->listing($request, $settings, null);
    }

    /**
     * Halaman kategori: URL sendiri (/artikel/kategori/{slug}), terindeks.
     */
    public function category(Request $request, string $slug, ArticleIndexPageSettings $settings): Response
    {
        return $this->listing($request, $settings, ArticleCategory::query()->where('slug', $slug)->with('seo')->firstOrFail());
    }

    public function show(string $slug, ArticleDetailPageSettings $settings, GlobalSettings $global): Response
    {
        $article = Article::query()->published()->where('slug', $slug)
            ->with(['category', 'author.media', 'tags', 'seo', 'media'])
            ->firstOrFail();

        $display = $settings->section('display');
        $related = $settings->section('related');
        $seoPattern = $settings->section('seo');
        $values = ['title' => $article->title, 'excerpt' => $article->excerpt];

        // "Diperbarui" hanya kalau jauh (> 1 hari) dari tanggal terbit.
        $updated = $article->updated_at && $article->published_at && $article->updated_at->diffInHours($article->published_at, true) > 24
            ? $article->updated_at : null;

        return Inertia::render('Artikel/Show', [
            'meta' => PageMeta::make(PageMeta::fill($seoPattern['title_pattern'], $values), PageMeta::fill($seoPattern['description_pattern'], $values), $article->seo?->toArray() ?? []),
            'breadcrumbs' => Breadcrumbs::make(array_values(array_filter([
                [Breadcrumbs::nav('/artikel', 'Artikel'), '/artikel'],
                $article->category ? [$article->category->name, $article->category->publicPath()] : null,
            ]))),
            'article' => [
                ...ArticleCard::make($article),
                'body' => $article->body,
                'author' => $display['show_author'] && $article->author ? [
                    'name' => $article->author->name,
                    'photo' => Image::media($article->author, 'photo', $article->author->photo_alt, 'Foto '.$article->author->name),
                ] : null,
                'updated' => $updated?->translatedFormat('j M Y'),
                'tags' => $display['show_tags'] ? $article->tags->map(fn (Tag $tag) => $tag->name)->values()->all() : [],
            ],
            'display' => [
                'showReadingTime' => (bool) $display['show_reading_time'],
                'showShare' => (bool) $display['show_share'],
                'updatedLabel' => $display['updated_label'],
                'readingLabel' => $global->section('labels')['reading_time'],
                'shareLabel' => $global->section('labels')['share'],
            ],
            'related' => $related['enabled'] ? $this->related($article, $related) : null,
            'cta' => Cta::resolve($settings->section('cta')),
        ]);
    }

    private function listing(Request $request, ArticleIndexPageSettings $settings, ?ArticleCategory $category): Response
    {
        $header = $settings->section('header');
        $list = $settings->section('list');
        $newsletter = $settings->section('newsletter');
        $search = $list['show_search'] ? trim((string) $request->query('q')) : '';

        $published = Article::query()->published()->with(ArticleCard::with());

        $highlight = $header['highlight_article_id'] ? (clone $published)->find($header['highlight_article_id']) : null;
        $highlight ??= (clone $published)->where('is_highlight', true)->latest('published_at')->first();

        $matching = (clone $published)
            ->when($category, fn (Builder $q) => $q->where('article_category_id', $category->id))
            ->when($search !== '', fn (Builder $q) => $q->where(fn (Builder $q) => $q
                ->where('title', 'like', "%{$search}%")
                ->orWhere('excerpt', 'like', "%{$search}%")));

        // Jumlah = semua artikel yang cocok (termasuk highlight).
        $total = (clone $matching)->count();

        $paginator = (clone $matching)
            // Highlight tampil di atas; tidak diulang di grid halaman utama.
            ->when($highlight && ! $category && $search === '', fn (Builder $q) => $q->whereKeyNot($highlight->getKey()))
            ->latest('published_at')
            ->paginate((int) $list['per_page'])
            ->withQueryString();

        $categories = ArticleCategory::query()
            ->when(filled($list['categories']), fn (Builder $q) => $q->whereKey($list['categories']))
            ->whereHas('articles', fn (Builder $q) => $q->published())
            ->orderBy('sort_order')
            ->get();

        $title = $category ? $category->name : $header['title'];

        return Inertia::render('Artikel/Index', [
            'meta' => PageMeta::make(
                $title,
                $category ? ($category->description ?: $header['description']) : $header['description'],
                $category ? ($category->seo?->toArray() ?? []) : $settings->section('seo'),
                noindex: $search !== '',
            ),
            'breadcrumbs' => Breadcrumbs::make(array_values(array_filter([
                [Breadcrumbs::nav('/artikel', 'Artikel'), $category ? '/artikel' : null],
                $category ? [$category->name] : null,
            ]))),
            'header' => [
                'eyebrow' => $header['eyebrow'],
                'title' => $title,
                'description' => $category ? ($category->description ?: $header['description']) : $header['description'],
                'highlightBadge' => $header['highlight_badge'],
            ],
            'highlight' => $highlight && ! $category && $search === '' && $paginator->currentPage() === 1 ? ArticleCard::make($highlight) : null,
            'readLabel' => app(GlobalSettings::class)->section('labels')['read_article'],
            'readingLabel' => app(GlobalSettings::class)->section('labels')['reading_time'],
            'categories' => [
                'all' => ['label' => $list['all_label'], 'url' => '/artikel', 'active' => $category === null],
                'items' => $categories->map(fn (ArticleCategory $c) => ['label' => $c->name, 'url' => $c->publicPath(), 'active' => $category?->is($c) ?? false])->values()->all(),
            ],
            'search' => $list['show_search'] ? ['placeholder' => $list['search_placeholder'], 'value' => $search, 'action' => $category ? $category->publicPath() : '/artikel'] : null,
            'count' => trim($total.' '.$list['count_suffix']),
            'emptyText' => $list['empty_text'],
            'articles' => [
                'data' => ArticleCard::collection($paginator->items()),
                'pagination' => self::pagination($paginator),
            ],
            'newsletter' => $newsletter['enabled'] ? [
                'title' => $newsletter['title'],
                'description' => $newsletter['description'],
                'placeholder' => $newsletter['email_placeholder'],
                'buttonLabel' => $newsletter['button_label'],
            ] : null,
        ]);
    }

    /**
     * @param  array<string, mixed>  $section
     * @return array<string, mixed>|null
     */
    private function related(Article $article, array $section): ?array
    {
        $tagIds = $article->tags->pluck('id');

        $items = Article::query()->published()->whereKeyNot($article->getKey())->with(ArticleCard::with())
            ->where(fn (Builder $q) => $q
                ->where('article_category_id', $article->article_category_id)
                ->orWhereHas('tags', fn (Builder $q) => $q->whereKey($tagIds)))
            ->latest('published_at')
            ->limit((int) $section['limit'])
            ->get();

        return $items->isEmpty() ? null : [
            'title' => $section['title'],
            'link' => ['label' => $section['link_label'], 'url' => $section['link_url']],
            'items' => ArticleCard::collection($items),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function pagination(LengthAwarePaginator $paginator): array
    {
        return [
            'current' => $paginator->currentPage(),
            'last' => $paginator->lastPage(),
            'prev' => $paginator->previousPageUrl(),
            'next' => $paginator->nextPageUrl(),
            'pages' => collect(range(1, max(1, $paginator->lastPage())))
                ->map(fn (int $page) => ['page' => $page, 'url' => $paginator->url($page)])
                ->all(),
        ];
    }
}
