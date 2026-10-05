<?php

use App\Models\Cluster;
use App\Models\Facility;
use App\Settings\HomePageSettings;
use App\Support\Content;
use Inertia\Testing\AssertableInertia as Assert;

it('merender beranda dengan data layout global', function () {
    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Home')
            ->where('meta.title', 'BSD City — Kota mandiri Sinar Mas Land di Serpong')
            ->has('hero.title')
            ->where('site.brand.name', 'BSD City')
            // 5 menu dari Menu Navigasi + "Kontak" (WhatsApp) di akhir.
            ->has('site.navigation', 6)
            ->where('site.navigation.0', ['label' => 'Beranda', 'url' => '/', 'new_tab' => false])
            ->where('site.navigation.5.label', 'Kontak')
            ->has('site.footer.columns', 2)
            ->where('site.footer.columns.0.title', 'Properti')
            // Link media sosial contoh ("#") tidak ditampilkan.
            ->has('site.footer.social', 0)
            ->has('site.labels.open_menu')
        );
});

it('memakai lang="id" dan font self-host yang dipreload', function () {
    $html = $this->get('/')->assertOk()->getContent();

    expect($html)
        ->toContain('<html lang="id">')
        ->toContain('rel="preload" as="font"')
        ->not->toContain('fonts.googleapis.com')
        ->not->toContain('fonts.bunny.net');
});

it('memasang noindex di environment non-production', function () {
    $this->get('/')
        ->assertHeader('X-Robots-Tag', 'noindex, nofollow')
        ->assertInertia(fn (Assert $page) => $page->where('meta.robots', 'noindex, nofollow'));
});

it('tidak menyediakan login atau registrasi pengunjung', function (string $path) {
    $this->get($path)->assertNotFound();
})->with(['/login', '/register', '/dashboard', '/settings/profile']);

it('mengisi kolom Properti di footer dari kawasan yang tampil di publik', function () {
    $this->seed();

    $this->get('/')->assertInertia(fn (Assert $page) => $page
        ->where('site.footer.columns.0.links', [
            ['label' => 'Arunika Garden', 'url' => '/properti/kawasan/arunika-garden'],
            ['label' => 'Arunika Hills', 'url' => '/properti/kawasan/arunika-hills'],
            ['label' => 'Arunika Lakeside', 'url' => '/properti/kawasan/arunika-lakeside'],
            ['label' => 'Semua kawasan', 'url' => '/properti/kawasan'],
        ])
    );
});

it('tidak menampilkan kawasan tanpa cluster yang dipublikasikan di footer', function () {
    $this->seed();
    Cluster::query()->whereHas('kawasan', fn ($q) => $q->where('slug', 'arunika-hills'))->update(['is_published' => false]);

    $this->get('/')->assertInertia(fn (Assert $page) => $page
        ->has('site.footer.columns.0.links', 3) // 2 kawasan + "Semua kawasan"
        ->where('site.footer.columns.0.links.1.label', 'Arunika Lakeside')
    );
});

it('langsung menampilkan teks baru setelah settings diubah', function () {
    $settings = app(HomePageSettings::class);
    $settings->hero = [...$settings->hero, 'title' => 'Judul baru dari admin'];
    $settings->save();

    $this->get('/')->assertInertia(fn (Assert $page) => $page->where('hero.title', 'Judul baru dari admin'));
});

it('memakai isi awal kalau teks settings dikosongkan', function () {
    $settings = app(HomePageSettings::class);
    $settings->hero = [...$settings->hero, 'title' => ''];
    $settings->save();

    $this->get('/')->assertInertia(fn (Assert $page) => $page->where('hero.title', 'Pilih rumah di kota seluas 6.000 hektare.'));
});

it('menyembunyikan section Beranda otomatis kalau datanya kosong, bukan lewat toggle', function () {
    $this->seed();
    // Toggle lama di database tidak berpengaruh lagi.
    $settings = app(HomePageSettings::class);
    $settings->hero = [...$settings->hero, 'enabled' => false];
    $settings->facilities = [...$settings->facilities, 'enabled' => false];
    $settings->save();

    $this->get('/')->assertInertia(fn (Assert $page) => $page
        ->has('hero.title')
        ->has('facilities.items')
        ->has('listing.items'));

    Facility::query()->update(['is_published' => false]);
    Cluster::query()->update(['is_published' => false]);

    $this->get('/')->assertInertia(fn (Assert $page) => $page
        ->where('facilities', null)
        ->where('listing', null));
});

it('menganggap teks contoh dalam kurung siku sebagai kosong', function () {
    $this->seed();
    expect(Content::blank('[VISI PERUSAHAAN]'))->toBeTrue()
        ->and(Content::blank([['year' => '[TAHUN]', 'title' => '[TONGGAK]']]))->toBeTrue()
        ->and(Content::filled('Visi kami [2026]'))->toBeTrue();

    // Visi & timeline masih teks contoh → section tidak tampil.
    $this->get('/tentang-kami')->assertInertia(fn (Assert $page) => $page->where('vision', null)->where('timeline', null));
});
