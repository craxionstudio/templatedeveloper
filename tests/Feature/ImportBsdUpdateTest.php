<?php

use App\Filament\Resources\Clusters\Pages\ListClusters;
use App\Models\Cluster;
use App\Models\User;
use App\Settings\GlobalSettings;
use Illuminate\Support\Facades\Schema;
use Inertia\Testing\AssertableInertia as Assert;
use Livewire\Livewire;

/*
 * php artisan import:bsd-update (tanggal launching), urutan "Terbaru". Sistem promo lama sudah dihapus.
 */

beforeEach(function () {
    $this->seed();
    $this->artisan('import:bsd-data', ['--fresh' => true])->assertSuccessful();
    $this->artisan('import:bsd-update', ['path' => 'docs/data/bsd-city-update-2.json'])->assertSuccessful();
});

it('mengisi tanggal launching dari file dan 1 Januari untuk cluster yang hanya punya tahun', function () {
    expect(Cluster::where('slug', 'monard-of-the-armont')->first()->tanggal_launching->toDateString())->toBe('2026-07-07')
        ->and(Cluster::where('slug', 'lynelle')->first()->tanggal_launching->toDateString())->toBe('2025-10-29')
        // Hanya tahun launching di data awal.
        ->and(Cluster::whereNotNull('launch_year')->whereNull('tanggal_launching')->count())->toBe(0)
        ->and(Cluster::where('slug', 'tresor')->first()->tanggal_launching->toDateString())->toBe('2024-03-15')
        ->and(Cluster::whereNotNull('tanggal_launching')->get()->filter(fn (Cluster $c) => $c->tanggal_launching->format('m-d') === '01-01')->count())->toBeGreaterThan(0);
});

it('mengabaikan bagian "promos" di file update lama (sistem promo lama sudah dihapus)', function () {
    expect(Schema::hasTable('promos'))->toBeFalse()
        ->and(Schema::hasTable('cluster_promo'))->toBeFalse()
        ->and(Schema::hasTable('kawasan_promo'))->toBeFalse()
        ->and(json_decode((string) file_get_contents(base_path('docs/data/bsd-city-update-2.json')), true))->toHaveKey('promos');

    // Import ulang file yang masih berisi "promos" tetap sukses.
    $this->artisan('import:bsd-update', ['path' => 'docs/data/bsd-city-update-2.json'])->assertSuccessful();
});

it('mengurutkan /properti default berdasarkan tanggal launching terbaru, kosong di bawah, lalu prioritas & nama', function () {
    $names = collect($this->get('/properti?page=1')->inertiaProps('clusters.data'))->pluck('name');

    expect($names->take(6)->all())->toBe(['Monard of The Armont', 'Island Villa', 'Castilo at Terravia', 'Vyorelle at Vireya', 'IZZI', 'Lynelle']);

    // Tanggal sama: prioritas (terkecil dulu), lalu nama.
    Cluster::whereIn('slug', ['monard-of-the-armont', 'island-villa', 'lakewood'])->update(['tanggal_launching' => '2027-01-01']);
    $names = collect($this->get('/properti')->inertiaProps('clusters.data'))->pluck('name');
    expect($names->take(3)->all())->toBe(['Monard of The Armont', 'Island Villa', 'Lakewood']);

    // Tanpa tanggal launching: paling bawah.
    $last = Cluster::query()->published()->latestLaunched()->get()->last();
    expect($last->tanggal_launching)->toBeNull();
});

it('memakai urutan Terbaru di daftar cluster Detail Kawasan', function () {
    $names = collect($this->get('/properti/kawasan/vireya')->inertiaProps('clusters.items'))->pluck('name');

    // Vyorelle (22 Mei 2026) sebelum Lynelle (29 Okt 2025).
    expect($names->search('Vyorelle at Vireya'))->toBeLessThan($names->search('Lynelle'));
});

it('tidak lagi menampilkan promo lama di website (diganti Bank Benefit)', function () {
    $this->get('/properti/izzi')->assertOk()->assertInertia(fn (Assert $page) => $page->missing('promo'));
    $this->get('/properti/kawasan/vireya')->assertOk()->assertInertia(fn (Assert $page) => $page->missing('promos'));
    $this->get('/')->assertOk()->assertInertia(fn (Assert $page) => $page->missing('promos'));

    // Badge "Promo" sekarang dari Bank Benefit, bukan dari promo lama.
    $card = collect($this->get('/properti')->inertiaProps('clusters.data'))->firstWhere('name', 'IZZI');
    expect($card['badges'])->toBe(['Baru']);
});

it('bisa mengurutkan tabel cluster admin berdasarkan tanggal launching', function () {
    $this->actingAs(User::where('email', 'admin@example.com')->first());

    Livewire::test(ListClusters::class)
        ->assertTableColumnExists('tanggal_launching')
        ->sortTable('tanggal_launching', 'desc')
        ->assertCanSeeTableRecords([Cluster::where('slug', 'monard-of-the-armont')->first()]);
});

it('tidak menulis "Tipe Tipe" untuk nama tipe yang sudah diawali "Tipe"', function () {
    $global = app(GlobalSettings::class);
    $global->contact = [...$global->contact, 'whatsapp' => '081234567890'];
    $global->save();

    $url = $this->get('/properti/lynelle')->inertiaProps('types.0.whatsappUrl');

    expect(urldecode($url))->toContain('Lynelle Tipe 5 Standard')->not->toContain('tipe Tipe');
});

it('berjalan tanpa pertanyaan di production dengan --force --no-interaction (script deploy)', function () {
    app()->detectEnvironment(fn () => 'production');

    $this->artisan('import:bsd-data', ['path' => 'docs/data/bsd-city-data.json', '--fresh' => true, '--force' => true, '--no-interaction' => true])
        ->doesntExpectOutputToContain('Lanjutkan?')
        ->assertSuccessful();
    $this->artisan('import:bsd-update', ['path' => 'docs/data/bsd-city-update-2.json', '--force' => true, '--no-interaction' => true])
        ->assertSuccessful();

    expect(Cluster::count())->toBe(144)
        ->and(Cluster::where('slug', 'monard-of-the-armont')->first()->tanggal_launching->toDateString())->toBe('2026-07-07');
});

it('meminta konfirmasi --fresh di production tanpa --force dan berhenti kalau ditolak', function () {
    app()->detectEnvironment(fn () => 'production');
    $clusters = Cluster::count();

    $this->artisan('import:bsd-data', ['--fresh' => true])
        ->expectsConfirmation('--fresh menghapus SEMUA kawasan, cluster, dan tipe rumah di database production. Lanjutkan?', 'no')
        ->expectsOutputToContain('--force')
        ->assertFailed();

    expect(Cluster::count())->toBe($clusters);
});
