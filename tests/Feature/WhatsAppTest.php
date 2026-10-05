<?php

use App\Filament\Pages\Settings\ManageGlobalSettings;
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

    $link = url('/properti/vega-garden');
    expect(waText($props['cluster']['whatsappUrl']))->toBe("Halo, saya tertarik dengan Vega Garden. Boleh minta info harga & brosurnya?\n{$link}")
        ->and(waText($props['contact']['surveyUrl']))->toBe("Halo, saya ingin jadwalkan survey ke Vega Garden.\n{$link}")
        ->and(waText($props['cta']['surveyUrl']))->toBe("Halo, saya ingin jadwalkan survey ke Vega Garden.\n{$link}")
        ->and($props['cta']['cluster'])->toBe('Vega Garden');

    Cluster::where('slug', 'vega-garden')->first()->clusterBenefits()->create(['benefit_id' => Benefit::where('slug', 'tanpa-dp')->value('id'), 'urutan' => 1]);
    expect(waText($this->get('/properti/vega-garden')->inertiaProps('benefits.whatsappUrl')))
        ->toBe("Halo, saya tertarik dengan promo di Vega Garden. Boleh minta informasi lengkapnya?\n{$link}");

    $this->get('/tentang-kami')->assertInertia(fn (Assert $page) => $page
        ->where('site.contact.whatsappUrl', fn (string $url) => waText($url) === "Halo, saya ingin konsultasi rumah di BSD City.\n".url('/tentang-kami')));
});

it('bisa mengubah template dari Pengaturan Umum', function () {
    $global = app(GlobalSettings::class);
    $global->contact = [...$global->contact, 'whatsapp_cluster_message' => 'Info {nama_cluster} dong', 'whatsapp_message' => 'Halo BSD'];
    $global->save();

    expect(waText(WhatsApp::url(WhatsApp::CLUSTER, Cluster::where('slug', 'vega-garden')->first())))->toBe('Info Vega Garden dong')
        ->and(waText(WhatsApp::url()))->toBe('Halo BSD');
});

it('memakai satu nomor WA global untuk semua tombol (nomor per cluster dihapus)', function () {
    expect(Schema::hasColumn('clusters', 'marketing_whatsapp'))->toBeFalse();

    $props = $this->get('/properti/vega-garden')->inertiaProps();
    expect(collect([$props['cluster']['whatsappUrl'], $props['contact']['surveyUrl'], $props['cta']['whatsappUrl'], $props['site']['contact']['whatsappUrl']])
        ->map(fn (string $url) => waNumber($url))->unique()->all())->toBe(['6281111111111']);
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
        ->and(waText($kontak['url']))->toBe("Halo, saya ingin konsultasi rumah di BSD City.\n".url('/').'/');

    $footerKontak = collect($props['footer']['columns'])->flatMap(fn (array $column) => $column['links'])->firstWhere('label', 'Kontak');
    expect($footerKontak)->toMatchArray(['url' => $kontak['url'], 'new_tab' => true, 'position' => 'menu_kontak']);
});

it('tetap memakai link wa.me langsung walau nomor WhatsApp global kosong (tanpa anchor)', function () {
    $global = app(GlobalSettings::class);
    $global->contact = [...$global->contact, 'whatsapp' => ''];
    $global->save();

    $props = $this->get('/')->inertiaProps('site');
    expect(collect($props['navigation'])->last())->toMatchArray(['label' => 'Kontak', 'new_tab' => true])
        ->and(collect($props['navigation'])->last()['url'])->toStartWith('https://wa.me/?text=')
        ->and($props['contact']['whatsappUrl'])->toStartWith('https://wa.me/?text=')
        ->and(json_encode($props))->not->toContain('#info-kontak');
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

/*
 * Penyederhanaan WhatsApp (6 Okt 2026): satu nomor, selalu link wa.me, template dengan placeholder halaman.
 */

it('membuat link wa.me dengan pesan ter-URL-encode (spasi %20, baris baru %0A) dan nomor dinormalisasi', function (string $number, string $expected) {
    expect(WhatsApp::link($number, "Halo & salam, tipe 6.5?\nhttps://contoh.test/properti/a"))
        ->toBe("https://wa.me/{$expected}?text=Halo%20%26%20salam%2C%20tipe%206.5%3F%0Ahttps%3A%2F%2Fcontoh.test%2Fproperti%2Fa");
})->with([
    ['0812-3456-7890', '6281234567890'],
    ['+62 812 3456 7890', '6281234567890'],
    ['6281234567890', '6281234567890'],
]);

it('menyamakan baris baru Windows dari textarea admin jadi %0A', function () {
    $global = app(GlobalSettings::class);
    $global->contact = [...$global->contact, 'whatsapp_message' => "Baris satu\r\nBaris dua {link_halaman}"];
    $global->save();

    $url = $this->get('/fasilitas?page=2&utm_source=x')->inertiaProps('site.contact.whatsappUrl');

    expect($url)->toBe('https://wa.me/6281111111111?text=Baris%20satu%0ABaris%20dua%20'.rawurlencode(url('/fasilitas')))
        ->and($url)->not->toContain('%0D');
});

it('menghasilkan link wa.me lengkap untuk Detail Rumah dengan {link_halaman} tanpa query string', function () {
    config(['app.url' => 'https://templatedeveloper.craxionstudio.com']);

    $props = $this->get('/properti/vega-garden?tipe=deneb&utm_source=ig')->inertiaProps();
    $link = 'https://templatedeveloper.craxionstudio.com/properti/vega-garden';

    expect($props['cluster']['whatsappUrl'])->toBe('https://wa.me/6281111111111?text='
            .rawurlencode("Halo, saya tertarik dengan Vega Garden. Boleh minta info harga & brosurnya?\n{$link}"))
        ->and($props['cluster']['whatsappUrl'])->toContain('%0Ahttps%3A%2F%2Ftemplatedeveloper.craxionstudio.com%2Fproperti%2Fvega-garden')
        ->and(urldecode($props['cluster']['whatsappUrl']))->not->toContain('utm_source')->not->toContain('tipe=');
});

it('mengisi semua placeholder dan tidak menambah link kalau {link_halaman} tidak ada di template', function () {
    $global = app(GlobalSettings::class);
    $global->contact = [...$global->contact,
        'whatsapp_cluster_message' => '{nama_cluster} | {nama_kawasan} | {judul_halaman}',
        'whatsapp_message' => 'Tanpa link',
    ];
    $global->save();

    $props = $this->get('/properti/vega-garden')->inertiaProps();

    expect(waText($props['cluster']['whatsappUrl']))->toBe('Vega Garden | Arunika Garden | '.$props['meta']['title'])
        ->and(waText($props['site']['contact']['whatsappUrl']))->toBe('Tanpa link');
});

it('memakai template kawasan di Detail Kawasan (CTA & tombol melayang), template umum untuk menu Kontak', function () {
    $props = $this->get('/properti/kawasan/arunika-garden')->inertiaProps();
    $link = url('/properti/kawasan/arunika-garden');

    expect(waText($props['floatingWhatsappUrl']))->toBe("Halo, saya ingin tahu cluster di kawasan Arunika Garden.\n{$link}")
        ->and(waText($props['cta']['whatsappUrl']))->toBe("Halo, saya ingin tahu cluster di kawasan Arunika Garden.\n{$link}")
        ->and(waText(collect($props['site']['navigation'])->last()['url']))->toBe("Halo, saya ingin konsultasi rumah di BSD City.\n{$link}");
});

it('membuat link per halaman walau data layout di-cache', function () {
    config(['site.page_cache.enabled' => true]);

    $home = $this->get('/')->inertiaProps('site.contact.whatsappUrl');
    $about = $this->get('/tentang-kami')->inertiaProps('site.contact.whatsappUrl');

    expect(waText($home))->toEndWith(url('/').'/')
        ->and(waText($about))->toEndWith(url('/tentang-kami'));
});

it('memperbarui template bawaan lama di database dengan {link_halaman} tanpa menimpa teks admin', function () {
    $global = app(GlobalSettings::class);
    $global->contact = [...$global->contact,
        'whatsapp_message' => 'Halo, saya ingin konsultasi rumah di BSD City.',
        'whatsapp_promo_message' => 'Promo {nama_cluster} versi admin',
    ];
    unset($global->contact['whatsapp_kawasan_message']);
    $global->save();

    (require base_path('database/settings/2026_10_06_200000_whatsapp_templates_with_link.php'))->up();

    $contact = app(GlobalSettings::class)->refresh()->contact;
    expect($contact['whatsapp_message'])->toBe("Halo, saya ingin konsultasi rumah di BSD City.\n{link_halaman}")
        ->and($contact['whatsapp_promo_message'])->toBe('Promo {nama_cluster} versi admin')
        ->and($contact['whatsapp_kawasan_message'])->toBe("Halo, saya ingin tahu cluster di kawasan {nama_kawasan}.\n{link_halaman}");
});

it('mewajibkan nomor WA global di Pengaturan Umum dan menampilkan placeholder + contoh hasil', function () {
    $this->actingAs(User::query()->where('email', 'admin@example.com')->firstOrFail());

    Livewire::test(ManageGlobalSettings::class)
        ->fillForm(['contact.whatsapp' => ''])
        ->call('save')
        ->assertHasFormErrors(['contact.whatsapp' => 'required']);

    Livewire::test(ManageGlobalSettings::class)
        ->fillForm(['contact.whatsapp' => '0812345'])
        ->call('save')
        ->assertHasFormErrors(['contact.whatsapp' => 'regex']);

    Livewire::test(ManageGlobalSettings::class)
        ->assertSee('{judul_halaman}')
        ->assertSee('Contoh hasil')
        ->assertSee('Halo, saya ingin tahu cluster di kawasan Greenwich Park.')
        ->assertSee(url('/properti/castilo-at-terravia'))
        ->fillForm(['contact.whatsapp_survey_message' => 'Survey {nama_cluster} yuk'])
        ->assertSee('Survey Castilo at Terravia yuk')
        ->call('save')
        ->assertHasNoFormErrors();

    expect(app(GlobalSettings::class)->refresh()->contact['whatsapp_survey_message'])->toBe('Survey {nama_cluster} yuk');
});

it('memperingatkan di dashboard admin dan laporan deploy kalau nomor WA global kosong', function () {
    $this->actingAs(User::query()->where('email', 'admin@example.com')->firstOrFail());

    $this->get('/admin')->assertOk()->assertDontSee('Nomor WhatsApp belum diisi');
    $this->artisan('ops:site-status')->expectsOutputToContain('Nomor WhatsApp: 6281111111111')->assertSuccessful();

    $global = app(GlobalSettings::class);
    $global->contact = [...$global->contact, 'whatsapp' => ''];
    $global->save();

    $this->get('/admin')->assertOk()->assertSee('Nomor WhatsApp belum diisi');
    $this->artisan('ops:site-status')->expectsOutputToContain('Nomor WhatsApp: KOSONG')->assertSuccessful();
});

it('mencatat klik WhatsApp ke GA4 dengan cluster, posisi_tombol, dan halaman', function () {
    $js = file_get_contents(resource_path('js/lib/analytics.ts'));

    expect($js)->toContain("'click_whatsapp'")
        ->toContain('cluster: link.dataset.cluster')
        ->toContain('posisi_tombol: link.dataset.position')
        ->toContain('halaman,');
});
