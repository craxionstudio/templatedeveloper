<?php

use App\Filament\Resources\Clusters\Pages\CreateCluster;
use App\Models\Cluster;
use App\Models\HouseType;
use App\Models\Kawasan;
use App\Models\User;
use App\Settings\GlobalSettings;
use App\Settings\ListingPageSettings;
use App\Support\Rupiah;
use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia as Assert;
use Livewire\Livewire;

/*
 * Status penjualan opsional & cluster tanpa tipe rumah / harga:
 * tidak ada badge status kosong, harga "Hubungi kami untuk harga", tidak ada Offer tanpa harga,
 * dan tidak ada error di halaman mana pun.
 */

beforeEach(function () {
    $this->seed();
    $this->kawasan = Kawasan::where('slug', 'arunika-garden')->firstOrFail();

    $global = app(GlobalSettings::class);
    $global->contact = [...$global->contact, 'whatsapp' => '0812-3456-7890'];
    $global->save();

    // Semua cluster dalam satu halaman supaya urutan bisa dicek utuh.
    $listing = app(ListingPageSettings::class);
    $listing->cluster_view = [...$listing->cluster_view, 'per_page' => 50];
    $listing->save();
});

/** Cluster terpublikasi tanpa tipe rumah dan tanpa status. */
function clusterWithoutTypes(array $attributes = []): Cluster
{
    $cluster = Cluster::factory()->create([
        'name' => 'Nusa Indah',
        'slug' => 'nusa-indah',
        'status' => null,
        ...$attributes,
    ]);
    $cluster->refreshAggregates();

    return $cluster->fresh();
}

/** Prop kartu dari daftar kartu berdasarkan nama. */
function cardNamed(TestResponse $response, string $key, string $name): ?array
{
    return collect(data_get($response->inertiaProps(), $key))->firstWhere('name', $name);
}

it('menyimpan cluster tanpa status dari form admin', function () {
    $this->actingAs(User::where('email', 'admin@example.com')->first());

    Livewire::test(CreateCluster::class)
        ->fillForm(['name' => 'Tanpa Status', 'slug' => 'tanpa-status', 'kawasan_id' => null, 'status' => null])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Cluster::where('slug', 'tanpa-status')->first()->status)->toBeNull();
});

it('tidak mengirim status ke Detail Rumah kalau status kosong', function () {
    clusterWithoutTypes();

    $this->get('/properti/nusa-indah')->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Cluster/Show')
        ->where('cluster.status', null));

    // Cluster dengan status tetap membawa label status.
    $this->get('/properti/vega-garden')->assertInertia(fn (Assert $page) => $page->where('cluster.status', 'Ready stock'));
});

it('merender Detail Rumah cluster tanpa tipe: tanpa tipe, WA & form tetap ada, tanpa Offer', function () {
    clusterWithoutTypes(['kawasan_id' => $this->kawasan->id]);

    $response = $this->get('/properti/nusa-indah')->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Cluster/Show')
        ->has('types', 0)
        ->where('selectedType', null)
        ->where('cluster.whatsappUrl', fn (string $url) => str_starts_with($url, 'https://wa.me/') && ! str_contains($url, '%7Btype%7D') && ! str_contains($url, 'tipe'))
        ->has('form.submitLabel'));

    $residence = ofType(jsonLd($response), 'Residence');
    expect($residence['name'])->toBe('Nusa Indah')
        ->and($residence)->not->toHaveKey('containsPlace')
        ->and(json_encode(jsonLd($response)))->not->toContain('Offer');
});

it('tidak membuat Offer untuk tipe tanpa harga dan menyembunyikan data kosong', function () {
    $cluster = clusterWithoutTypes();
    HouseType::factory()->for($cluster)->create([
        'name' => 'Kenanga', 'slug' => 'kenanga', 'price_from' => null, 'installment_from' => null,
        'land_area' => null, 'building_area' => null, 'bedrooms' => 0, 'bathrooms' => 0, 'carports' => 0,
    ]);

    $response = $this->get('/properti/nusa-indah')->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->has('types', 1)
        ->where('types.0.hasData', false)
        ->where('types.0.price', null)
        ->where('types.0.installment', null)
        ->where('types.0.landArea', null)
        ->where('types.0.bedrooms', null));

    $place = ofType(jsonLd($response), 'Residence')['containsPlace'][0];
    expect($place['name'])->toBe('Nusa Indah — Kenanga')
        ->and($place)->not->toHaveKey('offers')
        ->and($place)->not->toHaveKey('numberOfBedrooms');

    expect($cluster->fresh())->price_min->toBeNull()->land_area_min->toBeNull()->bedrooms_min->toBeNull();
});

it('menampilkan kartu tanpa harga (bukan Rp 0) di listing, kawasan, dan beranda', function () {
    clusterWithoutTypes(['kawasan_id' => $this->kawasan->id]);

    $listing = $this->get('/properti')->assertOk();
    expect(cardNamed($listing, 'clusters.data', 'Nusa Indah'))
        ->toMatchArray(['price' => null, 'installment' => null, 'typesCount' => 0, 'types' => [], 'landArea' => null, 'bedrooms' => null])
        ->and(json_encode($listing->inertiaProps()))->not->toContain('Rp 0');

    $kawasan = $this->get('/properti/kawasan/arunika-garden')->assertOk();
    expect(cardNamed($kawasan, 'clusters.items', 'Nusa Indah'))->toMatchArray(['price' => null, 'typesCount' => 0])
        ->and(json_encode($kawasan->inertiaProps()))->not->toContain('Rp 0');

    $this->get('/properti/kawasan')->assertOk();
    $this->get('/')->assertOk();
    $this->get('/properti/vega-garden')->assertOk();
    $this->get('/sitemap-properti.xml')->assertOk()->assertSee('/properti/nusa-indah', false);
});

it('menampilkan "Hubungi kami untuk harga" di kawasan yang semua clusternya belum punya harga', function () {
    $kawasan = Kawasan::factory()->create(['name' => 'Arunika Sky', 'slug' => 'arunika-sky']);
    clusterWithoutTypes(['kawasan_id' => $kawasan->id]);

    $this->get('/properti/kawasan/arunika-sky')->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Kawasan/Show')
        ->where('hero.stats', fn ($stats) => collect($stats)->contains('value', 'Hubungi kami untuk harga')));

    $card = cardNamed($this->get('/properti/kawasan')->assertOk(), 'kawasans', 'Arunika Sky');
    expect($card['priceFrom'] ?? null)->toBeNull();
});

it('mengurutkan cluster berharga di atas cluster tanpa harga di listing', function (string $query) {
    clusterWithoutTypes(['published_at' => now()]);

    $prices = collect($this->get('/properti'.$query)->inertiaProps('clusters.data'))->pluck('price');

    // Semua harga non-null lebih dulu, lalu null.
    expect($prices)->toHaveCount(10)
        ->and($prices->last())->toBeNull()
        ->and($prices->slice(0, -1)->every(fn ($price) => $price !== null))->toBeTrue();
})->with(['' => [''], 'harga terendah' => ['?urut=harga-terendah'], 'harga tertinggi' => ['?urut=harga-tertinggi']]);

it('mengurutkan cluster berharga lebih dulu di Detail Kawasan', function () {
    // sort_order 0 < cluster seeder (1..3): tanpa aturan harga-dulu, cluster ini akan tampil pertama.
    clusterWithoutTypes(['kawasan_id' => $this->kawasan->id, 'sort_order' => 0]);

    $prices = collect($this->get('/properti/kawasan/arunika-garden')->inertiaProps('clusters.items'))->pluck('price');

    expect($prices->last())->toBeNull()->and($prices->first())->not->toBeNull();
});

it('memformat harga 0 / kosong sebagai null', function () {
    expect(Rupiah::short(0))->toBeNull()
        ->and(Rupiah::short(null))->toBeNull()
        ->and(Rupiah::range(0, 0))->toBeNull()
        ->and(Rupiah::range(0, 850_000_000))->toBeNull()
        ->and(Rupiah::full(0))->toBeNull()
        ->and(Rupiah::short(580_000_000))->toBe('Rp 580 jt');
});

it('merender cluster tanpa tipe lewat SSR tanpa error', function () {
    $ssr = parse_url((string) config('inertia.ssr.url', 'http://127.0.0.1:13714'));
    $socket = @fsockopen($ssr['host'] ?? '127.0.0.1', $ssr['port'] ?? 13714, $errno, $errstr, 0.5);

    if (! $socket) {
        $this->markTestSkipped('Server SSR tidak berjalan (php artisan inertia:start-ssr).');
    }

    fclose($socket);
    clusterWithoutTypes(['kawasan_id' => $this->kawasan->id]);

    $detail = $this->get('/properti/nusa-indah')->assertOk()->getContent();
    expect(substr_count($detail, '<h1'))->toBe(1)
        ->and($detail)->toContain('Hubungi kami untuk harga')
        ->and($detail)->toContain('https://wa.me/')
        ->and($detail)->toContain('<form')
        ->and($detail)->not->toContain('Rp 0');

    foreach (['/properti', '/properti/kawasan/arunika-garden', '/'] as $path) {
        expect($this->get($path)->assertOk()->getContent())->not->toContain('Rp 0');
    }

    expect($this->get('/properti')->getContent())->toContain('Hubungi kami untuk harga');
});
