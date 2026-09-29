<?php

use App\Enums\PromoPlacement;
use App\Filament\Resources\Clusters\Pages\ListClusters;
use App\Filament\Resources\Promos\Pages\EditPromo;
use App\Models\Cluster;
use App\Models\Kawasan;
use App\Models\Promo;
use App\Models\User;
use App\Settings\GlobalSettings;
use App\Support\PageCache;
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

it('menampilkan promo aktif di Detail Rumah tanpa catatan internal, termasuk promo tanpa daftar benefit', function () {
    $this->get('/properti/izzi')->assertInertia(fn (Assert $page) => $page->where('promo', null));

    publishPromo('Promo IZZI');

    $response = $this->get('/properti/izzi')->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->where('promo.title', 'Promo rumah ini')
        ->has('promo.items', 1)
        ->where('promo.items.0.title', 'Promo IZZI')
        ->where('promo.items.0.description', 'Diskon hingga 18,5%, subsidi DP 10%, bebas biaya KPR, dan free kanopi.'));

    expect(json_encode($response->inertiaProps()))->not->toContain('bsd-city.com (situs agen)');
});

it('menyembunyikan promo yang belum mulai atau sudah lewat ends_at', function () {
    publishPromo('Promo Caelus September');
    // Data import terbit "sekarang"; mundurkan supaya halaman tetap terbit saat waktu dimundurkan.
    Cluster::query()->update(['published_at' => '2026-01-01 00:00:00']);
    Kawasan::query()->update(['published_at' => '2026-01-01 00:00:00']);

    $this->travelTo('2026-09-15 10:00');
    $this->get('/properti/caelus')->assertInertia(fn (Assert $page) => $page->has('promo.items', 1)->where('promo.items.0.period', 'Berlaku s.d. 30 September 2026'));

    // Masih berlaku sepanjang hari terakhir.
    $this->travelTo('2026-09-30 22:00');
    $this->get('/properti/caelus')->assertInertia(fn (Assert $page) => $page->has('promo.items', 1));

    $this->travelTo('2026-10-01 00:00:01');
    $this->get('/properti/caelus')->assertInertia(fn (Assert $page) => $page->where('promo', null));
    $this->get('/properti/kawasan/greenwich-park')->assertInertia(fn (Assert $page) => $page->where('promos', null));

    $this->travelTo('2026-08-31 23:00');
    $this->get('/properti/caelus')->assertInertia(fn (Assert $page) => $page->where('promo', null));
});

it('menampilkan promo kawasan dan promo cluster di Detail Kawasan dengan nama cluster', function () {
    $this->get('/properti/kawasan/vireya')->assertInertia(fn (Assert $page) => $page->where('promos', null));

    publishPromo('Promo Lynelle');
    $direct = Promo::query()->create(['title' => 'Promo kawasan Vireya', 'placement' => PromoPlacement::Detail, 'description' => 'Gratis biaya AJB.', 'is_published' => true]);
    $direct->kawasans()->attach(Kawasan::where('slug', 'vireya')->value('id'));
    // Promo kawasan lain & promo cluster di kawasan lain tidak ikut.
    publishPromo('Promo Castilo');

    $this->get('/properti/kawasan/vireya')->assertInertia(fn (Assert $page) => $page
        ->where('promos.title', 'Promo di kawasan ini')
        ->has('promos.items', 2)
        ->where('promos.items', fn ($items) => collect($items)->pluck('title')->sort()->values()->all() === ['Promo Lynelle', 'Promo kawasan Vireya']
            && collect($items)->firstWhere('title', 'Promo Lynelle')['clusters'] === [['name' => 'Lynelle', 'url' => '/properti/lynelle']]
            && collect($items)->firstWhere('title', 'Promo kawasan Vireya')['clusters'] === []));
});

it('memberi badge Promo otomatis di kartu cluster selama promo aktif', function () {
    $card = fn () => collect($this->get('/properti')->inertiaProps('clusters.data'))->firstWhere('name', 'IZZI');

    expect($card()['badge'])->toBe('Baru');

    publishPromo('Promo IZZI', ['ends_at' => now()->addDay()]);
    expect($card()['badge'])->toBe('Promo');

    $this->travel(2)->days();
    expect($card()['badge'])->toBe('Baru');
});

it('membatasi umur cache halaman sampai promo berikutnya mulai atau berakhir', function () {
    config(['site.page_cache.ttl' => 3600]);
    expect(PageCache::ttl())->toBe(3600);

    publishPromo('Promo IZZI', ['ends_at' => now()->addMinutes(10)]);

    expect(PageCache::ttl())->toBeLessThanOrEqual(600)->toBeGreaterThan(0);
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
