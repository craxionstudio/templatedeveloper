<?php

use App\Jobs\GenerateImageVariants;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Artisan;
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
