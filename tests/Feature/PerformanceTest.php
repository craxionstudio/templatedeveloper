<?php

use App\Http\Middleware\HandleInertiaRequests;
use App\Models\Article;
use App\Models\Cluster;
use App\Models\User;
use App\Presenters\Image;
use App\Settings\HomePageSettings;
use App\Support\Attribution;
use App\Support\PageCache;
use App\Support\ResponsiveImages;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed();
    Storage::fake('public');
});

it('membuat varian WebP + AVIF responsif untuk gambar media library tanpa memperbesar', function () {
    $article = Article::query()->published()->firstOrFail();
    $article->addMedia(UploadedFile::fake()->image('cover.jpg', 1200, 800))->toMediaCollection('cover');

    $media = $article->fresh()->getFirstMedia('cover');

    foreach ([480, 960] as $width) {
        foreach (['webp', 'avif'] as $format) {
            expect($media->hasGeneratedConversion("{$width}-{$format}"))->toBeTrue()
                ->and(Storage::disk('public')->exists($media->getPathRelativeToRoot("{$width}-{$format}")))->toBeTrue();
        }
    }

    $image = Image::media($article->fresh(), 'cover', 'Cover');

    expect($image)->toMatchArray(['width' => 1200, 'height' => 800])
        ->and($image['sources'][0]['type'])->toBe('image/avif')
        ->and($image['sources'][1]['type'])->toBe('image/webp')
        // 1600 tidak diperbesar: lebar maksimum = lebar asli 1200.
        ->and($image['sources'][1]['srcset'])->toContain(' 480w')->toContain(' 960w')->toContain(' 1200w')->not->toContain('1600w');

    [$w] = getimagesize(Storage::disk('public')->path($media->getPathRelativeToRoot('1600-webp')));
    expect($w)->toBe(1200);
});

it('mengirim srcset & ukuran gambar ke halaman (galeri Detail Rumah)', function () {
    $cluster = Cluster::query()->where('slug', 'vega-garden')->firstOrFail();
    $cluster->galleryItems()->create(['alt' => 'Fasad', 'sort_order' => 0])
        ->addMedia(UploadedFile::fake()->image('fasad.jpg', 1600, 1000))->toMediaCollection('image');

    $this->get('/properti/vega-garden')->assertInertia(fn (Assert $page) => $page
        ->where('gallery.items.0.width', 1600)
        ->where('gallery.items.0.height', 1000)
        ->where('gallery.items.0.sources.0.type', 'image/avif')
        ->where('meta.og.image', fn (string $url) => str_contains($url, 'fasad')));

    // Kartu cluster (listing) juga memakai varian responsif.
    $this->get('/properti')->assertInertia(fn (Assert $page) => $page
        ->where('clusters.data', fn ($items) => collect($items)->firstWhere('name', 'Vega Garden')['image']['sources'][0]['type'] === 'image/avif'));
});

it('membuat varian untuk gambar yang diunggah lewat settings halaman', function () {
    Storage::disk('public')->put('home/hero.jpg', UploadedFile::fake()->image('hero.jpg', 2000, 1200)->getContent());

    $settings = app(HomePageSettings::class);
    $settings->hero = [...$settings->hero, 'image' => 'home/hero.jpg'];
    $settings->save();

    expect(Storage::disk('public')->exists(ResponsiveImages::variantPath('home/hero.jpg', 960, 'avif')))->toBeTrue()
        ->and(Storage::disk('public')->exists(ResponsiveImages::variantPath('home/hero.jpg', 1600, 'webp')))->toBeTrue();

    $this->get('/')->assertInertia(fn (Assert $page) => $page
        ->where('hero.image.width', 2000)
        ->where('hero.image.sources.0.type', 'image/avif')
        ->where('hero.image.sources.1.srcset', fn (string $srcset) => str_contains($srcset, '_variants/home/hero-1600.webp 1600w')));
});

describe('cache halaman publik', function () {
    beforeEach(function () {
        config(['site.page_cache.enabled' => true]);
        PageCache::flush();
    });

    it('menyimpan HTML tamu dan dibuang saat konten berubah', function () {
        $this->get('/properti')->assertOk()->assertHeader('X-Page-Cache', 'MISS');
        $this->get('/properti')->assertOk()->assertHeader('X-Page-Cache', 'HIT')->assertSee('Vega Garden');

        // Parameter atribusi tidak membuat cache baru.
        $this->get('/properti?utm_source=facebook&fbclid=abc')->assertHeader('X-Page-Cache', 'HIT');
        // Filter = halaman lain.
        $this->get('/properti?kawasan=mandiri')->assertHeader('X-Page-Cache', 'MISS');

        Cluster::query()->where('slug', 'vega-garden')->first()->update(['name' => 'Vega Garden Baru']);

        $this->get('/properti')->assertHeader('X-Page-Cache', 'MISS')
            ->assertInertia(fn (Assert $page) => $page->where('clusters.data.0.name', 'Vega Garden Baru'));
    });

    it('dibuang saat settings halaman disimpan', function () {
        $this->get('/')->assertHeader('X-Page-Cache', 'MISS');
        $this->get('/')->assertHeader('X-Page-Cache', 'HIT');

        $settings = app(HomePageSettings::class);
        $settings->hero = [...$settings->hero, 'title' => 'Judul baru dari admin'];
        $settings->save();

        $this->get('/')->assertHeader('X-Page-Cache', 'MISS')
            ->assertInertia(fn (Assert $page) => $page->where('hero.title', 'Judul baru dari admin'));
    });

    it('tidak meng-cache request Inertia, user login, halaman terima kasih, dan hasil submit form', function () {
        $this->get('/kontak', ['X-Inertia' => 'true', 'X-Inertia-Version' => app(HandleInertiaRequests::class)->version(request())])
            ->assertHeaderMissing('X-Page-Cache');

        $this->get('/terima-kasih')->assertHeaderMissing('X-Page-Cache');

        // Error validasi di session → halaman harus menampilkan error, bukan versi cache.
        $this->get('/kontak')->assertHeader('X-Page-Cache', 'MISS');
        $this->from('/kontak')->post('/lead', ['name' => '', 'whatsapp' => '', 'source_position' => 'kontak']);
        $this->get('/kontak')->assertHeaderMissing('X-Page-Cache')->assertInertia(fn (Assert $page) => $page->has('errors.name'));

        $this->actingAs(User::query()->where('email', 'admin@example.com')->firstOrFail());
        $this->get('/kontak')->assertHeaderMissing('X-Page-Cache');
    });

    it('tetap menangkap UTM walaupun halaman dari cache', function () {
        $this->get('/fasilitas');
        $response = $this->get('/fasilitas?utm_source=google&utm_campaign=cache')->assertHeader('X-Page-Cache', 'HIT');

        expect(collect($response->headers->getCookies())->map->getName())->toContain(Attribution::COOKIE);
    });
});

it('memasang HSTS hanya di production lewat HTTPS', function () {
    $this->get('/')->assertHeaderMissing('Strict-Transport-Security');

    app()->detectEnvironment(fn () => 'production');
    config(['app.url' => 'https://localhost']);

    $this->get('https://localhost/')->assertHeader('Strict-Transport-Security', 'max-age=31536000');
});
