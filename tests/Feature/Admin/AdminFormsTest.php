<?php

use App\Filament\Pages\Settings\ManageGlobalSettings;
use App\Filament\Pages\Settings\ManageHomePage;
use App\Filament\Pages\Settings\ManageOtherPages;
use App\Filament\Pages\Settings\ManagePropertyPages;
use App\Filament\Resources\Clusters\Pages\CreateCluster;
use App\Filament\Resources\Clusters\Pages\EditCluster;
use App\Filament\Resources\Kawasans\Pages\CreateKawasan;
use App\Models\Cluster;
use App\Models\Facility;
use App\Models\Kawasan;
use App\Models\Redirect;
use App\Models\User;
use App\Settings\ClusterDetailPageSettings;
use App\Settings\ContactPageSettings;
use App\Settings\GlobalSettings;
use App\Settings\HomePageSettings;
use App\Settings\ListingPageSettings;
use App\Support\DummyData;
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
    Storage::fake('public');

    Livewire::test(CreateCluster::class)
        ->fillForm(['name' => 'Cluster Baru', 'kawasan_id' => Cluster::STANDALONE_FILTER, 'galleryItems' => newClusterPhoto()])
        ->call('create')
        ->assertHasNoFormErrors();

    // Slug otomatis dari nama, alt foto otomatis dari nama cluster.
    $cluster = Cluster::where('slug', 'cluster-baru')->first();
    expect($cluster)->not->toBeNull()
        ->kawasan_id->toBeNull()
        ->and($cluster->galleryItems()->first()->alt)->toBe('Foto Cluster Baru');
});

it('hanya mewajibkan nama, kawasan, dan foto di form cluster', function () {
    Livewire::test(CreateCluster::class)
        ->fillForm(['name' => null, 'kawasan_id' => null])
        ->call('create')
        ->assertHasFormErrors(['name' => 'required', 'kawasan_id' => 'required', 'galleryItems' => 'required']);

    $required = collect(Livewire::test(CreateCluster::class)->instance()->form->getFlatFields(withHidden: true))
        ->filter(fn ($field) => method_exists($field, 'isRequired') && $field->isRequired())
        ->keys()
        ->reject(fn (string $key) => str_contains($key, '.'))
        ->values()
        ->all();

    expect($required)->toEqualCanonicalizing(['kawasan_id', 'name', 'galleryItems']);
});

it('mengisi slug, ringkasan, dan tahun launching cluster otomatis', function () {
    $kawasan = Kawasan::first();
    $cluster = Cluster::query()->create([
        'name' => 'Vega Garden',
        'kawasan_id' => $kawasan->id,
        'description' => '<p>Cluster dua lantai dengan taman tematik dan clubhouse.</p>',
        'tanggal_launching' => '2026-07-07',
    ]);

    expect($cluster->slug)->toBe('vega-garden-2')
        ->and($cluster->summary)->toBe('Cluster dua lantai dengan taman tematik dan clubhouse.')
        ->and($cluster->launch_year)->toBe(2026);
});

it('menduplikat cluster beserta tipe, benefit, dan foto', function () {
    Storage::fake('public');
    $cluster = withClusterPhoto(Cluster::where('slug', 'vega-garden')->first());
    $types = $cluster->houseTypes()->count();

    Livewire::test(EditCluster::class, ['record' => $cluster->getRouteKey()])
        ->callAction('duplikat')
        ->assertHasNoActionErrors();

    $copy = Cluster::where('name', 'Vega Garden (salinan)')->first();

    expect($copy)->not->toBeNull()
        ->slug->toBe('vega-garden-salinan')
        ->is_published->toBeFalse()
        ->and($copy->houseTypes()->count())->toBe($types)
        ->and($copy->galleryItems()->first()->getFirstMedia('image'))->not->toBeNull()
        ->and($cluster->fresh()->houseTypes()->count())->toBe($types);
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
    Storage::fake('public');
    $cluster = withClusterPhoto(Cluster::where('slug', 'vega-garden')->first());

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

it('meringkas pengaturan halaman jadi 4 menu', function () {
    $labels = collect(Filament\Facades\Filament::getNavigation())
        ->firstWhere(fn ($group) => $group->getLabel() === 'Pengaturan')
        ->getItems();

    expect(collect($labels)->map->getLabel()->all())->toBe(['Beranda', 'Properti', 'Halaman Lain', 'Pengaturan Umum']);
});

it('menyimpan beberapa settings halaman dari satu menu Properti dan Halaman Lain', function () {
    Livewire::test(ManagePropertyPages::class)
        ->fillForm(['listing.header.title' => 'Rumah di BSD', 'cluster.pricing.price_note' => 'Harga belum termasuk PPN.'])
        ->call('save')
        ->assertHasNoFormErrors();

    $settings = app(ListingPageSettings::class);

    expect($settings->header['title'])->toBe('Rumah di BSD')
        ->and($settings->toggle['kawasan_label'])->toBe('Kawasan')
        ->and(app(ClusterDetailPageSettings::class)->pricing['price_note'])->toBe('Harga belum termasuk PPN.');

    $this->get('/properti')->assertInertia(fn ($page) => $page->where('header.title', 'Rumah di BSD'));

    Livewire::test(ManageOtherPages::class)
        ->fillForm(['contact.header.title' => 'Hubungi kami', 'about.vision.vision' => 'Kota yang nyaman.'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(app(ContactPageSettings::class)->header['title'])->toBe('Hubungi kami');
    $this->get('/tentang-kami')->assertInertia(fn ($page) => $page->where('vision.vision', 'Kota yang nyaman.'));
});

it('memakai teks tetap di kode untuk field yang tidak ada di admin', function () {
    $settings = app(ListingPageSettings::class);
    $settings->toggle = [...$settings->toggle, 'kawasan_label' => 'Teks lama dari database'];
    $settings->save();

    expect(app(ListingPageSettings::class)->section('toggle')['kawasan_label'])->toBe('Kawasan');
});

it('mengisi slug, ringkasan, dan alt text gambar kawasan otomatis', function () {
    Storage::fake('public');

    Livewire::test(CreateKawasan::class)
        ->fillForm([
            'name' => 'Arunika Valley',
            'description' => '<p>Kawasan hunian di tepi danau.</p>',
            'hero' => [UploadedFile::fake()->image('IMG_0001.jpg', 1600, 900)],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $kawasan = Kawasan::where('slug', 'arunika-valley')->first();
    $media = $kawasan->getFirstMedia('hero');

    expect($kawasan->summary)->toBe('Kawasan hunian di tepi danau.')
        ->and($media)->not->toBeNull()
        ->and($media->file_name)->toStartWith('arunika-valley-');
});

it('menyimpan Pengaturan Umum: WhatsApp, kontak, GA4, dan verifikasi Search Console', function () {
    Livewire::test(ManageGlobalSettings::class)
        ->assertSee('Pengaturan Umum')
        ->fillForm([
            'contact.whatsapp' => '6281234567890',
            'contact.email' => 'marketing@bsdcity.test',
            'tracking.ga4_id' => 'G-AB12CD34EF',
            'tracking.google_verification' => 'kode-verifikasi',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $settings = app(GlobalSettings::class);
    expect($settings->tracking['ga4_id'])->toBe('G-AB12CD34EF')
        ->and($settings->contact['whatsapp'])->toBe('6281234567890');

    Livewire::test(ManageGlobalSettings::class)
        ->fillForm(['tracking.ga4_id' => 'UA-123'])
        ->call('save')
        ->assertHasFormErrors(['tracking.ga4_id' => 'regex']);
});

it('menandai data dummy di admin sampai nilainya diganti', function () {
    $deneb = Cluster::where('slug', 'vega-garden')->first()->houseTypes()->where('slug', 'deneb')->first();

    expect(DummyData::isHouseTypeValue($deneb, 'building_area', 120))->toBeTrue()
        ->and(DummyData::isHouseTypeValue($deneb, 'building_area', 125))->toBeFalse()
        ->and(DummyData::isFacilityKawasan(Facility::where('slug', 'rumah-ibadah')->first(), Kawasan::where('slug', 'arunika-lakeside')->value('id')))->toBeTrue()
        ->and(DummyData::isKawasanFacilities(Kawasan::where('slug', 'arunika-hills')->first(), Kawasan::where('slug', 'arunika-hills')->first()->facilities))->toBeTrue()
        ->and(DummyData::isKawasanFacilities(Kawasan::where('slug', 'arunika-garden')->first(), Kawasan::where('slug', 'arunika-garden')->first()->facilities))->toBeFalse();

    $this->get('/admin/facilities/'.Facility::where('slug', 'rumah-ibadah')->value('id').'/edit')->assertSee('Data dummy');
    $this->get('/admin/kawasans/'.Kawasan::where('slug', 'arunika-hills')->value('id').'/edit?tab=fasilitas-kawasan::data::tab')->assertOk();
});
