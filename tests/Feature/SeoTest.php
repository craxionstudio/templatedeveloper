<?php

use App\Http\Middleware\RedirectManager;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Cluster;
use App\Models\Kawasan;
use App\Models\Redirect;
use App\Models\User;
use App\Settings\GlobalSettings;
use App\Support\Sitemaps;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed();
    Sitemaps::flush();
});

/**
 * Simulasikan production (robots, canonical host, noindex).
 */
function asProduction(): void
{
    app()->detectEnvironment(fn () => 'production');
}

/**
 * @return list<array<string, mixed>>
 */
function jsonLd(TestResponse $response): array
{
    $page = $response->viewData('page');

    return $page['props']['meta']['jsonLd'];
}

/**
 * @param  list<array<string, mixed>>  $graphs
 * @return array<string, mixed>|null
 */
function ofType(array $graphs, string $type): ?array
{
    return collect($graphs)->first(fn (array $g) => $g['@type'] === $type || (is_array($g['@type']) && in_array($type, $g['@type'], true)));
}

it('memasang canonical absolut tanpa query, kecuali pagination', function (string $url, string $canonical) {
    $this->get($url)->assertInertia(fn (Assert $page) => $page->where('meta.canonical', url($canonical)));
})->with([
    'detail rumah dengan ?tipe' => ['/properti/vega-garden?tipe=deneb', '/properti/vega-garden'],
    'filter listing' => ['/properti?kawasan=mandiri&urut=harga-terendah', '/properti'],
    'pencarian artikel' => ['/artikel?q=kpr', '/artikel'],
    'pagination self-canonical' => ['/artikel?page=2', '/artikel?page=2'],
]);

it('memakai noindex, follow untuk filter & pencarian di production, index untuk halaman biasa', function () {
    asProduction();

    $this->get('/properti')->assertInertia(fn (Assert $page) => $page->where('meta.robots', 'index, follow, max-image-preview:large'));
    $this->get('/properti?kawasan=mandiri')->assertInertia(fn (Assert $page) => $page->where('meta.robots', 'noindex, follow'));
    $this->get('/fasilitas?kategori=pendidikan')->assertInertia(fn (Assert $page) => $page->where('meta.robots', 'noindex, follow'));
    $this->get('/artikel?q=kpr')->assertInertia(fn (Assert $page) => $page->where('meta.robots', 'noindex, follow'));
    $this->get('/terima-kasih')->assertInertia(fn (Assert $page) => $page->where('meta.robots', 'noindex, follow'));
    $this->get('/')->assertHeaderMissing('X-Robots-Tag');
});

it('mengisi Open Graph & Twitter lengkap dengan OG image default per tipe halaman', function () {
    $this->get('/properti/vega-garden')->assertInertia(fn (Assert $page) => $page
        ->where('meta.og.type', 'website')
        ->where('meta.og.url', url('/properti/vega-garden'))
        ->where('meta.og.locale', 'id_ID')
        ->where('meta.og.siteName', 'Arunika Land')
        ->where('meta.og.image', url('/og/rumah.png'))
        ->where('meta.og.imageWidth', 1200)
        ->where('meta.og.imageHeight', 630)
        ->has('meta.og.description'));

    foreach (['/' => 'home', '/properti' => 'properti', '/properti/kawasan' => 'kawasan', '/fasilitas' => 'fasilitas', '/artikel' => 'artikel', '/tentang-kami' => 'tentang', '/kontak' => 'kontak'] as $url => $image) {
        $this->get($url)->assertInertia(fn (Assert $page) => $page->where('meta.og.image', url("/og/{$image}.png")));
        expect(public_path("og/{$image}.png"))->toBeFile();
    }

    // OG image dari tab SEO konten menang.
    Cluster::query()->where('slug', 'vega-garden')->first()->seo()->create(['og_image' => 'seo/vega.jpg']);
    $this->get('/properti/vega-garden')->assertInertia(fn (Assert $page) => $page->where('meta.og.image', asset('storage/seo/vega.jpg')));
});

it('memakai og:type article dengan waktu terbit di detail artikel', function () {
    $article = Article::query()->published()->firstOrFail();

    $this->get($article->publicPath())->assertInertia(fn (Assert $page) => $page
        ->where('meta.og.type', 'article')
        ->where('meta.article.publishedTime', $article->published_at->toIso8601String())
        ->has('meta.article.modifiedTime'));
});

it('memasang Organization & WebSite di semua halaman dan BreadcrumbList selain home', function () {
    $home = jsonLd($this->get('/'));
    expect(ofType($home, 'Organization'))->toMatchArray(['@context' => 'https://schema.org', 'name' => 'Arunika Land', '@id' => url('/').'/#organization'])
        ->and(ofType($home, 'WebSite'))->toMatchArray(['inLanguage' => 'id-ID'])
        ->and(ofType($home, 'RealEstateAgent'))->not->toBeNull()
        ->and(ofType($home, 'BreadcrumbList'))->toBeNull();

    $contact = jsonLd($this->get('/kontak'));
    $office = ofType($contact, 'RealEstateAgent');
    expect($office['openingHoursSpecification'][0])->toMatchArray(['opens' => '09:00', 'closes' => '17:00'])
        ->and($office['openingHoursSpecification'][0]['dayOfWeek'])->toHaveCount(7)
        ->and($office['address']['addressCountry'])->toBe('ID');

    $breadcrumb = ofType(jsonLd($this->get('/properti/vega-garden')), 'BreadcrumbList');
    expect(collect($breadcrumb['itemListElement'])->pluck('name')->all())->toBe(['Beranda', 'Properti', 'Arunika Garden', 'Vega Garden'])
        ->and(collect($breadcrumb['itemListElement'])->pluck('item')->all())->toBe([url('/').'/', url('/properti'), url('/properti/kawasan/arunika-garden'), url('/properti/vega-garden')])
        ->and(collect($breadcrumb['itemListElement'])->pluck('position')->all())->toBe([1, 2, 3, 4]);
});

it('memasang ItemList di kedua tampilan listing', function () {
    $clusters = ofType(jsonLd($this->get('/properti')), 'ItemList');
    expect($clusters['numberOfItems'])->toBe(9)
        ->and(collect($clusters['itemListElement'])->pluck('url'))->toContain(url('/properti/vega-garden'));

    $kawasan = ofType(jsonLd($this->get('/properti/kawasan')), 'ItemList');
    expect(collect($kawasan['itemListElement'])->pluck('url')->all())->toBe([
        url('/properti/kawasan/arunika-garden'), url('/properti/kawasan/arunika-hills'), url('/properti/kawasan/arunika-lakeside'),
        url('/properti/kalea-townhouse'), url('/properti/hana-residence'),
    ]);
});

it('memasang Place + ItemList di Detail Kawasan', function () {
    $graphs = jsonLd($this->get('/properti/kawasan/arunika-garden'));
    $place = ofType($graphs, 'Place');

    expect($place)->toMatchArray(['name' => 'Arunika Garden', 'url' => url('/properti/kawasan/arunika-garden')])
        ->and($place['containedInPlace']['@type'])->toBe('Place')
        ->and(ofType($graphs, 'ItemList')['numberOfItems'])->toBe(3);
});

it('memasang Residence dengan tipe rumah, luas, kamar, dan Offer IDR di Detail Rumah', function () {
    $residence = ofType(jsonLd($this->get('/properti/vega-garden')), 'Residence');

    expect($residence['name'])->toBe('Vega Garden')
        ->and($residence['containsPlace'])->toHaveCount(3);

    $deneb = collect($residence['containsPlace'])->firstWhere('name', 'Vega Garden — Deneb');
    expect($deneb['@type'])->toBe(['Product', 'SingleFamilyResidence'])
        ->and($deneb['floorSize'])->toMatchArray(['@type' => 'QuantitativeValue', 'unitCode' => 'MTK'])
        ->and($deneb['numberOfRooms'])->toBeInt()
        ->and($deneb['numberOfBathroomsTotal'])->toBeInt()
        ->and($deneb['offers'])->toMatchArray(['@type' => 'Offer', 'priceCurrency' => 'IDR', 'availability' => 'https://schema.org/InStock'])
        ->and($deneb['offers']['price'])->toBeGreaterThan(0);
});

it('memasang BlogPosting di Detail Artikel', function () {
    $article = Article::query()->published()->with('author')->whereNotNull('author_id')->firstOrFail();
    $posting = ofType(jsonLd($this->get($article->publicPath())), 'BlogPosting');

    expect($posting)->toMatchArray([
        'headline' => $article->title,
        'datePublished' => $article->published_at->toAtomString(),
        'inLanguage' => 'id-ID',
        'mainEntityOfPage' => url($article->publicPath()),
    ])
        ->and($posting['author'])->toMatchArray(['@type' => 'Person', 'name' => $article->author->name])
        ->and($posting['publisher']['@id'])->toBe(url('/').'/#organization')
        ->and($posting)->toHaveKey('dateModified');
});

it('menghasilkan JSON-LD yang valid (JSON & @context) di semua tipe halaman', function (string $url) {
    foreach (jsonLd($this->get($url)) as $graph) {
        expect($graph['@context'])->toBe('https://schema.org')
            ->and($graph)->toHaveKey('@type')
            ->and(json_decode(json_encode($graph), true))->toBe($graph);
    }
})->with(['/', '/properti', '/properti/kawasan', '/properti/kawasan/arunika-garden', '/properti/vega-garden', '/fasilitas', '/artikel', '/tentang-kami', '/kontak', '/kebijakan-privasi']);

it('menyediakan sitemap index dengan tiga sitemap', function () {
    $xml = simplexml_load_string($this->get('/sitemap.xml')->assertOk()->assertHeader('Content-Type', 'application/xml; charset=UTF-8')->getContent());

    $locs = [];
    foreach ($xml->sitemap as $sitemap) {
        $locs[] = (string) $sitemap->loc;
    }

    expect($locs)->toBe([
        url('/sitemap-pages.xml'), url('/sitemap-properti.xml'), url('/sitemap-artikel.xml'),
    ])->and((string) $xml->sitemap[0]->lastmod)->not->toBe('');
});

it('hanya memuat URL yang dipublikasikan dan tidak noindex di sitemap', function () {
    Cluster::query()->where('slug', 'orion-park')->update(['is_published' => false]);
    Cluster::query()->where('slug', 'lyra-residence')->first()->seo()->create(['noindex' => true]);
    Kawasan::query()->where('slug', 'arunika-hills')->update(['is_published' => false]);
    $draft = Article::query()->published()->firstOrFail();
    $draft->update(['published_at' => now()->addWeek()]);

    $properti = $this->get('/sitemap-properti.xml')->getContent();
    expect($properti)
        ->toContain('<loc>'.url('/properti/kawasan').'</loc>')
        ->toContain(url('/properti/kawasan/arunika-garden'))
        ->toContain(url('/properti/vega-garden'))
        ->toContain(url('/properti/hana-residence'))
        ->not->toContain(url('/properti/orion-park'))
        ->not->toContain(url('/properti/lyra-residence'))
        ->not->toContain(url('/properti/kawasan/arunika-hills'))
        ->toContain('<lastmod>');

    $artikel = $this->get('/sitemap-artikel.xml')->getContent();
    expect($artikel)
        ->not->toContain(url($draft->publicPath()))
        ->toContain(url(ArticleCategory::query()->whereHas('articles', fn ($q) => $q->published())->firstOrFail()->publicPath()))
        ->toContain(url(Article::query()->published()->firstOrFail()->publicPath()));

    $pages = $this->get('/sitemap-pages.xml')->getContent();
    expect($pages)->toContain('<loc>'.url('/').'/</loc>')->toContain(url('/kontak'))->not->toContain('terima-kasih');
});

it('memperbarui sitemap otomatis saat konten berubah', function () {
    expect($this->get('/sitemap-properti.xml')->getContent())->toContain(url('/properti/orion-park'));

    Cluster::query()->where('slug', 'orion-park')->first()->update(['is_published' => false]);

    expect($this->get('/sitemap-properti.xml')->getContent())->not->toContain(url('/properti/orion-park'));
});

it('membedakan robots.txt production dan non-production', function () {
    $this->get('/robots.txt')->assertOk()->assertSeeText("User-agent: *\nDisallow: /", false);

    asProduction();
    $robots = $this->get('/robots.txt')->assertOk()->getContent();

    expect($robots)
        ->toContain('Allow: /')
        ->toContain('Disallow: /admin')
        ->toContain('Disallow: /livewire')
        ->toContain('Disallow: /terima-kasih')
        ->toContain('Disallow: /*?kawasan=')
        ->toContain('Disallow: /*?urut=')
        ->toContain('Sitemap: '.url('/sitemap.xml'))
        ->not->toContain("Disallow: /\n");
});

it('menyediakan RSS artikel yang valid dan tertaut dari head', function () {
    $response = $this->get('/artikel/feed.xml')->assertOk()->assertHeader('Content-Type', 'application/rss+xml; charset=UTF-8');
    $rss = simplexml_load_string($response->getContent());

    expect((string) $rss['version'])->toBe('2.0')
        ->and(count($rss->channel->item))->toBe(Article::query()->published()->count())
        ->and((string) $rss->channel->item[0]->link)->toStartWith(url('/artikel/'));

    expect($this->get('/')->getContent())->toContain('type="application/rss+xml"')->toContain(url('/artikel/feed.xml'));
});

it('menjalankan redirect dari Redirect Manager dan mencatat hit', function () {
    $redirect = Redirect::query()->create(['from_path' => '/promo-lama/', 'to_path' => '/properti', 'status_code' => 301]);
    Redirect::query()->create(['from_path' => '/sementara', 'to_path' => '/kontak', 'status_code' => 302]);
    Redirect::query()->create(['from_path' => '/cluster-dihapus', 'status_code' => 410]);

    expect($redirect->fresh()->from_path)->toBe('/promo-lama');

    $this->get('/promo-lama?utm_source=ig')->assertStatus(301)->assertRedirect(url('/properti?utm_source=ig'));
    $this->get('/sementara')->assertStatus(302)->assertRedirect(url('/kontak'));
    $this->get('/cluster-dihapus')->assertStatus(410)->assertInertia(fn (Assert $page) => $page->component('Errors/NotFound')->where('content.eyebrow', '410'));

    expect($redirect->fresh()->hits)->toBe(1);
});

it('mengarahkan slug lama ke slug baru secara otomatis (301)', function () {
    Cluster::query()->where('slug', 'vega-garden')->first()->update(['slug' => 'vega-garden-baru']);

    $this->get('/properti/vega-garden')->assertStatus(301)->assertRedirect(url('/properti/vega-garden-baru'));
});

it('menormalkan trailing slash dan huruf kapital dengan 301', function () {
    // Klien test Laravel memangkas trailing slash, jadi middleware diuji langsung.
    $middleware = new RedirectManager;
    $next = fn () => response('ok');

    $slash = $middleware->handle(Request::create('/properti/'), $next);
    expect($slash->getStatusCode())->toBe(301)->and($slash->headers->get('Location'))->toBe(url('/properti'));

    $upper = $middleware->handle(Request::create('/Properti/Vega-Garden/?tipe=deneb'), $next);
    expect($upper->getStatusCode())->toBe(301)->and($upper->headers->get('Location'))->toBe(url('/properti/vega-garden?tipe=deneb'));

    expect($middleware->handle(Request::create('/'), $next)->getContent())->toBe('ok')
        ->and($middleware->handle(Request::create('/storage/Foto-A.JPG'), $next)->getContent())->toBe('ok');

    $this->get('/Properti/Vega-Garden')->assertStatus(301)->assertRedirect(url('/properti/vega-garden'));
    $this->get('/properti')->assertOk();
});

it('mengarahkan http/www ke host kanonik di production', function () {
    asProduction();
    config(['app.url' => 'https://arunika.test']);

    $this->get('http://www.arunika.test/properti?tipe=x')->assertStatus(301)->assertRedirect('https://arunika.test/properti?tipe=x');
    $this->get('https://arunika.test/properti')->assertOk();
});

it('mengembalikan 410 untuk konten yang sudah dihapus', function () {
    Cluster::query()->where('slug', 'orion-park')->first()->delete();
    Article::query()->published()->firstOrFail()->delete();

    $this->get('/properti/orion-park')->assertStatus(410);
    $this->get('/artikel/'.Article::onlyTrashed()->value('slug'))->assertStatus(410);
    $this->get('/properti/tidak-pernah-ada')->assertNotFound();
});

it('menampilkan pratinjau draft artikel hanya lewat URL bertanda tangan untuk admin', function () {
    $draft = Article::query()->published()->firstOrFail();
    $draft->update(['is_published' => false]);
    $url = URL::temporarySignedRoute('artikel.preview', now()->addHour(), ['article' => $draft]);

    $this->get($draft->publicPath())->assertNotFound();
    $this->get($url)->assertForbidden();

    $this->actingAs(User::query()->where('email', 'admin@example.com')->firstOrFail());
    $this->get($url)->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Artikel/Show')
        ->where('preview', true)
        ->where('meta.noindex', true));
    $this->get(route('artikel.preview', ['article' => $draft]))->assertForbidden();

    // Admin Konten boleh, Marketing tidak.
    $this->actingAs(User::query()->where('email', 'marketing@example.com')->firstOrFail());
    $this->get($url)->assertForbidden();
});

it('menyusun title dari pola settings dan override meta title admin', function () {
    $this->get('/properti/kawasan/arunika-garden')->assertInertia(fn (Assert $page) => $page->where('meta.title', fn (string $title) => str_ends_with($title, '| Arunika Land')));

    Cluster::query()->where('slug', 'vega-garden')->first()->seo()->create(['meta_title' => 'Rumah Vega Garden Serpong', 'meta_description' => 'Deskripsi khusus.']);
    $this->get('/properti/vega-garden')->assertInertia(fn (Assert $page) => $page
        ->where('meta.title', 'Rumah Vega Garden Serpong')
        ->where('meta.description', 'Deskripsi khusus.'));

    $settings = app(GlobalSettings::class);
    expect($settings->section('seo')['title_pattern'])->toContain('{title}');
});
