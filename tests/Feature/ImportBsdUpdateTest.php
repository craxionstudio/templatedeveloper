<?php

use App\Enums\PromoPlacement;
use App\Filament\Resources\Clusters\Pages\ListClusters;
use App\Filament\Resources\Promos\Pages\EditPromo;
use App\Models\Cluster;
use App\Models\Kawasan;
use App\Models\Promo;
use App\Models\User;
use App\Settings\GlobalSettings;
use Inertia\Testing\AssertableInertia as Assert;
use Livewire\Livewire;

/*
 * php artisan import:bsd-update (tanggal launching & promo), urutan "Terbaru", dan promo aktif.
 */

beforeEach(function () {
    $this->seed();
    $this->artisan('import:bsd-data', ['--fresh' => true])->assertSuccessful();
    $this->artisan('import:bsd-update', ['path' => 'docs/data/bsd-city-update-2.json'])->assertSuccessful();
});

function bsdPromo(string $title): Promo
{
    return Promo::query()->where('title', $title)->firstOrFail();
}

function publishPromo(string $title, array $attributes = []): Promo
{
    $promo = bsdPromo($title);
    $promo->update(['is_published' => true, ...$attributes]);

    return $promo;
}

it('mengisi tanggal launching dari file dan 1 Januari untuk cluster yang hanya punya tahun', function () {
    expect(Cluster::where('slug', 'monard-of-the-armont')->first()->tanggal_launching->toDateString())->toBe('2026-07-07')
        ->and(Cluster::where('slug', 'lynelle')->first()->tanggal_launching->toDateString())->toBe('2025-10-29')
        // Hanya tahun launching di data awal.
        ->and(Cluster::whereNotNull('launch_year')->whereNull('tanggal_launching')->count())->toBe(0)
        ->and(Cluster::where('slug', 'tresor')->first()->tanggal_launching->toDateString())->toBe('2024-03-15')
        ->and(Cluster::whereNotNull('tanggal_launching')->get()->filter(fn (Cluster $c) => $c->tanggal_launching->format('m-d') === '01-01')->count())->toBeGreaterThan(0);
});

it('mengimpor promo sebagai draft dengan sumber di catatan internal, aman diulang', function () {
    $this->artisan('import:bsd-update', ['path' => 'docs/data/bsd-city-update-2.json'])->assertSuccessful();

    $promo = bsdPromo('Promo Caelus September');

    expect(Promo::whereIn('title', ['Promo Castilo', 'Promo IZZI', 'Promo Caelus September'])->count())->toBe(3)
        ->and(Promo::where('catatan_internal', '!=', null)->count())->toBe(9)
        ->and($promo)
        ->is_published->toBeFalse()
        ->placement->toBe(PromoPlacement::Detail)
        ->catatan_internal->toBe('sinarmasland.com halaman produk (Sep 2026)')
        ->and($promo->starts_at->toDateTimeString())->toBe('2026-09-01 00:00:00')
        ->and($promo->ends_at->toDateTimeString())->toBe('2026-09-30 23:59:59')
        ->and($promo->clusters->pluck('slug')->all())->toBe(['caelus'])
        ->and(DB::table('cluster_promo')->where('promo_id', $promo->id)->count())->toBe(1);
});

it('tidak mematikan lagi promo yang sudah dipublikasikan admin saat import diulang', function () {
    publishPromo('Promo IZZI');

    $this->artisan('import:bsd-update', ['path' => 'docs/data/bsd-city-update-2.json'])->assertSuccessful();

    expect(bsdPromo('Promo IZZI')->is_published)->toBeTrue();
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

it('tidak lagi menampilkan promo lama di website walau dipublikasikan (diganti Bank Benefit)', function () {
    publishPromo('Promo IZZI');
    publishPromo('Promo Lynelle');

    $this->get('/properti/izzi')->assertOk()->assertInertia(fn (Assert $page) => $page->missing('promo'));
    $this->get('/properti/kawasan/vireya')->assertOk()->assertInertia(fn (Assert $page) => $page->missing('promos'));
    $this->get('/')->assertOk()->assertInertia(fn (Assert $page) => $page->where('promos', null));

    // Badge "Promo" sekarang dari Bank Benefit, bukan dari promo lama.
    $card = collect($this->get('/properti')->inertiaProps('clusters.data'))->firstWhere('name', 'IZZI');
    expect($card['badges'])->toBe(['Baru']);
});

it('bisa mengurutkan tabel cluster admin berdasarkan tanggal launching dan menghubungkan promo ke kawasan', function () {
    $this->actingAs(User::where('email', 'admin@example.com')->first());

    Livewire::test(ListClusters::class)
        ->assertTableColumnExists('tanggal_launching')
        ->sortTable('tanggal_launching', 'desc')
        ->assertCanSeeTableRecords([Cluster::where('slug', 'monard-of-the-armont')->first()]);

    $promo = bsdPromo('Promo Lynelle');
    $vireya = Kawasan::where('slug', 'vireya')->first();

    Livewire::test(EditPromo::class, ['record' => $promo->getRouteKey()])
        ->assertSchemaStateSet(['catatan_internal' => 'vireyabsd.co.id (situs agen)'])
        ->fillForm(['kawasans' => [$vireya->id]])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($promo->fresh()->kawasans->pluck('slug')->all())->toBe(['vireya']);
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
