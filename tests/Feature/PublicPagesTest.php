<?php

use App\Models\Article;
use App\Models\Cluster;
use App\Models\Kawasan;
use App\Settings\ListingPageSettings;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed();
});

it('merender semua halaman publik', function (string $path, string $component) {
    $this->get($path)->assertOk()->assertInertia(fn (Assert $page) => $page->component($component)->has('meta.title'));
})->with([
    ['/', 'Home'],
    ['/properti', 'Properti/Index'],
    ['/properti/kawasan', 'Properti/Kawasan'],
    ['/properti/kawasan/arunika-garden', 'Kawasan/Show'],
    ['/properti/vega-garden', 'Cluster/Show'],
    ['/properti/hana-residence', 'Cluster/Show'],
    ['/fasilitas', 'Fasilitas/Index'],
    ['/artikel', 'Artikel/Index'],
    ['/tentang-kami', 'About'],
    ['/kontak', 'Contact'],
    ['/kebijakan-privasi', 'Privacy'],
    ['/terima-kasih', 'ThankYou'],
]);

it('merender detail artikel dan halaman kategori', function () {
    $article = Article::query()->published()->with('category')->firstOrFail();

    $this->get('/artikel/'.$article->slug)->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Artikel/Show')
        ->where('article.title', $article->title)
        ->has('article.body'));

    $this->get($article->category->publicPath())->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Artikel/Index')
        ->where('header.title', $article->category->name));
});

it('memakai kartu cluster untuk Listing Produk di beranda', function () {
    $this->get('/')->assertInertia(fn (Assert $page) => $page
        ->has('listing.items', 4)
        ->has('listing.items.0', fn (Assert $card) => $card
            ->hasAll(['name', 'url', 'kawasan', 'typesCount', 'types', 'price', 'landArea', 'bedrooms'])
            ->etc()));
});

it('tidak menangkap /properti/kawasan sebagai slug cluster', function () {
    $this->get('/properti/kawasan')->assertInertia(fn (Assert $page) => $page->component('Properti/Kawasan'));
});

it('menampilkan semua cluster di tampilan Cluster', function () {
    $this->get('/properti')->assertInertia(fn (Assert $page) => $page
        ->where('result.clusters', 9)
        ->where('result.types', 20)
        ->has('clusters.data', 9));
});

it('memfilter cluster per kawasan dan cluster mandiri', function () {
    $this->get('/properti?kawasan=arunika-garden')->assertInertia(fn (Assert $page) => $page
        ->where('result.clusters', 3)
        ->where('clusters.data', fn ($items) => collect($items)->every(fn ($c) => $c['kawasan']['name'] === 'Arunika Garden')));

    $this->get('/properti?kawasan=mandiri')->assertInertia(fn (Assert $page) => $page
        ->where('result.clusters', 2)
        ->where('clusters.data', fn ($items) => collect($items)->pluck('kawasan')->filter()->isEmpty()));
});

it('memasang noindex pada listing yang difilter', function () {
    $this->get('/properti')->assertInertia(fn (Assert $page) => $page->where('meta.noindex', false));
    $this->get('/properti?kawasan=mandiri')->assertInertia(fn (Assert $page) => $page->where('meta.noindex', true));
});

it('menampilkan cluster mandiri hanya di section-nya sendiri pada tampilan Kawasan', function () {
    $this->get('/properti/kawasan')->assertInertia(fn (Assert $page) => $page
        ->has('kawasans', 3)
        ->has('standalone.items', 2)
        ->where('standalone.items', fn ($items) => collect($items)->pluck('name')->sort()->values()->all() === ['Hana Residence', 'Kalea Townhouse']));
});

it('menampilkan rentang harga dan kamar di kartu cluster', function () {
    $this->get('/properti?kawasan=arunika-garden')->assertInertia(fn (Assert $page) => $page
        ->where('clusters.data', fn ($items) => collect($items)->firstWhere('name', 'Vega Garden')['price'] === 'Rp 1,1 – 2,2 M'
            && collect($items)->firstWhere('name', 'Vega Garden')['bedrooms'] === '3 – 4+1'));
});

it('menampilkan ringkasan kawasan di Detail Kawasan', function () {
    $this->get('/properti/kawasan/arunika-garden')->assertInertia(fn (Assert $page) => $page
        ->has('clusters.items', 3)
        ->where('kawasan.name', 'Arunika Garden'));
});

it('memakai breadcrumb kawasan di Detail Rumah, kecuali cluster mandiri', function () {
    $this->get('/properti/vega-garden')->assertInertia(fn (Assert $page) => $page
        ->where('breadcrumbs', fn ($crumbs) => collect($crumbs)->pluck('url')->contains('/properti/kawasan/arunika-garden')));

    $this->get('/properti/hana-residence')->assertInertia(fn (Assert $page) => $page
        ->where('breadcrumbs', fn ($crumbs) => ! collect($crumbs)->pluck('url')->filter()->contains(fn ($url) => str_starts_with($url, '/properti/kawasan/'))));
});

it('mengembalikan 404 untuk konten yang tidak dipublikasikan', function () {
    Cluster::query()->where('slug', 'vega-garden')->update(['is_published' => false]);
    Kawasan::query()->where('slug', 'arunika-lakeside')->update(['is_published' => false]);
    $article = Article::query()->published()->firstOrFail();
    $article->update(['published_at' => now()->addWeek()]);

    $this->get('/properti/vega-garden')->assertNotFound();
    $this->get('/properti/kawasan/arunika-lakeside')->assertNotFound();
    $this->get('/artikel/'.$article->slug)->assertNotFound();
});

it('merender halaman 404 custom dengan status 404 asli', function () {
    $this->get('/halaman-yang-tidak-ada')
        ->assertNotFound()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Errors/NotFound')
            ->where('meta.noindex', true)
            ->has('content.links', 3)
            ->has('site.navigation'));
});

it('memasang noindex di halaman terima kasih', function () {
    $this->get('/terima-kasih')->assertInertia(fn (Assert $page) => $page->where('meta.noindex', true));
});

it('tidak merender section listing yang dimatikan', function () {
    $settings = app(ListingPageSettings::class);
    $settings->cta = [...$settings->cta, 'enabled' => false];
    $settings->save();

    $this->get('/properti')->assertInertia(fn (Assert $page) => $page->where('cta', null));
});

it('merender H1, canonical, dan JSON-LD di HTML awal lewat SSR (tanpa JavaScript)', function (string $path) {
    $ssr = parse_url((string) config('inertia.ssr.url', 'http://127.0.0.1:13714'));
    $socket = @fsockopen($ssr['host'] ?? '127.0.0.1', $ssr['port'] ?? 13714, $errno, $errstr, 0.5);

    if (! $socket) {
        $this->markTestSkipped('Server SSR tidak berjalan (php artisan inertia:start-ssr).');
    }

    fclose($socket);
    $html = $this->get($path)->assertOk()->getContent();
    $head = explode('</head>', $html)[0];

    expect(substr_count($html, '<h1'))->toBe(1)
        ->and($head)->toContain('rel="canonical"')
        ->and($head)->toContain('application/ld+json')
        ->and($head)->toContain('property="og:image"')
        ->and($html)->toMatch('/<a [^>]*href="\/properti/');
})->with(['/', '/properti', '/properti/kawasan', '/properti/kawasan/arunika-garden', '/properti/vega-garden', '/fasilitas', '/artikel', '/artikel/5-hal-yang-perlu-dicek-sebelum-mengajukan-kpr-rumah-pertama', '/tentang-kami', '/kontak']);
