<?php

use App\Models\Benefit;
use App\Models\Cluster;
use App\Settings\GlobalSettings;
use App\Settings\PrivacyPageSettings;
use App\Support\WhatsApp;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Inertia\Testing\AssertableInertia as Assert;

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

    $this->get('/kontak')->assertInertia(fn (Assert $page) => $page
        ->where('whatsapp.url', fn (string $url) => waText($url) === 'Halo, saya ingin konsultasi rumah di BSD City.')
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

    $this->get('/kontak')->assertInertia(fn (Assert $page) => $page->missing('form')->has('whatsapp.url'));
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
