<?php

use App\Filament\Pages\Settings\ManageHomePage;
use App\Filament\Pages\Settings\ManageListingPage;
use App\Filament\Resources\Clusters\Pages\CreateCluster;
use App\Filament\Resources\Clusters\Pages\EditCluster;
use App\Filament\Resources\Kawasans\Pages\CreateKawasan;
use App\Filament\Resources\Leads\Pages\ListLeads;
use App\Models\Cluster;
use App\Models\Kawasan;
use App\Models\Lead;
use App\Models\Redirect;
use App\Models\User;
use App\Settings\HomePageSettings;
use App\Settings\ListingPageSettings;
use Filament\Actions\Testing\TestAction;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed();
    $this->actingAs(User::where('email', 'admin@example.com')->first());
});

it('menolak slug cluster "kawasan" di form admin', function () {
    Livewire::test(CreateCluster::class)
        ->fillForm(['name' => 'Kawasan', 'slug' => 'kawasan'])
        ->call('create')
        ->assertHasFormErrors(['slug' => 'not_in']);
});

it('membuat cluster mandiri dari form admin', function () {
    Livewire::test(CreateCluster::class)
        ->fillForm(['name' => 'Cluster Baru', 'slug' => 'cluster-baru', 'kawasan_id' => null])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Cluster::where('slug', 'cluster-baru')->first())
        ->not->toBeNull()
        ->kawasan_id->toBeNull();
});

it('membuat kawasan dan menolak slug yang sudah dipakai', function () {
    Livewire::test(CreateKawasan::class)
        ->fillForm(['name' => 'Arunika Garden', 'slug' => 'arunika-garden', 'summary' => 'Ringkasan'])
        ->call('create')
        ->assertHasFormErrors(['slug' => 'unique']);

    Livewire::test(CreateKawasan::class)
        ->fillForm(['name' => 'Arunika Valley', 'slug' => 'arunika-valley', 'summary' => 'Ringkasan'])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Kawasan::where('slug', 'arunika-valley')->exists())->toBeTrue();
});

it('membuat redirect saat slug cluster diubah dari admin', function () {
    $cluster = Cluster::where('slug', 'vega-garden')->first();

    Livewire::test(EditCluster::class, ['record' => $cluster->getRouteKey()])
        ->fillForm(['slug' => 'vega-garden-residence'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(Redirect::where('from_path', '/properti/vega-garden')->value('to_path'))->toBe('/properti/vega-garden-residence');
});

it('menyimpan settings halaman tanpa menghilangkan field lain', function () {
    Livewire::test(ManageHomePage::class)
        ->fillForm(['hero.title' => 'Rumah untuk keluarga'])
        ->call('save')
        ->assertHasNoFormErrors();

    $hero = app(HomePageSettings::class)->hero;

    expect($hero['title'])->toBe('Rumah untuk keluarga')
        ->and($hero['primary_url'])->toBe('/properti')
        ->and($hero['enabled'])->toBeTrue();
});

it('menyimpan settings listing untuk kedua tampilan', function () {
    Livewire::test(ManageListingPage::class)
        ->fillForm(['toggle.kawasan_label' => 'Per kawasan', 'kawasan_view.standalone.enabled' => false])
        ->call('save')
        ->assertHasNoFormErrors();

    $settings = app(ListingPageSettings::class);

    expect($settings->toggle['kawasan_label'])->toBe('Per kawasan')
        ->and($settings->kawasan_view['standalone']['enabled'])->toBeFalse()
        ->and($settings->kawasan_view['standalone']['title'])->toBe('Cluster yang berdiri sendiri');
});

it('mewajibkan alt text saat mengunggah gambar', function () {
    Storage::fake('public');

    Livewire::test(CreateKawasan::class)
        ->fillForm([
            'name' => 'Arunika Valley',
            'slug' => 'arunika-valley',
            'summary' => 'Ringkasan',
            'hero' => [UploadedFile::fake()->image('hero.jpg', 1600, 900)],
            'hero_alt' => '',
        ])
        ->call('create')
        ->assertHasFormErrors(['hero_alt' => 'required']);

    Livewire::test(CreateKawasan::class)
        ->fillForm([
            'name' => 'Arunika Valley',
            'slug' => 'arunika-valley',
            'summary' => 'Ringkasan',
            'hero' => [UploadedFile::fake()->image('IMG_0001.jpg', 1600, 900)],
            'hero_alt' => 'Foto aerial Arunika Valley',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $media = Kawasan::where('slug', 'arunika-valley')->first()->getFirstMedia('hero');

    expect($media)->not->toBeNull()
        ->and($media->file_name)->toStartWith('foto-aerial-arunika-valley-')
        ->and($media->getCustomProperty('alt'))->toBe('Foto aerial Arunika Valley');
});

it('mengekspor lead ke CSV sesuai filter', function () {
    Lead::query()->create(['name' => 'Budi', 'whatsapp' => '6281234567890', 'utm_source' => 'facebook']);
    Lead::query()->create(['name' => 'Sari', 'whatsapp' => '6281298765432', 'utm_source' => 'google']);

    Livewire::test(ListLeads::class)
        ->filterTable('utm_source', 'facebook')
        ->callAction(TestAction::make('export')->table(), ['format' => 'csv'])
        ->assertFileDownloaded();
});
