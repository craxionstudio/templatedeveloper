<?php

use App\Jobs\GenerateImageVariants;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

/*
 * Pendukung script deploy: cek SSR (ssr:check) dan laporan antrean (ops:queue-status).
 */

beforeEach(function () {
    $this->seed();
});

it('menandai ssr:check gagal kalau halaman tidak dirender di server', function () {
    config(['inertia.ssr.enabled' => true, 'inertia.ssr.url' => 'http://127.0.0.1:1']);

    $this->artisan('ssr:check', ['path' => '/properti'])
        ->expectsOutputToContain('SSR GAGAL /properti: tidak dirender di server')
        ->assertFailed();
});

it('memakai host APP_URL supaya tidak kena redirect host kanonik di production', function () {
    app()->detectEnvironment(fn () => 'production');
    config(['app.url' => 'https://www.contoh-bsd.test', 'inertia.ssr.enabled' => true, 'inertia.ssr.url' => 'http://127.0.0.1:1']);

    $this->artisan('ssr:check', ['path' => '/properti'])
        ->doesntExpectOutputToContain('status 301')
        ->expectsOutputToContain('tidak dirender di server')
        ->assertFailed();
});

it('tidak merekam Inertia DevTools saat ssr:check (folder bisa milik user web server)', function () {
    $dir = storage_path('framework/testing/devtools-'.uniqid());
    config(['inertia.devtools.enabled' => true, 'inertia.devtools.storage.path' => $dir]);

    $this->artisan('ssr:check', ['path' => '/properti']);

    expect(glob($dir.'/*') ?: [])->toBe([]);
    File::deleteDirectory($dir);
});

it('lolos ssr:check kalau server SSR berjalan', function () {
    $ssr = parse_url((string) config('inertia.ssr.url', 'http://127.0.0.1:13714'));
    $socket = @fsockopen($ssr['host'] ?? '127.0.0.1', $ssr['port'] ?? 13714, $errno, $errstr, 0.5);

    if (! $socket) {
        $this->markTestSkipped('Server SSR tidak berjalan (php artisan inertia:start-ssr).');
    }

    fclose($socket);

    $this->artisan('ssr:check', ['path' => '/properti'])
        ->expectsOutputToContain('SSR OK /properti: H1 "Properti')
        ->assertSuccessful();
});

it('melaporkan jumlah job antre, job gagal, dan heartbeat scheduler', function () {
    Storage::fake('local');
    config(['queue.default' => 'database']);
    GenerateImageVariants::dispatch([]);

    Artisan::call('ops:queue-status');
    expect(Artisan::output())->toContain('1 job antre')->toContain('0 job gagal; heartbeat scheduler: belum ada');

    Storage::disk('local')->put('scheduler-heartbeat', '2026-10-05T14:00:00+07:00');
    Artisan::call('ops:queue-status');
    expect(Artisan::output())->toContain('heartbeat scheduler: 2026-10-05T14:00:00+07:00');
});

it('menjalankan worker queue dan heartbeat dari scheduler tiap menit', function () {
    $events = collect(app(Schedule::class)->events())->keyBy->description;

    expect($events['queue-worker']->command)->toContain('queue:work')->toContain('--stop-when-empty')
        ->and($events['queue-worker']->expression)->toBe('* * * * *')
        ->and($events['scheduler-heartbeat']->expression)->toBe('* * * * *');
});

it('melaporkan environment, indeks, robots, dan perilaku production', function () {
    app()->detectEnvironment(fn () => 'production');
    config(['app.debug' => false, 'site.indexable' => false]);

    Artisan::call('ops:site-status');

    expect(Artisan::output())
        ->toContain('APP_ENV: production')
        ->toContain('APP_DEBUG: false')
        ->toContain('SITE_INDEXABLE: false')
        ->toContain('Beranda: X-Robots-Tag: noindex, nofollow')
        ->toContain('robots.txt: User-agent: * | Disallow: /')
        ->toContain('Paksa host & HTTPS (APP_URL): aktif');
});

it('menjaga header respons production di bawah 4 KB (batas buffer bawaan nginx)', function () {
    // Tanpa batas, header Link semua aset + nonce CSP + cookie > 4 KB dan nginx membalas 502
    // "upstream sent too big header" (terjadi di server saat APP_ENV diubah ke production).
    app()->detectEnvironment(fn () => 'production');
    config(['app.url' => 'http://templatedeveloper.craxionstudio.com', 'site.csp.enabled' => true]);

    foreach (['/', '/properti', '/properti/vega-garden', '/artikel'] as $path) {
        $response = $this->get('http://templatedeveloper.craxionstudio.com'.$path);
        $size = strlen("HTTP/1.1 200 OK\r\n".$response->headers);

        expect($size)->toBeLessThan(3584, "{$path}: header {$size} byte");
        expect(substr_count((string) $response->headers->get('Link'), 'rel='))->toBeLessThanOrEqual(4);
    }
});
