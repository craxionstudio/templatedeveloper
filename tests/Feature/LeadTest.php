<?php

use App\Filament\Pages\Settings\ManageGlobalSettings;
use App\Filament\Resources\Leads\Tables\LeadsTable;
use App\Jobs\SendLeadWebhook;
use App\Jobs\SendMetaLeadEvent;
use App\Models\Cluster;
use App\Models\Lead;
use App\Models\NewsletterSubscriber;
use App\Models\User;
use App\Notifications\NewLeadNotification;
use App\Services\MetaConversions;
use App\Services\Turnstile;
use App\Settings\GlobalSettings;
use App\Support\Attribution;
use App\Support\Phone;
use App\Support\Secret;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\RateLimiter;
use Inertia\Testing\AssertableInertia as Assert;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed();
    RateLimiter::clear('lead-min:127.0.0.1');
    // Semua request HTTP keluar harus di-fake; SSR (juga lewat HTTP) dimatikan di test ini.
    config(['inertia.ssr.enabled' => false]);
    Http::preventStrayRequests();
});

/**
 * @param  array<string, mixed>  $tracking
 * @param  array<string, mixed>  $notifications
 */
function configureTracking(array $tracking = [], array $notifications = []): void
{
    $settings = app(GlobalSettings::class);
    $settings->tracking = [...$settings->tracking, ...$tracking];
    $settings->notifications = [...$settings->notifications, ...$notifications];
    $settings->save();
}

/**
 * @return array<string, mixed>
 */
function leadInput(array $overrides = []): array
{
    $cluster = Cluster::query()->where('slug', 'vega-garden')->firstOrFail();

    return [
        'name' => 'Budi Santoso',
        'whatsapp' => '0812-3456-7890',
        'email' => 'Budi@Example.com',
        'cluster_id' => $cluster->id,
        'house_type_id' => $cluster->houseTypes()->where('slug', 'deneb')->value('id'),
        'consent' => true,
        'website' => '',
        'source_page' => '/properti/vega-garden?tipe=deneb',
        'source_position' => 'sidebar',
        ...$overrides,
    ];
}

it('menormalisasi nomor WhatsApp Indonesia ke 62…', function (string $input, ?string $expected) {
    expect(Phone::normalize($input))->toBe($expected);
})->with([
    ['0812-3456-7890', '6281234567890'],
    ['+62 812 3456 7890', '6281234567890'],
    ['6281234567890', '6281234567890'],
    ['81234567890', '6281234567890'],
    ['021 555 1234', null],
    ['0812', null],
    ['abc', null],
]);

it('menangkap UTM, fbclid, gclid, landing page, dan referrer di kunjungan pertama', function () {
    $response = $this->withHeader('Referer', 'https://www.google.com/')
        ->get('/properti?utm_source=facebook&utm_medium=cpc&utm_campaign=launch&fbclid=FB123&gclid=G456');

    $cookie = collect($response->headers->getCookies())->first(fn ($c) => $c->getName() === Attribution::COOKIE);
    // Cookie Laravel terenkripsi dengan prefix "{hash}|".
    $data = json_decode(explode('|', decrypt($cookie->getValue(), false), 2)[1], true);

    expect($cookie->getExpiresTime())->toBeGreaterThan(now()->addDays(29)->getTimestamp())
        ->and($data)->toMatchArray([
            'utm_source' => 'facebook',
            'utm_medium' => 'cpc',
            'utm_campaign' => 'launch',
            'fbclid' => 'FB123',
            'gclid' => 'G456',
            'landing_page' => url('/properti?fbclid=FB123&gclid=G456&utm_campaign=launch&utm_medium=cpc&utm_source=facebook'),
            'referrer' => 'https://www.google.com/',
        ]);
});

it('menyimpan lead lengkap dengan UTM, fbclid, gclid, dan landing page', function () {
    $this->withCookie(Attribution::COOKIE, json_encode([
        'utm_source' => 'facebook', 'utm_medium' => 'cpc', 'utm_campaign' => 'launch-garden',
        'utm_content' => 'video-a', 'utm_term' => 'rumah serpong',
        'fbclid' => 'FB123', 'gclid' => 'G456',
        'landing_page' => 'https://arunika.test/properti?utm_source=facebook',
        'referrer' => 'https://facebook.com/',
    ]))
        ->post('/lead', leadInput())
        ->assertSessionHasNoErrors()
        ->assertRedirect('/terima-kasih');

    $lead = Lead::query()->sole();

    expect($lead)
        ->name->toBe('Budi Santoso')
        ->whatsapp->toBe('6281234567890')
        ->email->toBe('budi@example.com')
        ->cluster->slug->toBe('vega-garden')
        ->houseType->slug->toBe('deneb')
        ->source_page->toBe('/properti/vega-garden?tipe=deneb')
        ->source_position->toBe('sidebar')
        ->utm_source->toBe('facebook')
        ->utm_medium->toBe('cpc')
        ->utm_campaign->toBe('launch-garden')
        ->utm_content->toBe('video-a')
        ->utm_term->toBe('rumah serpong')
        ->fbclid->toBe('FB123')
        ->gclid->toBe('G456')
        ->landing_page->toBe('https://arunika.test/properti?utm_source=facebook')
        ->referrer->toBe('https://facebook.com/')
        ->consent->toBeTrue()
        ->ip_hash->toHaveLength(64)
        ->ip_hash->not->toContain('127.0.0.1')
        ->event_id->toBeUuid();
});

it('menolak data yang tidak valid', function (array $overrides, string $field) {
    $this->post('/lead', leadInput($overrides))->assertSessionHasErrors($field);

    expect(Lead::query()->count())->toBe(0);
})->with([
    'nama kosong' => [['name' => ''], 'name'],
    'WA bukan seluler' => [['whatsapp' => '021 555 1234'], 'whatsapp'],
    'tanpa persetujuan' => [['consent' => false], 'consent'],
    'email salah' => [['email' => 'bukan-email'], 'email'],
    'tipe dari cluster lain' => [['house_type_id' => 999999], 'house_type_id'],
]);

it('mengabaikan bot yang mengisi honeypot tanpa menyimpan lead', function () {
    Notification::fake();

    $this->post('/lead', leadInput(['website' => 'https://spam.example']))
        ->assertRedirect('/terima-kasih');

    expect(Lead::query()->count())->toBe(0);
    Notification::assertNothingSent();

    // Tidak ada event konversi untuk bot.
    $this->get('/terima-kasih')->assertInertia(fn (Assert $page) => $page->where('conversion', null));
});

it('membatasi jumlah submit per IP (rate limit)', function () {
    foreach (range(1, 5) as $i) {
        $this->post('/lead', leadInput(['whatsapp' => '08123456789'.$i]))->assertRedirect('/terima-kasih');
    }

    $this->from('/kontak')->post('/lead', leadInput(['whatsapp' => '081234567899']))
        ->assertRedirect('/kontak')
        ->assertSessionHasErrors('form');

    expect(Lead::query()->count())->toBe(5);
});

it('melewati Turnstile kalau key kosong, dan memverifikasinya kalau diisi', function () {
    expect(Turnstile::enabled())->toBeFalse();
    $this->post('/lead', leadInput())->assertSessionHasNoErrors();

    configureTracking(['turnstile_site_key' => '0x4AAA', 'turnstile_secret_key' => Secret::encrypt('rahasia-turnstile')]);
    Http::fake([Turnstile::ENDPOINT => Http::sequence()->push(['success' => false])->push(['success' => true])]);

    $this->post('/lead', leadInput(['turnstile_token' => 'token-palsu']))->assertSessionHasErrors('turnstile_token');
    $this->post('/lead', leadInput(['turnstile_token' => 'token-asli']))->assertSessionHasNoErrors();

    expect(Lead::query()->count())->toBe(2);
    Http::assertSent(fn (HttpRequest $request) => $request['secret'] === 'rahasia-turnstile' && $request['response'] === 'token-asli');
    $this->get('/kontak')->assertInertia(fn (Assert $page) => $page->where('site.tracking.turnstileSiteKey', '0x4AAA'));
});

it('mengirim notifikasi email ke beberapa alamat dan webhook lewat queue', function () {
    Notification::fake();
    Queue::fake([SendLeadWebhook::class]);
    configureTracking(notifications: ['emails' => ['marketing@arunika.test', 'sales@arunika.test'], 'webhook_url' => 'https://hooks.example.com/lead']);

    $this->post('/lead', leadInput());

    Notification::assertSentOnDemand(NewLeadNotification::class, fn ($notification, $channels, AnonymousNotifiable $notifiable) => $notifiable->routes['mail'] === ['marketing@arunika.test', 'sales@arunika.test']);
    Queue::assertPushed(SendLeadWebhook::class, fn (SendLeadWebhook $job) => $job->url === 'https://hooks.example.com/lead');
    expect(new NewLeadNotification(Lead::query()->sole()))->toBeInstanceOf(ShouldQueue::class);
});

it('mengirim payload webhook berisi data lead', function () {
    Http::fake(['hooks.example.com/*' => Http::response(['ok' => true])]);
    configureTracking(notifications: ['webhook_url' => 'https://hooks.example.com/lead']);

    $this->post('/lead', leadInput());

    Http::assertSent(fn (HttpRequest $request) => $request->url() === 'https://hooks.example.com/lead'
        && $request['event'] === 'lead.created'
        && $request['lead']['whatsapp'] === '6281234567890'
        && $request['lead']['cluster'] === 'Vega Garden');
});

it('tidak mengirim email atau webhook kalau belum diatur', function () {
    Notification::fake();
    Queue::fake();

    $this->post('/lead', leadInput());

    Notification::assertNothingSent();
    Queue::assertNothingPushed();
});

it('melewati Conversions API tanpa error kalau token kosong', function () {
    Queue::fake();
    configureTracking(['meta_pixel_id' => '1234567890']);

    $this->post('/lead', leadInput())->assertRedirect('/terima-kasih');

    expect(MetaConversions::enabled())->toBeFalse();
    Queue::assertNotPushed(SendMetaLeadEvent::class);
});

it('memakai event_id yang sama untuk Pixel (browser) dan Conversions API (server)', function () {
    Http::fake(['graph.facebook.com/*' => Http::response(['events_received' => 1])]);
    configureTracking(['meta_pixel_id' => '1234567890', 'meta_capi_token' => Secret::encrypt('TOKEN-CAPI')]);

    $this->withUnencryptedCookie('_fbp', 'fb.1.1700000000000.111')
        ->withHeader('User-Agent', 'Mozilla/5.0 Test')
        ->withCookie(Attribution::COOKIE, json_encode(['fbclid' => 'FB123', 'fbclid_at' => '1700000000000']))
        ->post('/lead', leadInput())
        ->assertRedirect('/terima-kasih');

    $lead = Lead::query()->sole();

    // Browser: /terima-kasih mengirim Pixel Lead dengan eventID ini.
    $this->get('/terima-kasih')->assertInertia(fn (Assert $page) => $page
        ->where('conversion.eventId', $lead->event_id)
        ->where('conversion.cluster', 'Vega Garden')
        ->where('conversion.position', 'sidebar'));

    // Server: event Lead dengan event_id yang sama, data pribadi di-hash SHA-256.
    Http::assertSent(function (HttpRequest $request) use ($lead) {
        $event = $request['data'][0];

        return str_starts_with($request->url(), 'https://graph.facebook.com/v21.0/1234567890/events')
            && str_contains($request->url(), 'access_token=TOKEN-CAPI')
            && $event['event_name'] === 'Lead'
            && $event['event_id'] === $lead->event_id
            && $event['action_source'] === 'website'
            && $event['event_source_url'] === url('/properti/vega-garden?tipe=deneb')
            && $event['user_data']['ph'] === [hash('sha256', '6281234567890')]
            && $event['user_data']['em'] === [hash('sha256', 'budi@example.com')]
            && $event['user_data']['fn'] === [hash('sha256', 'budi')]
            && $event['user_data']['ln'] === [hash('sha256', 'santoso')]
            && $event['user_data']['fbp'] === 'fb.1.1700000000000.111'
            && $event['user_data']['fbc'] === 'fb.1.1700000000000.FB123'
            && $event['user_data']['client_user_agent'] === 'Mozilla/5.0 Test'
            && ! str_contains(json_encode($event), 'Budi')
            && ! str_contains(json_encode($event), '6281234567890');
    });

    // Refresh halaman terima kasih tidak menghitung konversi lagi.
    $this->get('/terima-kasih')->assertInertia(fn (Assert $page) => $page->where('conversion', null));
});

it('menampilkan tombol lanjut WhatsApp dengan nama cluster di halaman terima kasih', function () {
    Cluster::query()->where('slug', 'vega-garden')->update(['marketing_whatsapp' => '6281111111111']);

    $this->post('/lead', leadInput());

    $this->get('/terima-kasih')->assertInertia(fn (Assert $page) => $page
        ->where('whatsapp.url', 'https://wa.me/6281111111111?text='.rawurlencode('Halo, saya Budi. Saya baru saja mengisi form untuk Vega Garden.')));
});

it('menyimpan newsletter tanpa duplikat dan menolak honeypot', function () {
    $this->from('/artikel')->post('/newsletter', ['email' => 'Pembaca@Example.com', 'source_page' => '/artikel'])
        ->assertRedirect('/artikel')
        ->assertSessionHas('newsletter', 'subscribed');
    $this->from('/artikel')->post('/newsletter', ['email' => 'pembaca@example.com']);
    $this->from('/artikel')->post('/newsletter', ['email' => 'bot@example.com', 'website' => 'x']);
    $this->from('/artikel')->post('/newsletter', ['email' => 'salah'])->assertSessionHasErrors('email');

    expect(NewsletterSubscriber::query()->pluck('email')->all())->toBe(['pembaca@example.com'])
        ->and(NewsletterSubscriber::query()->value('source'))->toBe('/artikel');
});

it('menyimpan access token CAPI terenkripsi dan tidak menampilkannya ulang', function () {
    $this->actingAs(User::query()->where('email', 'admin@example.com')->firstOrFail());

    Livewire::test(ManageGlobalSettings::class)
        ->fillForm(['tracking.meta_capi_token' => 'TOKEN-RAHASIA', 'tracking.turnstile_secret_key' => 'SECRET-TS'])
        ->call('save')
        ->assertHasNoFormErrors();

    $stored = app(GlobalSettings::class)->tracking;
    expect($stored['meta_capi_token'])->not->toBe('TOKEN-RAHASIA')
        ->and(Secret::decrypt($stored['meta_capi_token']))->toBe('TOKEN-RAHASIA')
        ->and(Secret::decrypt($stored['turnstile_secret_key']))->toBe('SECRET-TS');

    // Tidak dikirim ulang ke browser; field kosong saat disimpan = nilai lama dipertahankan.
    $page = Livewire::test(ManageGlobalSettings::class)->assertDontSee('TOKEN-RAHASIA');
    expect($page->get('data.tracking.meta_capi_token'))->toBeNull();

    $page->fillForm(['tracking.gtm_id' => 'GTM-ABC123'])->call('save')->assertHasNoFormErrors();
    expect(Secret::decrypt(app(GlobalSettings::class)->tracking['meta_capi_token']))->toBe('TOKEN-RAHASIA');

    // Centang "hapus" = dikosongkan.
    Livewire::test(ManageGlobalSettings::class)
        ->fillForm(['tracking.meta_capi_token_clear' => true])
        ->call('save')
        ->assertHasNoFormErrors();
    expect(app(GlobalSettings::class)->tracking['meta_capi_token'])->toBe('');

    // Rahasia tidak pernah ada di props halaman publik.
    expect($this->get('/')->getContent())->not->toContain('SECRET-TS');
});

it('hanya Super Admin yang bisa mengubah tujuan notifikasi lead', function () {
    configureTracking(notifications: ['webhook_url' => 'https://hooks.example.com/asli']);
    $this->actingAs(User::query()->where('email', 'konten@example.com')->firstOrFail());

    Livewire::test(ManageGlobalSettings::class)
        ->assertDontSee('Notifikasi lead')
        ->assertDontSee('hooks.example.com')
        ->set('data.notifications', ['webhook_url' => 'https://penyerang.example/curi'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(app(GlobalSettings::class)->notifications['webhook_url'])->toBe('https://hooks.example.com/asli');
});

it('memasang GTM dan Pixel dari settings di head', function () {
    expect($this->get('/')->getContent())->not->toContain('googletagmanager.com')->not->toContain('fbevents.js');

    configureTracking(['gtm_id' => 'GTM-ABC123', 'meta_pixel_id' => '1234567890']);

    expect($this->get('/')->getContent())
        ->toContain('gtm.js?id=')
        ->toContain('GTM-ABC123')
        ->toContain("fbq('init', '1234567890')")
        ->toContain('ns.html?id=GTM-ABC123');
});

it('menyimpan first-touch UTM terpisah dari UTM terakhir', function () {
    $first = $this->get('/?utm_source=google&utm_medium=cpc&utm_campaign=brand');
    $cookie = collect($first->headers->getCookies())->first(fn ($c) => $c->getName() === Attribution::COOKIE);
    $value = explode('|', decrypt($cookie->getValue(), false), 2)[1];

    // Kunjungan kedua lewat kampanye lain: UTM terakhir berubah, first-touch tetap.
    $second = $this->withCookie(Attribution::COOKIE, $value)->get('/properti?utm_source=facebook&utm_medium=paid_social&utm_campaign=open-house');
    $cookie = collect($second->headers->getCookies())->first(fn ($c) => $c->getName() === Attribution::COOKIE);
    $value = explode('|', decrypt($cookie->getValue(), false), 2)[1];

    expect(json_decode($value, true))->toMatchArray([
        'utm_source' => 'facebook', 'utm_medium' => 'paid_social', 'utm_campaign' => 'open-house',
        'first_utm_source' => 'google', 'first_utm_medium' => 'cpc', 'first_utm_campaign' => 'brand',
        'landing_page' => url('/').'/?utm_campaign=brand&utm_medium=cpc&utm_source=google',
    ]);

    $this->withCookie(Attribution::COOKIE, $value)->post('/lead', leadInput())->assertRedirect('/terima-kasih');

    expect(Lead::query()->sole())
        ->utm_source->toBe('facebook')
        ->utm_campaign->toBe('open-house')
        ->first_utm_source->toBe('google')
        ->first_utm_medium->toBe('cpc')
        ->first_utm_campaign->toBe('brand');
});

it('mengisi first-touch dari kampanye pertama walaupun kunjungan pertama tanpa UTM', function () {
    $direct = $this->get('/');
    $value = explode('|', decrypt(collect($direct->headers->getCookies())->first(fn ($c) => $c->getName() === Attribution::COOKIE)->getValue(), false), 2)[1];

    $campaign = $this->withCookie(Attribution::COOKIE, $value)->get('/?utm_source=instagram&utm_campaign=reels');
    $data = json_decode(explode('|', decrypt(collect($campaign->headers->getCookies())->first(fn ($c) => $c->getName() === Attribution::COOKIE)->getValue(), false), 2)[1], true);

    expect($data)->toMatchArray(['first_utm_source' => 'instagram', 'first_utm_campaign' => 'reels', 'landing_page' => url('/')]);
});

it('menampilkan UTM terakhir dan pertama di detail lead dan ekspor', function () {
    $lead = Lead::query()->create([
        'name' => 'Budi', 'whatsapp' => '6281234567890',
        'utm_source' => 'facebook', 'utm_campaign' => 'open-house',
        'first_utm_source' => 'google', 'first_utm_medium' => 'cpc', 'first_utm_campaign' => 'brand',
    ]);
    $this->actingAs(User::query()->where('email', 'admin@example.com')->firstOrFail());

    $this->get('/admin/leads/'.$lead->id.'/edit')
        ->assertOk()
        ->assertSee('UTM terakhir: campaign: open-house')
        ->assertSee('UTM pertama: source / medium: google / cpc')
        ->assertSee('UTM pertama: campaign: brand');

    $columns = LeadsTable::exportColumns();
    expect(array_keys($columns))->toContain('utm_source', 'first_utm_source', 'first_utm_medium', 'first_utm_campaign')
        ->and($columns['first_utm_campaign']($lead))->toBe('brand');
});

it('membatasi maksimal 3 lead per nomor WA per hari', function () {
    foreach (range(1, 3) as $i) {
        $this->post('/lead', leadInput(['whatsapp' => '0812 7777 8888']))->assertSessionHasNoErrors();
    }

    // Format berbeda, nomor sama.
    $this->post('/lead', leadInput(['whatsapp' => '+62 812-7777-8888']))->assertSessionHasErrors('whatsapp');
    // Nomor lain dari IP yang sama tetap bisa.
    $this->post('/lead', leadInput(['whatsapp' => '0812 7777 9999']))->assertSessionHasNoErrors();

    expect(Lead::query()->where('whatsapp', '6281277778888')->count())->toBe(3);

    // Setelah 24 jam, nomor itu bisa mengirim lagi.
    $this->travel(25)->hours();
    $this->post('/lead', leadInput(['whatsapp' => '081277778888']))->assertSessionHasNoErrors();
});

it('mengizinkan lebih dari 30 lead per hari dari satu IP (open house), tetap 5 per menit', function () {
    foreach (range(1, 40) as $i) {
        if ($i > 1 && $i % 5 === 1) {
            $this->travel(61)->seconds();
        }

        $this->post('/lead', leadInput(['whatsapp' => '0813'.str_pad((string) $i, 8, '0', STR_PAD_LEFT)]))->assertSessionHasNoErrors();
    }

    expect(Lead::query()->count())->toBe(40);
});

it('memakai Pixel ID khusus CAPI kalau Pixel dipasang lewat GTM', function () {
    Http::fake(['graph.facebook.com/*' => Http::response(['events_received' => 1])]);
    configureTracking(['gtm_id' => 'GTM-ABC123', 'meta_pixel_id' => '', 'meta_capi_pixel_id' => '9876543210', 'meta_capi_token' => Secret::encrypt('TOKEN')]);

    $this->post('/lead', leadInput());

    Http::assertSent(fn (HttpRequest $request) => str_starts_with($request->url(), 'https://graph.facebook.com/v21.0/9876543210/events')
        && $request['data'][0]['event_id'] === Lead::query()->sole()->event_id);

    // Pixel tidak dipasang langsung (hanya lewat GTM), dataLayer selalu ada.
    expect($this->get('/')->getContent())
        ->toContain('window.dataLayer = window.dataLayer || []')
        ->not->toContain('fbevents.js');
});

it('memperingatkan dobel hitung kalau GA4/Pixel langsung diisi bersama GTM', function () {
    configureTracking(['gtm_id' => 'GTM-ABC123', 'meta_pixel_id' => '1234567890']);
    $this->actingAs(User::query()->where('email', 'admin@example.com')->firstOrFail());

    Livewire::test(ManageGlobalSettings::class)
        ->assertSee('Kosongkan jika Pixel/GA4 sudah dipasang lewat GTM, supaya event tidak terhitung dua kali.')
        ->assertSee('GTM juga terisi');
});
