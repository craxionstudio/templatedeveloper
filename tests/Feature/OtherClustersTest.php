<?php

use App\Enums\ClusterDisplay;
use App\Filament\Resources\Clusters\ClusterResource;
use App\Filament\Resources\Clusters\Pages\EditCluster;
use App\Filament\Resources\Clusters\Pages\ListClusters;
use App\Filament\Resources\Kawasans\KawasanResource;
use App\Filament\Resources\Kawasans\Pages\EditKawasan;
use App\Filament\Resources\Kawasans\Pages\ListKawasans;
use App\Filament\Resources\OtherClusters\OtherClusterResource;
use App\Filament\Resources\OtherClusters\Pages\CreateOtherCluster;
use App\Filament\Resources\OtherClusters\Pages\ListOtherClusters;
use App\Filament\Resources\OtherKawasans\OtherKawasanResource;
use App\Filament\Resources\OtherKawasans\Pages\CreateOtherKawasan;
use App\Filament\Resources\OtherKawasans\Pages\EditOtherKawasan;
use App\Filament\Resources\OtherKawasans\Pages\ListOtherKawasans;
use App\Models\Benefit;
use App\Models\Cluster;
use App\Models\Kawasan;
use App\Models\User;
use App\Support\PageCache;
use App\Support\Sitemaps;
use Illuminate\Support\Facades\Schema;
use Inertia\Testing\AssertableInertia as Assert;
use Livewire\Livewire;

/*
 * Update 3: hanya cluster "halaman" punya halaman sendiri; cluster "daftar" hanya nama di
 * /properti/cluster-lainnya. Kawasan tanpa halaman sendiri hanya jadi judul grup di sana.
 */

beforeEach(function () {
    $this->seed();
    Sitemaps::flush();
});

function listOnly(string ...$slugs): void
{
    Cluster::query()->whereIn('slug', $slugs)->update(['tampil_sebagai' => ClusterDisplay::Daftar->value]);
    // Update lewat query tidak memicu event model: buang cache layout/halaman seperti import.
    PageCache::flush();
}

it('menambah kolom tampil_sebagai (default halaman) dan punya_halaman (default true)', function () {
    expect(Schema::hasColumns('clusters', ['tampil_sebagai']))->toBeTrue()
        ->and(Schema::hasColumns('kawasans', ['punya_halaman']))->toBeTrue()
        ->and(Cluster::query()->where('slug', 'vega-garden')->first()->tampil_sebagai)->toBe(ClusterDisplay::Halaman)
        ->and(Kawasan::query()->where('slug', 'arunika-garden')->first()->punya_halaman)->toBeTrue();
});

it('mengimpor cluster_tampilan dan kawasan_tampilan dari bsd-city-update-3.json', function () {
    $this->artisan('import:bsd-data', ['--fresh' => true, '--force' => true])->assertSuccessful();
    $this->artisan('import:bsd-update', ['path' => 'docs/data/bsd-city-update-3.json'])->assertSuccessful();

    $data = json_decode((string) file_get_contents(base_path('docs/data/bsd-city-update-3.json')), true);
    $pages = collect($data['cluster_tampilan'])->where('tampil_sebagai', 'halaman')->pluck('slug');
    $hiddenKawasans = collect($data['kawasan_tampilan'])->where('punya_halaman', false)->pluck('slug');

    expect(Cluster::query()->where('tampil_sebagai', 'halaman')->pluck('slug')->sort()->values()->all())->toBe($pages->sort()->values()->all())
        ->and(Cluster::query()->where('tampil_sebagai', 'daftar')->count())->toBe(count($data['cluster_tampilan']) - $pages->count())
        ->and(Kawasan::query()->where('punya_halaman', false)->pluck('slug')->sort()->values()->all())->toBe($hiddenKawasans->sort()->values()->all());

    // Aman diulang.
    $this->artisan('import:bsd-update', ['path' => 'docs/data/bsd-city-update-3.json'])->assertSuccessful();
    expect(Cluster::query()->where('tampil_sebagai', 'halaman')->count())->toBe($pages->count());
});

it('menyembunyikan cluster daftar dari /properti, filter, kartu, beranda, dan sitemap', function () {
    listOnly('vega-garden', 'kalea-townhouse');

    $this->get('/properti')->assertInertia(fn (Assert $page) => $page
        ->where('clusters.data', fn ($items) => ! collect($items)->pluck('name')->intersect(['Vega Garden', 'Kalea Townhouse'])->count()));
    $this->get('/properti?kawasan=arunika-garden')->assertInertia(fn (Assert $page) => $page
        ->where('clusters.data', fn ($items) => ! collect($items)->pluck('name')->contains('Vega Garden')));
    $this->get('/properti/kawasan/arunika-garden')->assertInertia(fn (Assert $page) => $page
        ->where('clusters.items', fn ($items) => ! collect($items)->pluck('name')->contains('Vega Garden')));

    $html = $this->get('/')->getContent().$this->get('/properti/lyra-residence')->getContent();
    expect($html)->not->toContain('/properti/vega-garden');

    $sitemap = $this->get('/sitemap-properti.xml')->getContent();
    expect($sitemap)->not->toContain('/properti/vega-garden')
        ->not->toContain('/properti/kalea-townhouse')
        ->toContain(url('/properti/cluster-lainnya'));
});

it('mengarahkan URL lama cluster daftar (301) ke grup kawasannya di Cluster Lainnya', function () {
    listOnly('vega-garden', 'kalea-townhouse');

    $this->get('/properti/vega-garden')->assertStatus(301)->assertRedirect('/properti/cluster-lainnya#arunika-garden');
    $this->get('/properti/kalea-townhouse')->assertStatus(301)->assertRedirect('/properti/cluster-lainnya#lainnya');
    $this->get('/properti/tidak-ada')->assertNotFound();
});

it('menyembunyikan kawasan tanpa halaman sendiri dan mengarahkan URL lamanya (301)', function () {
    Kawasan::query()->where('slug', 'arunika-hills')->update(['punya_halaman' => false]);

    $this->get('/properti/kawasan/arunika-hills')->assertStatus(301)->assertRedirect('/properti/cluster-lainnya#arunika-hills');
    $this->get('/properti/kawasan')->assertInertia(fn (Assert $page) => $page
        ->where('kawasans', fn ($items) => ! collect($items)->pluck('name')->contains('Arunika Hills')));
    $this->get('/')->assertInertia(fn (Assert $page) => $page
        ->where('site.footer.columns.0.links', fn ($links) => ! collect($links)->pluck('label')->contains('Arunika Hills')));
    expect($this->get('/sitemap-properti.xml')->getContent())->not->toContain('/properti/kawasan/arunika-hills');

    // Cluster berhalaman di kawasan itu tetap punya halaman; link kawasannya ke grup di Cluster Lainnya.
    $this->get('/properti/kirana-hills')->assertOk()->assertInertia(fn (Assert $page) => $page
        ->where('cluster.kawasan.url', '/properti/cluster-lainnya#arunika-hills'));

    // Kawasan yang semua clusternya "daftar" juga diarahkan.
    listOnly('sora-terrace', 'sora-terrace-ii');
    $this->get('/properti/kawasan/arunika-lakeside')->assertStatus(301)->assertRedirect('/properti/cluster-lainnya#arunika-lakeside');
});

it('menampilkan Cluster Lainnya: nama saja, per kawasan urut abjad, tanpa kawasan di grup Lainnya', function () {
    listOnly('vega-garden', 'orion-park', 'sora-terrace', 'kalea-townhouse', 'hana-residence');

    $this->get('/properti/cluster-lainnya')->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Properti/Lainnya')
        ->where('header.title', 'Cluster Lainnya di BSD City')
        ->where('header.description', 'Kawasan dan cluster yang telah tumbuh bersama BSD City.')
        ->where('header.stats', [])
        ->where('meta.title', 'Cluster Lainnya di BSD City | BSD City')
        ->where('meta.canonical', url('/properti/cluster-lainnya'))
        ->where('breadcrumbs', fn ($crumbs) => collect($crumbs)->pluck('label')->all() === ['Beranda', 'Properti', 'Cluster Lainnya'])
        ->where('groups', [
            ['id' => 'arunika-garden', 'title' => 'Arunika Garden', 'clusters' => ['Orion Park', 'Vega Garden']],
            ['id' => 'arunika-lakeside', 'title' => 'Arunika Lakeside', 'clusters' => ['Sora Terrace']],
            ['id' => 'lainnya', 'title' => 'Lainnya', 'clusters' => ['Hana Residence', 'Kalea Townhouse']],
        ]));
});

it('menampilkan section "Cluster Lainnya" (chip) setelah "Cluster {kawasan}" di Detail Kawasan, tersembunyi kalau kosong', function () {
    $this->get('/properti/kawasan/arunika-garden')->assertInertia(fn (Assert $page) => $page->where('otherClusters', null));

    listOnly('vega-garden', 'orion-park');

    $this->get('/properti/kawasan/arunika-garden')->assertInertia(fn (Assert $page) => $page
        ->where('otherClusters.title', 'Cluster Lainnya')
        ->where('otherClusters.names', ['Orion Park', 'Vega Garden'])
        ->has('clusters.items', 1));
});

it('menampilkan ajakan ke Cluster Lainnya di /properti dan link di footer hanya kalau ada cluster daftar', function () {
    $this->get('/properti')->assertInertia(fn (Assert $page) => $page
        ->where('others', null)
        ->where('site.footer.columns.0.links', fn ($links) => ! collect($links)->pluck('label')->contains('Cluster lainnya')));

    listOnly('vega-garden');

    $this->get('/properti')->assertInertia(fn (Assert $page) => $page
        ->where('others', ['text' => 'Masih banyak cluster lain di BSD City.', 'label' => 'Lihat cluster lainnya', 'url' => '/properti/cluster-lainnya'])
        ->where('site.footer.columns.0.links', fn ($links) => collect($links)->contains(['label' => 'Cluster lainnya', 'url' => '/properti/cluster-lainnya'])));
});

it('tidak menampilkan jumlah cluster/kawasan dan memakai judul baru di /properti', function () {
    $this->get('/properti')->assertInertia(fn (Assert $page) => $page
        ->where('header.title', 'Temukan Rumah Anda di BSD City')
        ->where('header.stats', fn ($stats) => collect($stats)->pluck('label')->all() === ['Harga mulai'])
        ->where('toggle.items', fn ($items) => collect($items)->every(fn ($item) => ! array_key_exists('count', $item)))
        ->missing('result'));

    $this->get('/properti/kawasan')->assertInertia(fn (Assert $page) => $page
        ->missing('summary')
        ->where('kawasans', fn ($items) => collect($items)->every(fn ($k) => ! array_key_exists('clustersCount', $k))));

    $this->get('/properti/kawasan/arunika-garden')->assertInertia(fn (Assert $page) => $page
        ->where('hero.stats', fn ($stats) => ! collect($stats)->pluck('label')->contains('Cluster'))
        ->where('clusters.title', 'Cluster Arunika Garden'));
});

it('tidak menyebut istilah teknis atau jumlah cluster/kawasan di HTML publik (SSR)', function (string $path) {
    $ssr = parse_url((string) config('inertia.ssr.url', 'http://127.0.0.1:13714'));
    $socket = @fsockopen($ssr['host'] ?? '127.0.0.1', $ssr['port'] ?? 13714, $errno, $errstr, 0.5);

    if (! $socket) {
        $this->markTestSkipped('Server SSR tidak berjalan (php artisan inertia:start-ssr).');
    }

    fclose($socket);
    listOnly('vega-garden', 'kalea-townhouse');

    $html = $this->get($path)->assertOk()->getContent();
    $body = strip_tags(explode('<body', $html, 2)[1] ?? $html);
    $text = preg_replace('/\s+/', ' ', html_entity_decode(explode('data-page=', $body)[0]));

    expect($text)->not->toMatch('/\b\d+\s+(cluster|kawasan)\b/i')
        ->not->toMatch('/\b(portofolio|halaman lengkap|tampil sebagai)\b/i');
})->with(['/', '/properti', '/properti/kawasan', '/properti/kawasan/arunika-garden', '/properti/cluster-lainnya']);

it('memisahkan menu admin Cluster / Cluster Lainnya berdasarkan tampil_sebagai, dengan action pindah & aktifkan', function () {
    $this->actingAs(User::query()->where('email', 'admin@example.com')->firstOrFail());
    listOnly('orion-park');
    $vega = withClusterPhoto(Cluster::query()->where('slug', 'vega-garden')->firstOrFail());
    $orion = Cluster::query()->where('slug', 'orion-park')->firstOrFail();
    $vega->clusterBenefits()->create(['benefit_id' => Benefit::query()->where('slug', 'tanpa-dp')->value('id'), 'urutan' => 1]);
    $types = $vega->houseTypes()->count();

    // Cluster: hanya "halaman"; form lengkap tanpa pilihan "Tampil sebagai", kolom/filter itu juga tidak ada.
    Livewire::test(ListClusters::class)
        ->assertCanSeeTableRecords([$vega])
        ->assertCanNotSeeTableRecords([$orion])
        ->assertTableColumnDoesNotExist('tampil_sebagai');
    expect(collect(Livewire::test(ListClusters::class)->instance()->getTable()->getFilters())->keys())->not->toContain('tampil_sebagai');
    Livewire::test(EditCluster::class, ['record' => $vega->getRouteKey()])
        ->assertFormFieldDoesNotExist('tampil_sebagai')
        ->assertFormFieldExists('galleryItems');
    $this->get(ClusterResource::getUrl('edit', ['record' => $orion]))->assertNotFound();

    // Cluster Lainnya: hanya "daftar".
    Livewire::test(ListOtherClusters::class)
        ->assertCanSeeTableRecords([$orion])
        ->assertCanNotSeeTableRecords([$vega])
        ->assertTableColumnExists('kawasan.name')
        ->filterTable('kawasan', 'lainnya')
        ->assertCanNotSeeTableRecords([$orion]);

    // Pindahkan ke Cluster Lainnya (konfirmasi): data lengkap tetap tersimpan.
    Livewire::test(EditCluster::class, ['record' => $vega->getRouteKey()])
        ->callAction('pindahKeLainnya')
        ->assertRedirect(OtherClusterResource::getUrl('index'));
    expect($vega->refresh()->tampil_sebagai)->toBe(ClusterDisplay::Daftar)
        ->and($vega->houseTypes()->count())->toBe($types)
        ->and($vega->galleryItems()->count())->toBeGreaterThan(0)
        ->and($vega->clusterBenefits()->count())->toBe(1)
        ->and($vega->description)->not->toBeEmpty();

    // Aktifkan lagi sebagai halaman → buka form Cluster lengkapnya.
    Livewire::test(ListOtherClusters::class)
        ->callTableAction('aktifkanHalaman', $vega)
        ->assertRedirect(ClusterResource::getUrl('edit', ['record' => $vega]));
    expect($vega->refresh()->tampil_sebagai)->toBe(ClusterDisplay::Halaman);

    // Tambah Cluster Lainnya: cukup nama + kawasan (boleh kosong), slug otomatis, langsung "daftar".
    Livewire::test(CreateOtherCluster::class)
        ->assertFormFieldExists('name')
        ->assertFormFieldExists('kawasan_id')
        ->assertFormFieldDoesNotExist('galleryItems')
        ->fillForm(['name' => 'Giri Loka 1', 'kawasan_id' => null])
        ->call('create')
        ->assertHasNoFormErrors();
    $created = Cluster::query()->where('name', 'Giri Loka 1')->firstOrFail();
    expect($created->tampil_sebagai)->toBe(ClusterDisplay::Daftar)
        ->and($created->slug)->toBe('giri-loka-1')
        ->and($created->kawasan_id)->toBeNull()
        ->and($created->is_published)->toBeTrue();
    $this->get('/properti/cluster-lainnya')->assertInertia(fn (Assert $page) => $page
        ->where('groups', fn ($groups) => in_array('Giri Loka 1', collect($groups)->firstWhere('id', 'lainnya')['clusters'], true)));
});

it('memisahkan menu admin Kawasan / Kawasan Lainnya berdasarkan punya_halaman, dengan action pindah & aktifkan', function () {
    $this->actingAs(User::query()->where('email', 'admin@example.com')->firstOrFail());
    $garden = Kawasan::query()->where('slug', 'arunika-garden')->firstOrFail();
    $hills = Kawasan::query()->where('slug', 'arunika-hills')->firstOrFail();
    $hills->update(['punya_halaman' => false]);

    Livewire::test(ListKawasans::class)->assertCanSeeTableRecords([$garden])->assertCanNotSeeTableRecords([$hills]);
    Livewire::test(ListOtherKawasans::class)->assertCanSeeTableRecords([$hills])->assertCanNotSeeTableRecords([$garden]);
    Livewire::test(EditKawasan::class, ['record' => $garden->getRouteKey()])->assertFormFieldDoesNotExist('punya_halaman');

    $description = $garden->description;
    Livewire::test(EditKawasan::class, ['record' => $garden->getRouteKey()])
        ->callAction('pindahKeLainnya')
        ->assertRedirect(OtherKawasanResource::getUrl('index'));
    expect($garden->refresh()->punya_halaman)->toBeFalse()
        ->and($garden->description)->toBe($description);

    Livewire::test(EditOtherKawasan::class, ['record' => $garden->getRouteKey()])
        ->assertFormFieldExists('name')
        ->assertFormFieldDoesNotExist('description')
        ->callAction('aktifkanHalaman')
        ->assertRedirect(KawasanResource::getUrl('edit', ['record' => $garden]));
    expect($garden->refresh()->punya_halaman)->toBeTrue();

    Livewire::test(CreateOtherKawasan::class)
        ->fillForm(['name' => 'Giri Loka'])
        ->call('create')
        ->assertHasNoFormErrors();
    expect(Kawasan::query()->where('name', 'Giri Loka')->first())
        ->punya_halaman->toBeFalse()
        ->slug->toBe('giri-loka');
});

it('mengurutkan menu grup Properti: Cluster, Cluster Lainnya, Kawasan, Kawasan Lainnya, Profil Lokasi (lalu Bank Benefit)', function () {
    $this->actingAs(User::query()->where('email', 'admin@example.com')->firstOrFail());

    $html = $this->get('/admin')->assertOk()->getContent();
    $positions = collect(['/admin/clusters"', '/admin/cluster-lainnya"', '/admin/kawasans"', '/admin/kawasan-lainnya"', '/admin/profil-lokasi"', '/admin/bank-benefit"'])
        ->map(fn (string $href) => strpos($html, $href));

    expect($positions->every(fn ($p) => $p !== false))->toBeTrue()
        ->and($positions->all())->toBe($positions->sort()->values()->all());
});

it('menerapkan revisi update-3 dari file (sumber kebenaran): cluster halaman → daftar, The Eminent tanpa halaman', function () {
    $this->artisan('import:bsd-data', ['--fresh' => true, '--force' => true])->assertSuccessful();

    // Kondisi sebelum revisi: semua punya halaman (mis. dari import update-3 versi lama).
    Cluster::query()->update(['tampil_sebagai' => 'halaman']);
    Kawasan::query()->update(['punya_halaman' => true]);

    $this->artisan('import:bsd-update', ['path' => 'docs/data/bsd-city-update-3.json'])->assertSuccessful();

    $data = json_decode((string) file_get_contents(base_path('docs/data/bsd-city-update-3.json')), true);
    expect(Cluster::query()->where('tampil_sebagai', 'halaman')->count())->toBe(collect($data['cluster_tampilan'])->where('tampil_sebagai', 'halaman')->count())
        ->and(Cluster::query()->where('tampil_sebagai', 'daftar')->count())->toBe(collect($data['cluster_tampilan'])->where('tampil_sebagai', 'daftar')->count())
        ->and(Kawasan::query()->where('slug', 'the-eminent')->value('punya_halaman'))->toBeFalse();

    foreach (['laurel', 'aether', 'ingenia'] as $slug) {
        $cluster = Cluster::query()->where('slug', $slug)->with('kawasan')->firstOrFail();
        expect($cluster->tampil_sebagai)->toBe(ClusterDisplay::Daftar);
        $this->get("/properti/{$slug}")->assertStatus(301)
            ->assertRedirect('/properti/cluster-lainnya#'.($cluster->kawasan?->slug ?? 'lainnya'));
    }

    $this->get('/properti/kawasan/the-eminent')->assertStatus(301)->assertRedirect('/properti/cluster-lainnya#the-eminent');
});
