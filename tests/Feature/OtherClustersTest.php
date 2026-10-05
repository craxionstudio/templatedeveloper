<?php

use App\Enums\ClusterDisplay;
use App\Filament\Resources\Clusters\Pages\CreateCluster;
use App\Filament\Resources\Clusters\Pages\EditCluster;
use App\Filament\Resources\Clusters\Pages\ListClusters;
use App\Filament\Resources\Kawasans\Pages\EditKawasan;
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

it('menampilkan "Cluster lain di kawasan ini" di Detail Kawasan, tersembunyi kalau kosong', function () {
    $this->get('/properti/kawasan/arunika-garden')->assertInertia(fn (Assert $page) => $page->where('otherClusters', null));

    listOnly('vega-garden', 'orion-park');

    $this->get('/properti/kawasan/arunika-garden')->assertInertia(fn (Assert $page) => $page
        ->where('otherClusters.title', 'Cluster lain di kawasan ini')
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
        ->where('clusters.title', 'Pilihan rumah di Arunika Garden'));
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

it('mengatur "Tampil sebagai" di form & tabel Cluster dan "Punya halaman sendiri" di form Kawasan', function () {
    $this->actingAs(User::query()->where('email', 'admin@example.com')->firstOrFail());
    $kawasan = Kawasan::query()->where('slug', 'arunika-garden')->firstOrFail();

    // Daftar: cukup nama + kawasan (tanpa foto), tab lain disembunyikan.
    Livewire::test(CreateCluster::class)
        ->fillForm(['tampil_sebagai' => ClusterDisplay::Daftar->value, 'name' => 'Cluster Lama', 'kawasan_id' => $kawasan->id])
        ->assertFormFieldHidden('galleryItems')
        ->assertFormFieldHidden('status')
        ->assertFormFieldHidden('is_published')
        ->assertFormFieldVisible('name')
        ->assertFormFieldVisible('kawasan_id')
        ->call('create')
        ->assertHasNoFormErrors();

    $created = Cluster::query()->where('name', 'Cluster Lama')->firstOrFail();
    expect($created->tampil_sebagai)->toBe(ClusterDisplay::Daftar)
        ->and($created->is_published)->toBeTrue();

    Livewire::test(EditCluster::class, ['record' => Cluster::query()->where('slug', 'vega-garden')->first()->getRouteKey()])
        ->assertFormFieldVisible('galleryItems')
        ->fillForm(['tampil_sebagai' => ClusterDisplay::Daftar->value])
        ->assertFormFieldHidden('galleryItems')
        ->call('save')
        ->assertHasNoFormErrors();

    Livewire::test(ListClusters::class)
        ->assertTableColumnExists('tampil_sebagai')
        ->filterTable('tampil_sebagai', ClusterDisplay::Daftar->value)
        ->assertCanSeeTableRecords(Cluster::query()->where('tampil_sebagai', 'daftar')->get())
        ->assertCanNotSeeTableRecords(Cluster::query()->where('tampil_sebagai', 'halaman')->get());

    Livewire::test(EditKawasan::class, ['record' => $kawasan->getRouteKey()])
        ->fillForm(['punya_halaman' => false])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($kawasan->refresh()->punya_halaman)->toBeFalse();
});
