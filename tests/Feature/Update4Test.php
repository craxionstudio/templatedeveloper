<?php

use App\Models\Benefit;
use App\Models\Cluster;
use App\Models\User;
use Filament\Auth\Pages\Login;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Inertia\Testing\AssertableInertia as Assert;
use Livewire\Livewire;

/*
 * Update 4: benefit per cluster dari bsd-city-update-4.json, sistem promo lama dihapus, cookie session Secure.
 */

beforeEach(fn () => $this->seed());

function importUpdate4(): void
{
    test()->artisan('import:bsd-data', ['--fresh' => true, '--force' => true])->assertSuccessful();
    foreach (['bsd-city-update-2.json', 'bsd-city-update-3.json', 'bsd-city-update-4.json'] as $file) {
        test()->artisan('import:bsd-update', ['path' => "docs/data/{$file}"])->assertSuccessful();
    }
}

it('memakai slug benefit & cluster yang ada di database', function () {
    $this->artisan('import:bsd-data', ['--fresh' => true, '--force' => true])->assertSuccessful();
    $rows = collect(json_decode((string) file_get_contents(base_path('docs/data/bsd-city-update-4.json')), true)['benefits']);

    expect($rows->pluck('benefit_slug')->unique()->sort()->values()->all())->toBe(['diskon', 'free-bphtb', 'tanpa-dp'])
        ->and(Benefit::query()->whereIn('slug', $rows->pluck('benefit_slug')->unique())->count())->toBe(3)
        ->and(Cluster::query()->whereIn('slug', $rows->pluck('cluster_slug')->unique())->count())->toBe($rows->pluck('cluster_slug')->unique()->count());
});

it('menampilkan benefit update-4 di Detail Rumah, chip kartu, dan badge Promo (Castilo & Fleekhauz R)', function () {
    importUpdate4();

    $this->get('/properti/castilo-at-terravia')->assertOk()->assertInertia(fn (Assert $page) => $page
        ->where('benefits.title', 'Promo & Benefit')
        ->where('benefits.groups', [
            ['category' => 'Pembayaran', 'items' => [['icon' => 'tag', 'text' => 'Tanpa DP']]],
            ['category' => 'Diskon', 'items' => [['icon' => 'tag', 'text' => 'Diskon hingga 13% + 0,5%']]],
        ]));

    $this->get('/properti/fleekhauz-r')->assertOk()->assertInertia(fn (Assert $page) => $page
        ->where('benefits.groups', fn ($groups) => collect($groups)->flatMap(fn ($g) => collect($g['items'])->pluck('text'))->sort()->values()->all() === ['Diskon hingga 17%', 'Free BPHTB', 'Tanpa DP']));

    $cards = collect($this->get('/properti?urut=promo')->inertiaProps('clusters.data'))->keyBy('name');
    expect($cards['Castilo at Terravia']['badges'])->toBe(['Baru', 'Promo'])
        ->and($cards['Castilo at Terravia']['benefits'])->toBe(['Tanpa DP', 'Diskon hingga 13% + 0,5%'])
        ->and($cards['Fleekhauz R']['badges'])->toContain('Promo')
        ->and($cards['Fleekhauz R']['benefits'])->toBe(['Tanpa DP', 'Diskon hingga 17%', 'Free BPHTB']);

    // Aman diulang: tidak ada pivot dobel.
    $count = DB::table('benefit_cluster')->count();
    $this->artisan('import:bsd-update', ['path' => 'docs/data/bsd-city-update-4.json'])->assertSuccessful();
    expect(DB::table('benefit_cluster')->count())->toBe($count);
});

it('mengekspor promo lama ke CSV lalu menghapus tabel promos beserta pivot-nya', function () {
    $migration = require base_path('database/migrations/2026_10_06_400000_export_and_drop_promos_table.php');
    $migration->down();

    $promoId = DB::table('promos')->insertGetId(['title' => 'Promo Castilo, DP 0%', 'placement' => 'detail', 'is_published' => true, 'created_at' => now(), 'updated_at' => now()]);
    DB::table('cluster_promo')->insert(['cluster_id' => Cluster::query()->where('slug', 'vega-garden')->value('id'), 'promo_id' => $promoId]);
    File::delete(glob(storage_path('app/backup/promos-*.csv')) ?: []);

    $migration->up();

    $files = glob(storage_path('app/backup/promos-*.csv'));
    expect(Schema::hasTable('promos'))->toBeFalse()
        ->and(Schema::hasTable('cluster_promo'))->toBeFalse()
        ->and(Schema::hasTable('kawasan_promo'))->toBeFalse()
        ->and($files)->toHaveCount(1)
        ->and(File::get($files[0]))->toContain('"Promo Castilo, DP 0%"')->toContain('vega-garden')->toContain('cluster_slugs');

    File::delete($files);
});

it('tidak punya sisa sistem promo lama (model, resource admin, slider beranda)', function () {
    expect(class_exists('App\\Models\\Promo'))->toBeFalse()
        ->and(class_exists('App\\Filament\\Resources\\Promos\\PromoResource'))->toBeFalse()
        ->and(File::exists(resource_path('js/components/home/promo-slider.tsx')))->toBeFalse();

    $this->actingAs(User::query()->where('email', 'admin@example.com')->firstOrFail())->get('/admin/promos')->assertNotFound();
    $this->get('/')->assertInertia(fn (Assert $page) => $page->missing('promos'));
});

it('tetap bisa login admin dengan cookie session Secure (SESSION_SECURE_COOKIE=true)', function () {
    config(['session.secure' => true]);

    $response = $this->get('https://localhost/admin/login')->assertOk();
    $cookie = collect($response->headers->getCookies())->first(fn ($c) => $c->getName() === config('session.cookie'));
    expect($cookie)->not->toBeNull()
        ->and($cookie->isSecure())->toBeTrue()
        ->and($cookie->isHttpOnly())->toBeTrue();

    Livewire::test(Login::class)
        ->fillForm(['email' => 'admin@example.com', 'password' => 'password'])
        ->call('authenticate')
        ->assertHasNoFormErrors()
        ->assertRedirect();

    expect(auth()->user()?->email)->toBe('admin@example.com');
});
