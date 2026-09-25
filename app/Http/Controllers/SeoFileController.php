<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Settings\ArticleIndexPageSettings;
use App\Settings\GlobalSettings;
use App\Support\PageMeta;
use App\Support\Sitemaps;
use App\Support\StructuredData;
use Illuminate\Http\Response;

/**
 * File untuk crawler (brief 8.4): sitemap, robots.txt dinamis, RSS artikel.
 */
class SeoFileController extends Controller
{
    public function sitemapIndex(): Response
    {
        return $this->xml(Sitemaps::index());
    }

    public function sitemap(string $name): Response
    {
        abort_unless(in_array($name, Sitemaps::NAMES, true), 404);

        return $this->xml(Sitemaps::render($name));
    }

    /**
     * Production: izinkan crawl, blok /admin, /livewire, /terima-kasih. URL filter/urutan/pencarian
     * SENGAJA tidak diblok: Google harus bisa merayapinya untuk membaca meta robots
     * "noindex, follow" + canonical ke versi tanpa query (keputusan pemilik, koreksi brief 8.4).
     * Non-production: blok semuanya.
     */
    public function robots(): Response
    {
        if (! app()->isProduction()) {
            return $this->text("User-agent: *\nDisallow: /\n");
        }

        $lines = [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin',
            'Disallow: /livewire',
            'Disallow: /terima-kasih',
            '',
            'Sitemap: '.StructuredData::url('/sitemap.xml'),
        ];

        return $this->text(implode("\n", $lines)."\n");
    }

    public function feed(ArticleIndexPageSettings $settings, GlobalSettings $global): Response
    {
        $articles = Article::query()->published()->with(['category', 'author', 'media'])
            ->latest('published_at')->limit(20)->get();
        $brand = $global->section('identity')['brand_name'];

        $xml = view('feeds.articles', [
            'title' => $settings->section('header')['title'].' — '.$brand,
            'description' => PageMeta::description($settings->section('header')['description']) ?? $brand,
            'link' => StructuredData::url('/artikel'),
            'self' => StructuredData::url('/artikel/feed.xml'),
            'articles' => $articles,
            'updated' => $articles->max('updated_at') ?? now(),
        ])->render();

        return response($xml, 200, ['Content-Type' => 'application/rss+xml; charset=UTF-8']);
    }

    private function xml(string $content): Response
    {
        return response($content, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    private function text(string $content): Response
    {
        return response($content, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
