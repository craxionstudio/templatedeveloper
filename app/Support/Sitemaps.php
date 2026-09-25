<?php

namespace App\Support;

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Cluster;
use App\Models\GalleryItem;
use App\Models\Kawasan;
use App\Settings\AboutPageSettings;
use App\Settings\ArticleIndexPageSettings;
use App\Settings\ContactPageSettings;
use App\Settings\FacilityPageSettings;
use App\Settings\HomePageSettings;
use App\Settings\ListingPageSettings;
use App\Settings\PageSettings;
use App\Settings\PrivacyPageSettings;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\SitemapIndex;
use Spatie\Sitemap\Tags\Url;

/**
 * Sitemap (brief 8.4): /sitemap.xml = index → sitemap-pages.xml, sitemap-properti.xml,
 * sitemap-artikel.xml. Hanya konten yang dipublikasikan dan tidak noindex. XML di-cache
 * dan dibuang otomatis saat konten/settings berubah (AppServiceProvider), plus
 * `sitemap:refresh` harian di scheduler.
 */
class Sitemaps
{
    public const NAMES = ['pages', 'properti', 'artikel'];

    public static function index(): string
    {
        return self::cached('index', function (): string {
            $index = SitemapIndex::create();

            foreach (self::NAMES as $name) {
                $index->add(\Spatie\Sitemap\Tags\Sitemap::create(StructuredData::url("/sitemap-{$name}.xml"))
                    ->setLastModificationDate(self::lastModified($name)));
            }

            return $index->render();
        });
    }

    public static function render(string $name): string
    {
        return self::cached($name, fn (): string => (match ($name) {
            'pages' => self::pages(),
            'properti' => self::properti(),
            'artikel' => self::artikel(),
        })->render());
    }

    public static function flush(): void
    {
        foreach (['index', ...self::NAMES] as $name) {
            Cache::forget("sitemap.{$name}");
        }
    }

    /**
     * Buang lalu bangun ulang semua sitemap (scheduler harian).
     */
    public static function refresh(): void
    {
        self::flush();
        self::index();

        foreach (self::NAMES as $name) {
            self::render($name);
        }
    }

    private static function pages(): Sitemap
    {
        $sitemap = Sitemap::create();

        foreach ([
            '/' => [HomePageSettings::class, 'seo'],
            '/properti' => [ListingPageSettings::class, 'seo_cluster'],
            '/fasilitas' => [FacilityPageSettings::class, 'seo'],
            '/artikel' => [ArticleIndexPageSettings::class, 'seo'],
            '/tentang-kami' => [AboutPageSettings::class, 'seo'],
            '/kontak' => [ContactPageSettings::class, 'seo'],
            '/kebijakan-privasi' => [PrivacyPageSettings::class, 'seo'],
        ] as $path => [$settings, $seoKey]) {
            /** @var PageSettings $instance */
            $instance = app($settings);

            if (! ($instance->section($seoKey)['noindex'] ?? false)) {
                $sitemap->add(Url::create(StructuredData::url($path))->setLastModificationDate(self::settingsUpdatedAt($settings::group())));
            }
        }

        return $sitemap;
    }

    private static function properti(): Sitemap
    {
        $sitemap = Sitemap::create();
        $listing = app(ListingPageSettings::class);

        if (! ($listing->section('seo_kawasan')['noindex'] ?? false)) {
            $sitemap->add(Url::create(StructuredData::url('/properti/kawasan'))
                ->setLastModificationDate(self::latest(Kawasan::query()->visible())));
        }

        Kawasan::query()->visible()->ordered()->indexable()->with(['media', 'publishedClusters'])->get()
            ->each(function (Kawasan $kawasan) use ($sitemap): void {
                $url = Url::create(StructuredData::url($kawasan->publicPath()))
                    ->setLastModificationDate(self::max($kawasan->updated_at, $kawasan->publishedClusters->max('updated_at')));

                if ($image = $kawasan->getFirstMediaUrl('hero')) {
                    $url->addImage($image, title: $kawasan->hero_alt ?: $kawasan->name);
                }

                $sitemap->add($url);
            });

        Cluster::query()->published()->ordered()->indexable()->with(['galleryItems.media', 'publishedHouseTypes'])->get()
            ->each(function (Cluster $cluster) use ($sitemap): void {
                $url = Url::create(StructuredData::url($cluster->publicPath()))
                    ->setLastModificationDate(self::max($cluster->updated_at, $cluster->publishedHouseTypes->max('updated_at')));

                // Image sitemap: galeri cluster.
                $cluster->galleryItems->each(function (GalleryItem $item) use ($url, $cluster): void {
                    if ($image = $item->getFirstMediaUrl('image')) {
                        $url->addImage($image, caption: (string) ($item->caption ?? ''), title: $item->alt ?: $cluster->name);
                    }
                });

                $sitemap->add($url);
            });

        return $sitemap;
    }

    private static function artikel(): Sitemap
    {
        $sitemap = Sitemap::create();

        ArticleCategory::query()->orderBy('sort_order')->indexable()
            ->whereHas('articles', fn (Builder $q) => $q->published())
            ->get()
            ->each(fn (ArticleCategory $category) => $sitemap->add(Url::create(StructuredData::url($category->publicPath()))
                ->setLastModificationDate(self::latest(Article::query()->published()->where('article_category_id', $category->id)))));

        Article::query()->published()->indexable()->with('media')->latest('published_at')->get()
            ->each(function (Article $article) use ($sitemap): void {
                $url = Url::create(StructuredData::url($article->publicPath()))
                    ->setLastModificationDate($article->updated_at ?? $article->published_at ?? now());

                if ($image = $article->getFirstMediaUrl('cover')) {
                    $url->addImage($image, title: $article->cover_alt ?: $article->title);
                }

                $sitemap->add($url);
            });

        return $sitemap;
    }

    private static function lastModified(string $name): DateTimeInterface
    {
        return match ($name) {
            'pages' => self::max(...array_map(fn (string $group) => self::settingsUpdatedAt($group), ['page_home', 'page_listing', 'page_facility', 'page_article_index', 'page_about', 'page_contact', 'page_privacy'])),
            'properti' => self::max(self::latest(Cluster::query()->published()), self::latest(Kawasan::query()->visible())),
            'artikel' => self::latest(Article::query()->published()),
        };
    }

    private static function settingsUpdatedAt(string $group): DateTimeInterface
    {
        $value = DB::table('settings')->where('group', $group)->max('updated_at');

        return $value ? Carbon::parse($value) : now()->startOfDay();
    }

    private static function latest(Builder $query): DateTimeInterface
    {
        $value = $query->max($query->getModel()->qualifyColumn('updated_at'));

        return $value ? Carbon::parse($value) : now()->startOfDay();
    }

    private static function max(mixed ...$dates): DateTimeInterface
    {
        return collect($dates)->filter()->map(fn ($d) => Carbon::parse($d))->max() ?? now()->startOfDay();
    }

    private static function cached(string $name, \Closure $build): string
    {
        return Cache::rememberForever("sitemap.{$name}", $build);
    }
}
