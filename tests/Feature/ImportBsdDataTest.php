<?php

use App\Console\Commands\ImportBsdData;
use App\Enums\ClusterStatus;
use App\Filament\Resources\Clusters\Pages\EditCluster;
use App\Filament\Resources\Clusters\Pages\ListClusters;
use App\Filament\Resources\Clusters\RelationManagers\HouseTypesRelationManager;
use App\Filament\Resources\Kawasans\Pages\EditKawasan;
use App\Models\Article;
use App\Models\Cluster;
use App\Models\HouseType;
use App\Models\Kawasan;
use App\Models\SeoMeta;
use App\Models\User;
use App\Settings\ClusterDetailPageSettings;
use App\Settings\GlobalSettings;
use App\Settings\ListingPageSettings;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Livewire\Livewire;

/*
 * php artisan import:bsd-data (docs/data/bsd-city-data.json).
 */

beforeEach(function () {
    $this->seed();
});

function importBsd(bool $fresh = true): void
{
    test()->artisan('import:bsd-data', $fresh ? ['--fresh' => true] : [])->assertSuccessful();
}

function bsdType(string $cluster, ?string $name): HouseType
{
    return HouseType::query()->whereHas('cluster', fn ($q) => $q->where('slug', $cluster))->where('name', $name)->firstOrFail();
}

it('mengganti data dummy dengan data BSD City tanpa menyentuh user, artikel, dan settings lain', function () {
    $articles = Article::count();
    $brand = app(GlobalSettings::class)->identity['brand_name'];

    importBsd();

    expect(Kawasan::count())->toBe(23)
        ->and(Cluster::count())->toBe(144)
        ->and(HouseType::count())->toBe(87)
        ->and(Cluster::withTrashed()->where('slug', 'vega-garden')->exists())->toBeFalse()
        ->and(Kawasan::withTrashed()->where('slug', 'arunika-garden')->exists())->toBeFalse()
        ->and(Article::published()->count())->toBe(0)
        ->and(User::where('email', 'admin@example.com')->exists())->toBeTrue()
        ->and(Article::count())->toBe($articles)
        ->and(app(GlobalSettings::class)->identity['brand_name'])->toBe($brand);

    $this->get('/properti/vega-garden')->assertNotFound();
});

it('aman dijalankan berkali-kali tanpa duplikat', function () {
    importBsd();
    importBsd(fresh: false);
    importBsd(fresh: false);

    expect(Kawasan::count())->toBe(23)
        ->and(Cluster::count())->toBe(144)
        ->and(HouseType::count())->toBe(87)
        ->and(SeoMeta::count())->toBe(23 + 144);
});

it('mempertahankan isian admin saat import ulang tanpa --fresh', function () {
    importBsd();

    // Lakewood: status & alamat null di JSON.
    $cluster = Cluster::where('slug', 'lakewood')->firstOrFail();
    $checklist = $cluster->perlu_dilengkapi;
    $checklist[0]['selesai'] = true;
    $cluster->update(['status' => ClusterStatus::Inden, 'perlu_dilengkapi' => $checklist, 'address' => 'Jl. Lakewood Raya']);

    importBsd(fresh: false);

    expect($cluster->fresh())
        ->status->toBe(ClusterStatus::Inden)
        ->address->toBe('Jl. Lakewood Raya')
        ->perlu_dilengkapi_count->toBe(count($checklist) - 1)
        ->and($cluster->fresh()->perlu_dilengkapi[0])->toBe(['item' => $checklist[0]['item'], 'selesai' => true]);
});

it('mengisi kawasan, cluster mandiri, status kosong, dan field null tetap kosong', function () {
    importBsd();

    expect(Cluster::where('slug', 'izzi')->first())
        ->kawasan_id->toBeNull()
        ->status->toBeNull()
        ->prioritas->toBe(5)
        ->and(Cluster::where('slug', 'monard-of-the-armont')->first()->kawasan->slug)->toBe('the-armont')
        ->and(Cluster::whereNotNull('status')->count())->toBe(0)
        ->and(bsdType('izzi', 'Tipe 6'))->price_from->toBeNull()->installment_from->toBeNull()
        ->and(Cluster::where('slug', 'lakewood')->first())->house_types_count->toBe(0)->price_min->toBeNull()->address->toBeNull();

    $vireya = Kawasan::where('slug', 'vireya')->first();
    expect((float) $vireya->area_ha)->toBe(50.0)
        ->and($vireya->opened_year)->toBe(2025)
        ->and($vireya->access)->toContain('Commuter Line via Stasiun Rawa Buntu')
        ->and(collect($vireya->facilities)->pluck('title'))->toContain('Clubhouse')
        ->and($vireya->description)->toStartWith('<p>Vireya dibuka tahun 2025');
});

it('menyimpan angka terkecil dari rentang dan menulis nilai aslinya di catatan internal', function () {
    importBsd();

    $premium = bsdType('island-villa', 'The Premium');
    expect($premium)->land_area->toBe(1000)->bedrooms->toBe(5)->bathrooms->toBe(5)
        ->and($premium->catatan_internal)->toContain('LT 1000-1788 m²')->toContain('Kamar tidur 5-6')->toContain('Kamar mandi 5-6');

    // "3+1": simpan 3, "+1" tetap tercatat (catatan data sudah menulis "Kamar mandi 3+1", tidak diulang).
    $tipe7 = bsdType('vyorelle-at-vireya', 'Tipe 7');
    expect($tipe7->bathrooms)->toBe(3)
        ->and(substr_count($tipe7->catatan_internal, 'Kamar mandi 3+1'))->toBe(1);

    expect(bsdType('namee-by-eonna', 'Tipe 10')->bathrooms)->toBe(5);

    $caelus = HouseType::whereHas('cluster', fn ($q) => $q->where('slug', 'caelus'))->first();
    expect($caelus)->name->toBeNull()->floors->toBe(2)
        ->and($caelus->catatan_internal)->toContain('Lantai 2,5');
});

it('bentuk konversi angka & catatan', function () {
    $notes = [];

    expect(ImportBsdData::number('1000-1788', 'LT', 'm²', $notes))->toBe(1000)
        ->and(ImportBsdData::number('3+1', 'Kamar mandi', null, $notes))->toBe(3)
        ->and(ImportBsdData::number('5+1+1', 'Kamar mandi', null, $notes))->toBe(5)
        ->and(ImportBsdData::number(2.5, 'Lantai', null, $notes))->toBe(2)
        ->and(ImportBsdData::number(120, 'LB', 'm²', $notes))->toBe(120)
        ->and(ImportBsdData::number(null, 'LB', 'm²', $notes))->toBeNull()
        ->and($notes)->toBe(['LT 1000-1788 m²', 'Kamar mandi 3+1', 'Kamar mandi 5+1+1', 'Lantai 2,5'])
        ->and(ImportBsdData::notes('Kamar mandi 3+1', ['Kamar mandi 3+1']))->toBe('Kamar mandi 3+1')
        ->and(ImportBsdData::notes(null, []))->toBeNull();
});

it('mengisi SEO kawasan, cluster, dan halaman', function () {
    importBsd();

    expect(Cluster::where('slug', 'monard-of-the-armont')->first()->seo->meta_title)->not->toBeEmpty()
        ->and(app(ListingPageSettings::class)->seo_cluster['meta_title'])->toBe('Daftar Cluster Rumah di BSD City')
        ->and(app(ClusterDetailPageSettings::class)->seo['title_pattern'])->toBe('{name} BSD City: Harga & Tipe Rumah');

    $this->get('/properti')->assertInertia(fn (Assert $page) => $page->where('meta.title', 'Daftar Cluster Rumah di BSD City'));
    $this->get('/properti/kawasan/vireya')->assertInertia(fn (Assert $page) => $page->where('meta.title', 'Vireya BSD City: Cluster, Tipe & Harga Rumah'));
});

it('merender halaman hasil import tanpa error', function (string $path) {
    importBsd();

    $this->get($path)->assertOk();
})->with([
    '/', '/properti', '/properti/kawasan', '/properti/kawasan/vireya', '/properti/kawasan/navapark',
    '/properti/monard-of-the-armont', '/properti/island-villa', '/properti/lynelle', '/properti/izzi',
    '/properti/lakewood', '/properti/caelus', '/properti/amata', '/sitemap-properti.xml',
]);

it('menampilkan cluster tanpa harga & tipe tanpa nama dengan benar', function () {
    importBsd();

    // Cluster tanpa tipe: tanpa harga di kartu, tanpa Offer di JSON-LD.
    $lakewood = $this->get('/properti/lakewood')->assertOk();
    $lakewood->assertInertia(fn (Assert $page) => $page->has('types', 0)->where('cluster.status', null));
    expect(json_encode(jsonLd($lakewood)))->not->toContain('Offer');

    // Tipe tanpa nama: harga tetap tampil, nama null, tidak jadi chip di kartu.
    $this->get('/properti/amata')->assertInertia(fn (Assert $page) => $page
        ->has('types', 2)
        ->where('types.0.name', null)
        ->where('types.0.price', 'Rp 5,6 M'));

    $card = collect($this->get('/properti/kawasan/navapark')->inertiaProps('clusters.items'))->firstWhere('name', 'Island Villa');
    expect($card)->toMatchArray(['types' => ['The Premium', 'The Signature'], 'typesCount' => 2]);
});

it('tidak menampilkan catatan internal, checklist, dan sisa unit di website', function () {
    importBsd();
    $cluster = Cluster::where('slug', 'monard-of-the-armont')->first();
    $cluster->houseTypes()->first()->update(['units_available' => 7]);

    $response = $this->get('/properti/monard-of-the-armont')->assertOk();
    $props = json_encode($response->inertiaProps(), JSON_UNESCAPED_UNICODE);

    expect($props)->not->toContain('pricelist agen Jun 2026')
        ->not->toContain($cluster->perlu_dilengkapi[0]['item'])
        ->not->toContain('unitsAvailable')
        ->not->toContain('prioritas');
});

it('menampilkan badge & filter "Belum lengkap" dan prioritas di tabel cluster admin', function () {
    importBsd();
    $this->actingAs(User::where('email', 'admin@example.com')->first());

    $monard = Cluster::where('slug', 'monard-of-the-armont')->first();
    $complete = Cluster::where('slug', 'lynelle')->first();
    $complete->update(['perlu_dilengkapi' => collect($complete->perlu_dilengkapi)->map(fn ($i) => [...$i, 'selesai' => true])->all()]);

    Livewire::test(ListClusters::class)
        ->assertTableColumnExists('perlu_dilengkapi_count')
        ->assertTableColumnFormattedStateSet('perlu_dilengkapi_count', 'Belum lengkap (6)', $monard)
        ->assertTableColumnFormattedStateSet('perlu_dilengkapi_count', 'Lengkap', $complete)
        ->sortTable('prioritas')
        ->filterTable('belum_lengkap', true)
        ->assertCanSeeTableRecords([$monard])
        ->assertCanNotSeeTableRecords([$complete]);
});

it('bisa membuka dan menyimpan cluster & kawasan hasil import di admin', function () {
    importBsd();
    $this->actingAs(User::where('email', 'admin@example.com')->first());

    Storage::fake('public');
    $cluster = withClusterPhoto(Cluster::where('slug', 'island-villa')->first());
    Livewire::test(EditCluster::class, ['record' => $cluster->getRouteKey()])
        ->assertSchemaStateSet(['prioritas' => 2, 'catatan_internal' => $cluster->catatan_internal])
        ->fillForm(['catatan_internal' => 'Dicek tim marketing'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($cluster->fresh()->catatan_internal)->toBe('Dicek tim marketing');

    $kawasan = Kawasan::where('slug', 'vireya')->first();
    Livewire::test(EditKawasan::class, ['record' => $kawasan->getRouteKey()])
        ->call('save')
        ->assertHasNoFormErrors();

    // Tipe tanpa nama tampil di relation manager tipe rumah.
    $caelus = Cluster::where('slug', 'caelus')->first();
    Livewire::test(HouseTypesRelationManager::class, ['ownerRecord' => $caelus, 'pageClass' => EditCluster::class])
        ->assertOk()
        ->assertCanSeeTableRecords($caelus->houseTypes);
});
