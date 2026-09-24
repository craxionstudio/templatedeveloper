<?php

use App\Models\Cluster;
use App\Settings\HomePageSettings;
use Inertia\Testing\AssertableInertia as Assert;

it('merender beranda dengan data layout global', function () {
    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Home')
            ->where('meta.title', 'Arunika Land — Developer Properti')
            ->has('hero.title')
            ->where('site.brand.name', 'Arunika Land')
            ->has('site.navigation', 5)
            ->where('site.navigation.0', ['label' => 'Beranda', 'url' => '/', 'new_tab' => false])
            ->has('site.footer.columns', 2)
            ->where('site.footer.columns.0.title', 'Properti')
            ->has('site.footer.social', 4)
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
    expect($this->get('/')->getContent())
        ->toContain('<meta name="robots" content="noindex, nofollow">');
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
        ])
    );
});

it('tidak menampilkan kawasan tanpa cluster yang dipublikasikan di footer', function () {
    $this->seed();
    Cluster::query()->whereHas('kawasan', fn ($q) => $q->where('slug', 'arunika-hills'))->update(['is_published' => false]);

    $this->get('/')->assertInertia(fn (Assert $page) => $page
        ->has('site.footer.columns.0.links', 2)
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

    $this->get('/')->assertInertia(fn (Assert $page) => $page->where('hero.title', 'Kota yang tumbuh bersama keluargamu.'));
});

it('tidak merender section yang dimatikan di settings', function () {
    $settings = app(HomePageSettings::class);
    $settings->hero = [...$settings->hero, 'enabled' => false];
    $settings->save();

    $this->get('/')->assertInertia(fn (Assert $page) => $page->where('hero', null));
});
