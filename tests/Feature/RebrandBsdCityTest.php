<?php

use App\Models\Article;
use App\Models\DeveloperProfile;
use App\Models\Facility;
use App\Models\FutureDevelopment;
use App\Models\Promo;
use App\Models\User;
use App\Settings\GlobalSettings;
use App\Settings\HomePageSettings;
use App\Settings\ListingPageSettings;
use App\Support\DummyContent;
use App\Support\Sitemaps;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * Ganti Arunika → BSD City (settings, profil, konten contoh), footer kawasan, dan keadaan kosong.
 */

beforeEach(function () {
    $this->seed();
    Sitemaps::flush();
});

function runMigrationFile(string $path): void
{
    (require base_path($path))->up();
}

it('mengganti teks Arunika di settings database lama tanpa menimpa teks yang sudah diubah admin', function () {
    // Simulasi database production sebelum migrasi: teks contoh lama + satu teks yang sudah diubah admin.
    $global = app(GlobalSettings::class);
    $global->identity = [...$global->identity, 'brand_name' => 'Arunika Land', 'tagline' => 'Developer Properti'];
    $global->save();

    $listing = app(ListingPageSettings::class);
    $listing->header = [...$listing->header, 'title' => 'Properti Arunika', 'description' => 'Deskripsi dari admin'];
    $listing->save();

    $home = app(HomePageSettings::class);
    $home->hero = [...$home->hero, 'eyebrow' => 'Arunika Land · Sejak [TAHUN]', 'title' => 'Kota yang tumbuh bersama keluargamu.', 'primary_label' => 'Lihat Semua Listing'];
    $home->facilities = [...$home->facilities, 'enabled' => true];
    $home->articles = [...$home->articles, 'enabled' => true];
    $home->save();

    runMigrationFile('database/settings/2026_09_29_100000_rebrand_to_bsd_city.php');

    expect(app(GlobalSettings::class)->refresh()->identity)
        ->brand_name->toBe('BSD City')
        ->tagline->toBe('Kota mandiri Sinar Mas Land di Serpong');

    expect(app(ListingPageSettings::class)->refresh()->header)
        ->title->toBe('Properti BSD City')
        ->description->toBe('Deskripsi dari admin');

    $home = app(HomePageSettings::class)->refresh();
    expect($home->hero)
        ->eyebrow->toBe('Serpong, Tangerang')
        ->title->toBe('Pilih rumah di kota seluas 6.000 hektare.')
        ->primary_label->toBe('Lihat Semua Cluster')
        ->primary_url->toBe('/properti')
        ->and($home->facilities['enabled'])->toBeFalse()
        ->and($home->developments['enabled'])->toBeFalse()
        ->and($home->articles['enabled'])->toBeFalse();
});

it('mengganti teks profil lalu menonaktifkan konten contoh tanpa menghapusnya', function () {
    $profile = DeveloperProfile::query()->firstOrFail();
    $profile->update(['description' => 'Arunika Land adalah pengembang kawasan hunian terpadu yang berdiri sejak [TAHUN]. Kami merencanakan setiap kawasan dari nol: jaringan jalan, ruang hijau, fasilitas pendidikan dan kesehatan, sampai pusat komersial, supaya penghuni tidak perlu jauh-jauh untuk kebutuhan sehari-hari.']);
    $articles = Article::count();
    $admin = User::where('email', 'admin@example.com')->exists();

    runMigrationFile('database/migrations/2026_09_29_100100_replace_arunika_content.php');

    expect($profile->fresh()->description)->toStartWith('BSD City adalah kota terencana')
        ->and(Article::count())->toBe($articles)
        ->and(Article::published()->count())->toBe(0)
        ->and(Facility::published()->count())->toBe(0)
        ->and(FutureDevelopment::published()->count())->toBe(0)
        ->and(Promo::where('is_published', true)->count())->toBe(0)
        ->and(User::where('email', 'admin@example.com')->exists())->toBe($admin);
});

it('tidak menyebut Arunika di halaman publik setelah import data asli', function (string $path) {
    $this->artisan('import:bsd-data', ['--fresh' => true])->assertSuccessful();

    $response = $this->get($path)->assertOk();

    expect(json_encode($response->inertiaProps(), JSON_UNESCAPED_UNICODE))->not->toContain('Arunika')->not->toContain('arunika');
})->with(['/', '/properti', '/properti/kawasan', '/properti/kawasan/vireya', '/properti/monard-of-the-armont', '/fasilitas', '/artikel', '/tentang-kami']);

it('memakai hero dan header Properti yang baru', function () {
    $this->get('/')->assertInertia(fn (Assert $page) => $page
        ->where('hero.eyebrow', 'Serpong, Tangerang')
        ->where('hero.title', 'Pilih rumah di kota seluas 6.000 hektare.')
        ->where('hero.description', 'Lebih dari 20 kawasan hunian, dari cluster baru di Vireya dan Terravia sampai NavaPark. Bandingkan tipe dan harga, lalu atur jadwal survey.')
        ->where('hero.primary', fn ($button) => $button['label'] === 'Lihat Semua Cluster' && $button['url'] === '/properti')
        ->where('hero.secondary.label', 'Chat Marketing'));

    $this->get('/properti')->assertInertia(fn (Assert $page) => $page
        ->where('header.title', 'Properti BSD City')
        ->where('header.description', 'Pilih rumah berdasarkan cluster, atau jelajahi dulu kawasan-kawasan di BSD City.'));
});

it('menampilkan maksimal 8 kawasan di footer, kawasan cluster prioritas dulu, plus link semua kawasan', function () {
    $this->artisan('import:bsd-data', ['--fresh' => true])->assertSuccessful();

    $this->get('/')->assertInertia(fn (Assert $page) => $page
        ->has('site.footer.columns.0.links', 9)
        ->where('site.footer.columns.0.links.0.label', 'The Armont') // Monard, prioritas 1
        ->where('site.footer.columns.0.links.1.label', 'NavaPark')   // Island Villa, prioritas 2
        ->where('site.footer.columns.0.links.2.label', 'Vireya')     // Vyorelle, prioritas 3
        ->where('site.footer.columns.0.links.3.label', 'Terravia')   // Castilo, prioritas 4
        ->where('site.footer.columns.0.links.8', ['label' => 'Semua kawasan', 'url' => '/properti/kawasan']));
});

it('mematikan section Fasilitas, Pengembangan Mendatang, dan Artikel di Beranda', function () {
    DummyContent::unpublish();

    $this->get('/')->assertInertia(fn (Assert $page) => $page
        ->where('facilities', null)
        ->where('developments', null)
        ->where('articles', null)
        ->where('promos', null));
});

it('menampilkan keadaan kosong di /fasilitas dan /artikel kalau belum ada konten yang dipublikasikan', function () {
    DummyContent::unpublish();

    $this->get('/fasilitas')->assertOk()->assertInertia(fn (Assert $page) => $page
        ->where('empty.title', 'Daftar fasilitas sedang kami lengkapi')
        ->where('empty.button', ['label' => 'Lihat kawasan', 'url' => '/properti/kawasan'])
        ->where('header.stats', [])
        ->where('header.images', [])
        ->has('facilities', 0));

    $this->get('/artikel')->assertOk()->assertInertia(fn (Assert $page) => $page
        ->where('empty.title', 'Artikel segera hadir')
        ->where('highlight', null)
        ->has('articles.data', 0));
});

it('tidak memakai keadaan kosong selama masih ada konten, termasuk pencarian tanpa hasil', function () {
    $this->get('/fasilitas')->assertInertia(fn (Assert $page) => $page->where('empty', null));
    $this->get('/artikel?q=tidak-ada-yang-cocok')->assertInertia(fn (Assert $page) => $page
        ->where('empty', null)
        ->has('articles.data', 0)
        ->where('emptyText', 'Belum ada artikel yang cocok.'));
});

it('tidak memuat artikel dan kategori tanpa artikel terbit di sitemap', function () {
    $article = Article::published()->firstOrFail();
    DummyContent::unpublish();

    $xml = $this->get('/sitemap-artikel.xml')->assertOk()->getContent();

    expect($xml)->not->toContain($article->publicPath())
        ->not->toContain('/artikel/kategori/');

    // Halaman /artikel & /fasilitas sendiri tetap ada (dengan keadaan kosong).
    expect($this->get('/sitemap-pages.xml')->getContent())->toContain('/artikel')->toContain('/fasilitas');
});
