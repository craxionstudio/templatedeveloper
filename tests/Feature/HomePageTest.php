<?php

use Inertia\Testing\AssertableInertia as Assert;

it('merender beranda dengan data layout global', function () {
    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('home')
            ->where('meta.title', 'Arunika Land — Developer Properti')
            ->has('hero.title')
            ->where('site.brand.name', 'Arunika Land')
            ->has('site.navigation', 5)
            ->where('site.navigation.0', ['label' => 'Beranda', 'url' => '/'])
            ->has('site.footer.columns', 2)
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
