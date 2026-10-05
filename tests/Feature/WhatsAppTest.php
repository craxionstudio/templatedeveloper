<?php

use App\Filament\Pages\Settings\ManageOtherPages;
use App\Models\Benefit;
use App\Models\Cluster;
use App\Models\User;
use App\Settings\GlobalSettings;
use App\Settings\NavigationSettings;
use App\Settings\PrivacyPageSettings;
use App\Support\WhatsApp;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Inertia\Testing\AssertableInertia as Assert;
use Livewire\Livewire;

/*
 * Tahap C: semua lead lewat WhatsApp (tanpa form), tracking organik GA4 langsung.
 */

beforeEach(function () {
    $this->seed();

    $global = app(GlobalSettings::class);
    $global->contact = [...$global->contact, 'whatsapp' => '6281111111111'];
    $global->save();
});

function waText(string $url): string
{
    parse_str((string) parse_url($url, PHP_URL_QUERY), $query);

    return (string) ($query['text'] ?? '');
}

function waNumber(string $url): string
{
    return trim((string) parse_url($url, PHP_URL_PATH), '/');
}

it('memakai template pesan per konteks dengan {nama_cluster}', function () {
    $response = $this->get('/properti/vega-garden')->assertOk();
    $props = $response->inertiaProps();

    expect(waText($props['cluster']['whatsappUrl']))->toBe('Halo, saya tertarik dengan Vega Garden. Boleh minta info harga & brosurnya?')
        ->and(waText($props['contact']['surveyUrl']))->toBe('Halo, saya ingin jadwalkan survey ke Vega Garden.')
        ->and(waText($props['cta']['surveyUrl']))->toBe('Halo, saya ingin jadwalkan survey ke Vega Garden.')
        ->and($props['cta']['cluster'])->toBe('Vega Garden');

    Cluster::where('slug', 'vega-garden')->first()->clusterBenefits()->create(['benefit_id' => Benefit::where('slug', 'tanpa-dp')->value('id'), 'urutan' => 1]);
    expect(waText($this->get('/properti/vega-garden')->inertiaProps('benefits.whatsappUrl')))
        ->toBe('Halo, saya tertarik dengan promo di Vega Garden. Boleh minta informasi lengkapnya?');

    $this->get('/tentang-kami')->assertInertia(fn (Assert $page) => $page
        ->where('site.contact.whatsappUrl', fn (string $url) => waText($url) === 'Halo, saya ingin konsultasi rumah di BSD City.'));
});

it('bisa mengubah template dari Pengaturan Umum', function () {
    $global = app(GlobalSettings::class);
    $global->contact = [...$global->contact, 'whatsapp_cluster_message' => 'Info {nama_cluster} dong', 'whatsapp_message' => 'Halo BSD'];
    $global->save();

    expect(waText(WhatsApp::url(WhatsApp::CLUSTER, Cluster::where('slug', 'vega-garden')->first())))->toBe('Info Vega Garden dong')
        ->and(waText(WhatsApp::url()))->toBe('Halo BSD');
});

it('memakai nomor WA cluster kalau diisi, kalau kosong nomor global', function () {
    $cluster = Cluster::where('slug', 'vega-garden')->first();
    $cluster->update(['marketing_whatsapp' => '6289999999999']);

    expect(waNumber($this->get('/properti/vega-garden')->inertiaProps('cluster.whatsappUrl')))->toBe('6289999999999')
        ->and(waNumber($this->get('/properti/hana-residence')->inertiaProps('cluster.whatsappUrl')))->toBe('6281111111111');
});

it('tidak lagi punya form lead, Turnstile, endpoint CAPI, atau halaman terima kasih', function () {
    expect($this->post('/lead', ['name' => 'Budi'])->status())->toBeIn([404, 405])
        ->and($this->post('/track/contact', [])->status())->toBeIn([404, 405]);

    $this->get('/terima-kasih')->assertStatus(301)->assertRedirect('/');

    $this->get('/properti/vega-garden')->assertInertia(fn (Assert $page) => $page->missing('form')->has('contact.surveyUrl'));

    $html = $this->get('/properti/vega-garden')->getContent();
    expect($html)->not->toContain('challenges.cloudflare.com')->not->toContain('leadModal');
});

it('mengekspor lead ke CSV sebelum tabel leads dihapus', function () {
    $migration = require base_path('database/migrations/2026_10_05_200000_export_and_drop_leads_table.php');
    $migration->down();

    DB::table('leads')->insert([
        'name' => 'Budi', 'whatsapp' => '6281234567890', 'status' => 'baru',
        'cluster_id' => Cluster::where('slug', 'vega-garden')->value('id'), 'created_at' => now(), 'updated_at' => now(),
    ]);

    File::deleteDirectory(storage_path('app/backup'));
    $migration->up();

    $file = storage_path('app/backup/leads-'.now()->format('Y-m-d').'.csv');
    expect(Schema::hasTable('leads'))->toBeFalse()
        ->and(File::exists($file))->toBeTrue()
        ->and(File::get($file))->toContain('Budi')->toContain('Vega Garden')->toContain('cluster_name');

    File::deleteDirectory(storage_path('app/backup'));
});

it('memasang GA4 lewat gtag.js hanya kalau Measurement ID diisi, tanpa GTM dan Meta Pixel', function () {
    $html = $this->get('/')->getContent();
    expect($html)->not->toContain('googletagmanager.com')->not->toContain('fbevents.js')->not->toContain('gtag(');

    $global = app(GlobalSettings::class);
    $global->tracking = [...$global->tracking, 'ga4_id' => 'G-AB12CD34EF', 'gtm_id' => 'GTM-LAMA123', 'meta_pixel_id' => '1234567890'];
    $global->save();

    $html = $this->get('/')->getContent();
    expect($html)->toContain('https://www.googletagmanager.com/gtag/js?id=')
        ->toContain('G-AB12CD34EF')
        ->toContain('send_page_view: false')
        ->not->toContain('GTM-LAMA123')
        ->not->toContain('gtm.js')
        ->not->toContain('fbevents.js')
        ->not->toContain('1234567890');

    $this->get('/')->assertInertia(fn (Assert $page) => $page->where('site.tracking', ['ga4Id' => 'G-AB12CD34EF']));
});

it('memasang meta tag verifikasi Google Search Console', function () {
    $global = app(GlobalSettings::class);
    $global->tracking = [...$global->tracking, 'google_verification' => '<meta name="google-site-verification" content="abcDEF123_xyz-987" />'];
    $global->save();

    expect($this->get('/')->getContent())->toContain('<meta name="google-site-verification" content="abcDEF123_xyz-987">');
});

it('membatasi CSP ke GA4, Google Maps, dan YouTube (tanpa Meta, GTM, Turnstile)', function () {
    $csp = (string) $this->get('/')->headers->get('Content-Security-Policy');

    expect($csp)->toContain('https://*.google-analytics.com')
        ->toContain('https://www.googletagmanager.com')
        ->not->toContain('facebook')
        ->not->toContain('challenges.cloudflare.com')
        ->not->toContain('td.doubleclick.net');
});

it('menyesuaikan Kebijakan Privasi dengan GA4 tanpa membahas form dan Meta', function () {
    $settings = app(PrivacyPageSettings::class);
    $settings->content = [...$settings->content, 'body' => '<p>[ISI KEBIJAKAN PRIVASI — WAJIB DITINJAU]</p><p>Meta Pixel</p>'];
    $settings->save();

    (require base_path('database/settings/2026_10_05_200000_whatsapp_only_and_ga4.php'))->up();
    app(PrivacyPageSettings::class)->refresh();
    app(GlobalSettings::class)->refresh();

    $body = $this->get('/kebijakan-privasi')->inertiaProps('content.body');
    expect($body)->toContain('Google Analytics 4')->toContain('WhatsApp')
        ->not->toContain('Meta')->not->toContain('Pixel')
        ->and(app(GlobalSettings::class)->refresh()->tracking)->toHaveKeys(['ga4_id', 'google_verification'])->not->toHaveKey('gtm_id');
});

it('menampilkan tombol WhatsApp melayang dengan konteks cluster', function () {
    $this->get('/properti/vega-garden')->assertInertia(fn (Assert $page) => $page
        ->component('Cluster/Show')
        ->where('cluster.name', 'Vega Garden')
        ->where('cluster.whatsappUrl', fn (string $url) => str_starts_with($url, 'https://wa.me/')));
});

/*
 * Halaman Kontak dihapus (6 Okt 2026): menu Kontak membuka WhatsApp, info kontak di footer.
 */

it('mengalihkan /kontak ke beranda dan tidak memuatnya di sitemap', function () {
    $this->get('/kontak')->assertStatus(301)->assertRedirect('/');

    expect($this->get('/sitemap-pages.xml')->getContent())->not->toContain('/kontak');
});

it('membuat menu Kontak di header, drawer, dan footer langsung membuka WhatsApp', function () {
    // Item /kontak lama yang tersimpan di Menu Navigasi tidak dobel.
    $navigation = app(NavigationSettings::class);
    $navigation->header_items = [...$navigation->header_items, ['label' => 'Hubungi Kami', 'url' => '/kontak', 'new_tab' => false]];
    $navigation->save();

    $props = $this->get('/')->inertiaProps('site');
    $kontak = collect($props['navigation'])->last();

    expect(collect($props['navigation'])->pluck('url'))->not->toContain('/kontak')
        ->and($kontak)->toMatchArray(['label' => 'Kontak', 'new_tab' => true, 'position' => 'menu_kontak'])
        ->and(waNumber($kontak['url']))->toBe('6281111111111')
        ->and(waText($kontak['url']))->toBe('Halo, saya ingin konsultasi rumah di BSD City.');

    $footerKontak = collect($props['footer']['columns'])->flatMap(fn (array $column) => $column['links'])->firstWhere('label', 'Kontak');
    expect($footerKontak)->toMatchArray(['url' => $kontak['url'], 'new_tab' => true, 'position' => 'menu_kontak']);
});

it('mengarahkan menu Kontak ke info kontak di footer kalau nomor WhatsApp global kosong', function () {
    $global = app(GlobalSettings::class);
    $global->contact = [...$global->contact, 'whatsapp' => ''];
    $global->save();

    expect(collect($this->get('/')->inertiaProps('site.navigation'))->last())
        ->toMatchArray(['label' => 'Kontak', 'url' => '#info-kontak', 'new_tab' => false]);
});

it('menampilkan info kontak dari Pengaturan Umum di footer semua halaman', function () {
    $global = app(GlobalSettings::class);
    $global->contact = [...$global->contact,
        'office_address' => 'Marketing Gallery BSD City, Jl. Grand Boulevard',
        'phone' => '021 5315 9000',
        'email' => 'marketing@bsdcity.com',
        'opening_hours' => 'Setiap hari, 09.00–17.00',
        'maps_url' => 'https://maps.app.goo.gl/abc123',
    ];
    $global->save();

    foreach (['/', '/properti', '/properti/vega-garden', '/artikel'] as $path) {
        $this->get($path)->assertInertia(fn (Assert $page) => $page
            ->where('site.footerContact.address.value', 'Marketing Gallery BSD City, Jl. Grand Boulevard')
            ->where('site.footerContact.phone', ['value' => '021 5315 9000', 'url' => 'tel:02153159000'])
            ->where('site.footerContact.whatsapp.value', '+62 811-1111-1111')
            ->where('site.footerContact.whatsapp.url', fn (string $url) => waNumber($url) === '6281111111111')
            ->where('site.footerContact.email', ['value' => 'marketing@bsdcity.com', 'url' => 'mailto:marketing@bsdcity.com'])
            ->where('site.footerContact.hours.value', 'Setiap hari, 09.00–17.00')
            ->where('site.footerContact.maps.url', 'https://maps.app.goo.gl/abc123'));
    }

    // Teks contoh [..] dan kosong tidak tampil; tanpa link Maps dipakai koordinat kalau ada.
    $global->contact = [...$global->contact, 'phone' => '[NO. TELEPON]', 'email' => '', 'maps_url' => '', 'latitude' => -6.3, 'longitude' => 106.65];
    $global->save();

    $this->get('/')->assertInertia(fn (Assert $page) => $page
        ->where('site.footerContact.phone', null)
        ->where('site.footerContact.email', null)
        ->where('site.footerContact.maps.url', 'https://www.google.com/maps/search/?api=1&query=-6.3,106.65'));
});

it('memasang kantor pemasaran (LocalBusiness) dengan alamat & kontak di JSON-LD semua halaman', function () {
    $global = app(GlobalSettings::class);
    $global->contact = [...$global->contact, 'office_address' => 'Jl. Grand Boulevard, BSD City', 'phone' => '021 5315 9000', 'email' => 'marketing@bsdcity.com'];
    $global->save();

    foreach (['/', '/properti', '/properti/vega-garden', '/tentang-kami'] as $path) {
        expect(ofType(jsonLd($this->get($path)), 'RealEstateAgent'))
            ->toMatchArray(['@id' => url('/').'/#kantor-pemasaran', 'url' => url('/').'/', 'telephone' => '02153159000', 'email' => 'marketing@bsdcity.com'])
            ->and(ofType(jsonLd($this->get($path)), 'RealEstateAgent')['address']['streetAddress'])->toBe('Jl. Grand Boulevard, BSD City');
    }
});

it('menghapus pengaturan halaman Kontak dari admin dan database', function () {
    (require base_path('database/settings/2026_10_06_100000_remove_contact_page.php'))->up();

    expect(DB::table('settings')->where('group', 'page_contact')->count())->toBe(0)
        ->and(class_exists('App\\Settings\\ContactPageSettings'))->toBeFalse()
        ->and(app(PrivacyPageSettings::class)->refresh()->content['body'])->not->toContain('halaman Kontak');

    $this->actingAs(User::query()->where('email', 'admin@example.com')->firstOrFail());

    Livewire::test(ManageOtherPages::class)
        ->assertFormFieldDoesNotExist('contact.header.title')
        ->assertFormFieldDoesNotExist('contact.map.embed_url');
});
