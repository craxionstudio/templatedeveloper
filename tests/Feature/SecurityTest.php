<?php

use App\Console\Commands\CheckPages;
use App\Filament\Pages\Settings\ManageGlobalSettings;
use App\Models\Cluster;
use App\Models\Kawasan;
use App\Models\User;
use App\Settings\ContactPageSettings;
use App\Settings\GlobalSettings;
use App\Settings\PrivacyPageSettings;
use App\Support\CspSources;
use App\Support\PageCache;
use Database\Seeders\UserSeeder;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed();
    config(['site.csp.enabled' => true]);
});

it('memasang security headers di semua respons', function (string $path) {
    $this->get($path)
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
        ->assertHeader('Permissions-Policy');
})->with(['/', '/properti/vega-garden', '/admin/login']);

it('memasang CSP dengan nonce yang sama di header dan semua script', function () {
    $response = $this->get('/')->assertOk();
    $csp = $response->headers->get('Content-Security-Policy');

    expect($csp)->toContain("'strict-dynamic'")->toContain("object-src 'none'")->toContain("base-uri 'self'")->toContain("frame-ancestors 'self'");
    preg_match("/'nonce-([^']+)'/", $csp, $nonce);

    $html = $response->getContent();
    preg_match_all('/<script(?![^>]*type="application\/(?:ld\+)?json")[^>]*>/', $html, $scripts);

    expect($scripts[0])->not->toBeEmpty();
    foreach ($scripts[0] as $tag) {
        expect($tag)->toContain('nonce="'.$nonce[1].'"');
    }
});

it('tidak memasang CSP di admin (Filament butuh Alpine/Livewire)', function () {
    $this->get('/admin/login')->assertHeaderMissing('Content-Security-Policy');
});

it('memakai nonce baru untuk halaman dari cache', function () {
    config(['site.page_cache.enabled' => true]);
    PageCache::flush();

    $first = $this->get('/fasilitas')->assertHeader('X-Page-Cache', 'MISS');
    $second = $this->get('/fasilitas')->assertHeader('X-Page-Cache', 'HIT');

    preg_match("/'nonce-([^']+)'/", $first->headers->get('Content-Security-Policy'), $a);
    preg_match("/'nonce-([^']+)'/", $second->headers->get('Content-Security-Policy'), $b);

    expect($a[1])->not->toBe($b[1])
        ->and($second->getContent())->toContain('nonce="'.$b[1].'"')
        ->not->toContain('nonce="'.$a[1].'"');
});

it('mensanitasi rich text sebelum dikirim ke browser', function () {
    $evil = '<p onclick="alert(1)">Aman</p><script>alert(1)</script><img src=x onerror="alert(1)"><a href="javascript:alert(1)">x</a>';

    Cluster::query()->where('slug', 'vega-garden')->first()->update(['description' => $evil]);
    Kawasan::query()->where('slug', 'arunika-garden')->first()->update(['description' => $evil]);
    $privacy = app(PrivacyPageSettings::class);
    $privacy->content = [...$privacy->content, 'body' => $evil];
    $privacy->save();

    $check = fn (?string $html) => expect($html)->toContain('Aman')->not->toContain('<script')->not->toContain('onclick')->not->toContain('onerror')->not->toContain('javascript:');

    $this->get('/properti/vega-garden')->assertInertia(fn (Assert $page) => $page->where('cluster.description', fn ($html) => $check($html) !== null));
    $this->get('/properti/kawasan/arunika-garden')->assertInertia(fn (Assert $page) => $page->where('about.description', fn ($html) => $check($html) !== null));
    $this->get('/kebijakan-privasi')->assertInertia(fn (Assert $page) => $page->where('content.body', fn ($html) => $check($html) !== null));

    // Juga disanitasi saat disimpan.
    expect(Cluster::query()->where('slug', 'vega-garden')->value('description'))->not->toContain('<script');
});

it('menjadwalkan backup harian dan membersihkan backup lama', function () {
    $commands = collect(app(Schedule::class)->events())->map(fn ($event) => $event->command)->implode("\n");

    expect($commands)->toContain('backup:run')->toContain('backup:clean')->toContain('backup:monitor')->toContain('sitemap:refresh')
        ->and(config('backup.backup.source.databases'))->not->toBeEmpty()
        ->and(config('backup.backup.source.files.include'))->toBe([storage_path('app/public')]);
});

it('mendeteksi masalah SSR & JSON-LD di pemeriksaan qa:pages', function () {
    $good = '<html><head><title>X</title><meta name="robots" content="index"><link rel="canonical" href="/"><meta property="og:image" content="/og.png">'
        .'<script type="application/ld+json">{"@context":"https://schema.org","@type":"Organization"}</script></head><body><h1>Judul</h1></body></html>';

    expect(CheckPages::problems(200, $good))->toBe([])
        ->and(CheckPages::problems(500, $good))->toContain('status 500')
        ->and(CheckPages::problems(200, str_replace('<h1>Judul</h1>', '', $good)))->toContain('0 H1')
        ->and(CheckPages::problems(200, str_replace('<link rel="canonical" href="/">', '', $good)))->toContain('tanpa canonical')
        ->and(CheckPages::problems(200, str_replace('Organization', 'Bukanschema', $good)))->toContain('@type tidak dikenal: Bukanschema')
        ->and(CheckPages::problems(200, str_replace('{"@context"', '{bukan json', $good)))->toContain('JSON-LD tidak valid');
});

it('menandai admin sebagai halaman yang tidak boleh di-frame dari luar', function () {
    $this->actingAs(User::query()->where('email', 'admin@example.com')->firstOrFail());
    $this->get('/admin')->assertOk()->assertHeader('X-Frame-Options', 'SAMEORIGIN');
});

it('memasang CSP juga di halaman 404 dan 410', function () {
    $this->get('/halaman-tidak-ada')->assertNotFound()->assertHeader('Content-Security-Policy');
    Cluster::query()->where('slug', 'orion-park')->first()->delete();
    $this->get('/properti/orion-park')->assertStatus(410)->assertHeader('Content-Security-Policy');
});

it('tidak memakai password "password" untuk akun seeder di production dan tidak me-reset password lama', function () {
    User::query()->delete();
    app()->detectEnvironment(fn () => 'production');

    (new UserSeeder)->run();
    $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();

    expect(Hash::check('password', $admin->password))->toBeFalse();

    $admin->update(['password' => 'Rahasia-Baru-123']);
    (new UserSeeder)->run();

    expect(Hash::check('Rahasia-Baru-123', $admin->fresh()->password))->toBeTrue();
});

it('memvalidasi format domain tambahan CSP', function (string $value, ?string $expected) {
    expect(CspSources::normalize($value))->toBe($expected);
})->with([
    ['analytics.tiktok.com', 'https://analytics.tiktok.com'],
    ['HTTPS://GoogleAds.G.DoubleClick.net/', 'https://googleads.g.doubleclick.net'],
    ['https://example.com:8443', 'https://example.com:8443'],
    ['*', null],
    ['*.tiktok.com', null],
    ['https://*.hotjar.com', null],
    ["'unsafe-eval'", null],
    ["'unsafe-inline'", null],
    ['https:', null],
    ['http://tracker.com', null],
    ['data:', null],
    ['tracker.com/path', null],
    ['evil.com; script-src *', null],
    ['evil.com https://other.com', null],
]);

it('menggabungkan domain tambahan CSP dari admin ke direktif yang tepat', function () {
    $this->actingAs(User::query()->where('email', 'admin@example.com')->firstOrFail());

    Livewire\Livewire::test(ManageGlobalSettings::class)
        ->fillForm([
            'tracking.csp_extra.script_src' => ['analytics.tiktok.com'],
            'tracking.csp_extra.connect_src' => ['analytics.tiktok.com', 'https://googleads.g.doubleclick.net'],
            'tracking.csp_extra.img_src' => ['www.googleadservices.com'],
            'tracking.csp_extra.frame_src' => ['bid.g.doubleclick.net'],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(app(GlobalSettings::class)->tracking['csp_extra']['connect_src'])
        ->toBe(['https://analytics.tiktok.com', 'https://googleads.g.doubleclick.net']);

    auth()->logout();
    $csp = collect(explode('; ', $this->get('/')->headers->get('Content-Security-Policy')))
        ->mapWithKeys(fn (string $d) => [strtok($d, ' ') => $d]);

    expect($csp['script-src'])->toContain('https://analytics.tiktok.com')
        ->and($csp['connect-src'])->toContain('https://analytics.tiktok.com')->toContain('https://googleads.g.doubleclick.net')
        ->and($csp['img-src'])->toContain('https://www.googleadservices.com')->not->toContain('analytics.tiktok.com')
        ->and($csp['frame-src'])->toContain('https://bid.g.doubleclick.net')
        // Bawaan tetap ada, dan tidak ada izin https: umum.
        ->and($csp['connect-src'])->toContain('https://*.google-analytics.com')->not->toMatch('/\shttps:(\s|$)/');
});

it('menolak domain CSP yang tidak valid di form admin', function () {
    $this->actingAs(User::query()->where('email', 'admin@example.com')->firstOrFail());

    Livewire\Livewire::test(ManageGlobalSettings::class)
        ->fillForm(['tracking.csp_extra.script_src' => ["'unsafe-eval'", '*']])
        ->call('save')
        ->assertHasFormErrors(['tracking.csp_extra.script_src.0', 'tracking.csp_extra.script_src.1']);

    expect(app(GlobalSettings::class)->tracking['csp_extra']['script_src'] ?? [])->toBe([]);
});

it('mengizinkan domain embed peta Kontak di frame-src secara otomatis', function () {
    $contact = app(ContactPageSettings::class);
    $contact->map = [...$contact->map, 'embed_url' => 'https://maps.example-embed.com/embed?pb=123'];
    $contact->save();

    expect($this->get('/kontak')->headers->get('Content-Security-Policy'))->toContain('https://maps.example-embed.com');
});
