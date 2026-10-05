<?php

use App\Enums\BenefitCategory;
use App\Enums\ClusterBadge;
use App\Enums\ClusterStatus;
use App\Filament\Resources\Benefits\Pages\ListBenefits;
use App\Filament\Resources\Clusters\Pages\EditCluster;
use App\Filament\Resources\Clusters\Pages\ListClusters;
use App\Jobs\SendMetaContactEvent;
use App\Models\Benefit;
use App\Models\BenefitCluster;
use App\Models\Cluster;
use App\Models\User;
use App\Services\MetaConversions;
use App\Settings\ClusterDetailPageSettings;
use App\Settings\GlobalSettings;
use App\Support\BenefitCatalog;
use App\Support\Secret;
use App\Support\Sitemaps;
use Filament\Actions\Testing\TestAction;
use Illuminate\Support\Facades\Queue;
use Inertia\Testing\AssertableInertia as Assert;
use Livewire\Livewire;

/*
 * Bank Benefit: benefit tetap yang dicentang per cluster (pengganti sistem Promo).
 */

beforeEach(function () {
    $this->seed();
    Sitemaps::flush();
});

function benefitCluster(string $slug): Cluster
{
    return Cluster::query()->where('slug', $slug)->firstOrFail();
}

/**
 * @param  array<string, ?string>  $benefits  slug benefit => teks tampil
 */
function giveBenefits(string $cluster, array $benefits): Cluster
{
    $model = benefitCluster($cluster);
    $order = 0;

    foreach ($benefits as $slug => $text) {
        BenefitCluster::query()->create([
            'cluster_id' => $model->id,
            'benefit_id' => Benefit::query()->where('slug', $slug)->value('id'),
            'teks_tampil' => $text,
            'urutan' => ++$order,
        ]);
    }

    return $model;
}

function setTracking(array $tracking): void
{
    $settings = app(GlobalSettings::class);
    $settings->tracking = [...$settings->tracking, ...$tracking];
    $settings->save();
}

it('mengisi Bank Benefit awal per kategori secara idempotent tanpa menimpa ubahan admin', function () {
    expect(Benefit::count())->toBe(15)
        ->and(Benefit::where('category', BenefitCategory::Pembayaran)->pluck('slug')->all())
        ->toBe(['tanpa-dp', 'free-biaya-kpr', 'free-bphtb', 'free-ppn', 'free-biaya-surat', 'free-ipl'])
        ->and(Benefit::where('category', BenefitCategory::Diskon)->pluck('name')->all())->toBe(['Diskon']);

    Benefit::where('slug', 'tanpa-dp')->update(['name' => 'DP 0%']);

    expect(BenefitCatalog::seed())->toBe(0)
        ->and(Benefit::count())->toBe(15)
        ->and(Benefit::where('slug', 'tanpa-dp')->value('name'))->toBe('DP 0%');
});

it('menampilkan section Promo & Benefit di Detail Rumah, dikelompokkan per kategori, dengan tombol WA', function () {
    $this->get('/properti/vega-garden')->assertInertia(fn (Assert $page) => $page->where('benefits', null));

    $cluster = giveBenefits('vega-garden', ['diskon' => 'Diskon hingga 13%', 'free-kitchen-set' => null, 'tanpa-dp' => null]);
    $cluster->update(['marketing_whatsapp' => '081234567890']);

    $this->get('/properti/vega-garden')->assertOk()->assertInertia(fn (Assert $page) => $page
        ->where('benefits.title', 'Promo & Benefit')
        ->where('benefits.groups', [
            ['category' => 'Pembayaran', 'items' => [['icon' => 'tag', 'text' => 'Tanpa DP']]],
            ['category' => 'Bonus unit', 'items' => [['icon' => 'gift', 'text' => 'Free Kitchen Set']]],
            ['category' => 'Diskon', 'items' => [['icon' => 'tag', 'text' => 'Diskon hingga 13%']]],
        ])
        ->where('benefits.disclaimer', '*Syarat dan ketentuan berlaku dan dapat berubah sewaktu-waktu.')
        ->where('benefits.buttonLabel', 'Dapatkan informasi lengkapnya via WhatsApp')
        ->where('benefits.whatsappUrl', 'https://wa.me/6281234567890?text='.rawurlencode('Halo, saya tertarik dengan promo di Vega Garden. Boleh minta informasi lengkapnya?')));
});

it('memakai nomor WA global kalau cluster tidak punya, dan menyembunyikan benefit nonaktif', function () {
    $global = app(GlobalSettings::class);
    $global->contact = [...$global->contact, 'whatsapp' => '0811111111'];
    $global->save();

    giveBenefits('vega-garden', ['free-cctv' => null, 'full-marmer' => null]);
    Benefit::where('slug', 'full-marmer')->update(['is_active' => false]);

    $this->get('/properti/vega-garden')->assertInertia(fn (Assert $page) => $page
        ->has('benefits.groups', 1)
        ->where('benefits.groups.0.items.0.text', 'Free CCTV')
        ->where('benefits.whatsappUrl', fn (string $url) => str_starts_with($url, 'https://wa.me/62811111111?text=')));
});

it('menampilkan teks syarat & pesan WA dari settings admin', function () {
    $settings = app(ClusterDetailPageSettings::class);
    $settings->sections = [...$settings->sections, 'benefits' => [...$settings->sections['benefits'], 'disclaimer' => '*S&K berlaku.', 'whatsapp_message' => 'Info promo {cluster} dong']];
    $settings->save();
    $global = app(GlobalSettings::class);
    $global->contact = [...$global->contact, 'whatsapp' => '0811111111'];
    $global->save();

    giveBenefits('vega-garden', ['free-ppn' => null]);

    $this->get('/properti/vega-garden')->assertInertia(fn (Assert $page) => $page
        ->where('benefits.disclaimer', '*S&K berlaku.')
        ->where('benefits.whatsappUrl', fn (string $url) => urldecode($url) === 'https://wa.me/62811111111?text=Info promo Vega Garden dong'));
});

it('menampilkan maksimal 3 chip benefit tanpa tanda * dan badge Promo di kartu cluster', function () {
    benefitCluster('vega-garden')->update(['badge' => null]);
    giveBenefits('vega-garden', ['tanpa-dp' => null, 'free-bphtb' => null, 'diskon' => 'Diskon 10%', 'free-cctv' => null, 'free-ipl' => null]);

    $cards = collect($this->get('/properti')->inertiaProps('clusters.data'));
    $vega = $cards->firstWhere('name', 'Vega Garden');
    $other = $cards->firstWhere('name', 'Hana Residence');

    expect($vega)->toMatchArray(['benefits' => ['Tanpa DP', 'Free BPHTB', 'Diskon 10%'], 'benefitsMore' => 2, 'badges' => ['Promo']])
        ->and(json_encode($vega['benefits']))->not->toContain('*')
        ->and($other['benefits'])->toBe([])
        ->and($other['badges'])->not->toContain('Promo');
});

it('tetap memprioritaskan badge admin (mis. "Baru") dengan "Promo" sebagai badge kedua', function () {
    benefitCluster('vega-garden')->update(['badge' => ClusterBadge::Baru]);
    benefitCluster('hana-residence')->update(['badge' => ClusterBadge::Promo]);
    giveBenefits('vega-garden', ['tanpa-dp' => null]);
    giveBenefits('hana-residence', ['free-bphtb' => null]);

    $cards = collect($this->get('/properti')->inertiaProps('clusters.data'));

    expect($cards->firstWhere('name', 'Vega Garden')['badges'])->toBe(['Baru', 'Promo'])
        // Badge admin sudah "Promo": tidak dobel.
        ->and($cards->firstWhere('name', 'Hana Residence')['badges'])->toBe(['Promo']);

    BenefitCluster::query()->where('cluster_id', benefitCluster('vega-garden')->id)->delete();

    expect(collect($this->get('/properti')->inertiaProps('clusters.data'))->firstWhere('name', 'Vega Garden')['badges'])->toBe(['Baru']);
});

it('memfilter /properti per benefit: satu benefit diindex dengan judul sendiri, kombinasi noindex', function () {
    giveBenefits('vega-garden', ['tanpa-dp' => null, 'free-bphtb' => null]);
    giveBenefits('hana-residence', ['tanpa-dp' => null]);

    $this->get('/properti?benefit=tanpa-dp')->assertOk()->assertInertia(fn (Assert $page) => $page
        ->where('clusters.data', fn ($clusters) => collect($clusters)->pluck('name')->sort()->values()->all() === ['Hana Residence', 'Vega Garden'])
        ->where('meta.title', 'Rumah Tanpa DP di BSD City')
        ->where('meta.noindex', false)
        ->where('meta.canonical', url('/properti?benefit=tanpa-dp'))
        ->where('header.title', 'Rumah Tanpa DP di BSD City')
        ->where('filters.active.benefit', 'tanpa-dp'));

    // Kombinasi = cluster yang punya semua benefit; noindex, canonical ke /properti.
    $this->get('/properti?benefit=tanpa-dp,free-bphtb')->assertOk()->assertInertia(fn (Assert $page) => $page
        ->has('clusters.data', 1)
        ->where('clusters.data.0.name', 'Vega Garden')
        ->where('meta.noindex', true)
        ->where('meta.canonical', url('/properti'))
        ->where('filters.options.benefit', fn ($options) => collect($options)->contains('label', 'Tanpa DP + Free BPHTB')));

    // Benefit + filter lain = noindex. Slug tidak dikenal diabaikan.
    $this->get('/properti?benefit=tanpa-dp&kawasan=mandiri')->assertInertia(fn (Assert $page) => $page->where('meta.noindex', true));
    $this->get('/properti?benefit=tidak-ada')->assertInertia(fn (Assert $page) => $page->where('filters.active', [])->has('clusters.data', 9));
});

it('mengurutkan "Promo" dengan cluster ber-benefit terbanyak di atas', function () {
    giveBenefits('hana-residence', ['tanpa-dp' => null]);
    giveBenefits('vega-garden', ['tanpa-dp' => null, 'free-bphtb' => null, 'free-ppn' => null]);

    $names = collect($this->get('/properti?urut=promo')->inertiaProps('clusters.data'))->pluck('name');

    expect($names->take(2)->all())->toBe(['Vega Garden', 'Hana Residence'])
        ->and(collect($this->get('/properti')->inertiaProps('filters.sortOptions'))->pluck('value'))->toContain('promo');
});

it('memuat halaman per benefit yang dipakai di sitemap', function () {
    giveBenefits('vega-garden', ['tanpa-dp' => null]);

    $xml = $this->get('/sitemap-properti.xml')->assertOk()->getContent();

    expect($xml)->toContain(e(url('/properti?benefit=tanpa-dp')))
        ->not->toContain('benefit=free-bphtb');
});

it('tidak menampilkan label Sold out di Detail Rumah maupun filter', function () {
    benefitCluster('vega-garden')->update(['status' => ClusterStatus::SoldOut]);

    $this->get('/properti/vega-garden')->assertInertia(fn (Assert $page) => $page->where('cluster.status', null));
    $this->get('/properti')->assertInertia(fn (Assert $page) => $page
        ->where('filters.options.status', fn ($options) => ! collect($options)->contains('value', 'sold_out')));
});

it('mengirim klik WA ke Meta CAPI dengan event_id yang sama (kalau CAPI aktif)', function () {
    Queue::fake();
    $eventId = (string) Str::uuid();

    // CAPI belum dikonfigurasi: diterima tanpa efek.
    $this->postJson('/track/contact', ['event_id' => $eventId, 'cluster' => 'Vega Garden', 'source' => 'promo_section'])->assertNoContent();
    Queue::assertNothingPushed();
    $this->get('/')->assertInertia(fn (Assert $page) => $page->where('site.tracking.capi', false));

    setTracking(['meta_pixel_id' => '1234567890', 'meta_capi_token' => Secret::encrypt('TOKEN')]);

    $this->postJson('/track/contact', [
        'event_id' => $eventId, 'cluster' => 'Vega Garden', 'source' => 'promo_section', 'page_url' => url('/properti/vega-garden'),
    ], ['Referer' => url('/properti/vega-garden')])->assertNoContent();

    Queue::assertPushed(SendMetaContactEvent::class, fn (SendMetaContactEvent $job) => $job->event['event_id'] === $eventId
        && $job->event['cluster'] === 'Vega Garden'
        && $job->event['source'] === 'promo_section'
        && $job->context['source_url'] === url('/properti/vega-garden'));

    $payload = MetaConversions::contactEvent(['event_id' => $eventId, 'cluster' => 'Vega Garden', 'source' => 'promo_section'], ['ip' => '1.2.3.4']);
    expect($payload)->toMatchArray(['event_name' => 'Contact', 'event_id' => $eventId, 'action_source' => 'website'])
        ->and($payload['custom_data'])->toBe(['method' => 'whatsapp', 'content_name' => 'Vega Garden', 'content_category' => 'promo_section']);

    $this->postJson('/track/contact', ['event_id' => 'bukan-uuid'])->assertUnprocessable();
});

it('mengimpor benefit per cluster dari file update tanpa menimpa ubahan admin', function () {
    $file = storage_path('framework/testing/benefit-update.json');
    @mkdir(dirname($file), 0777, true);
    file_put_contents($file, json_encode(['benefits' => [
        ['cluster_slug' => 'vega-garden', 'benefit_slug' => 'tanpa-dp', 'teks_tampil' => null],
        ['cluster_slug' => 'vega-garden', 'benefit_slug' => 'diskon', 'teks_tampil' => 'Diskon hingga 13%'],
        ['cluster_slug' => 'hana-residence', 'benefit_slug' => 'free-bphtb', 'teks_tampil' => null],
        ['cluster_slug' => 'tidak-ada', 'benefit_slug' => 'tanpa-dp', 'teks_tampil' => null],
    ]]));

    $this->artisan('import:bsd-update', ['path' => $file])->assertSuccessful();
    $this->artisan('import:bsd-update', ['path' => $file])->assertSuccessful();

    expect(BenefitCluster::count())->toBe(3)
        ->and(BenefitCluster::whereHas('benefit', fn ($q) => $q->where('slug', 'diskon'))->value('teks_tampil'))->toBe('Diskon hingga 13%');

    // Admin mengubah teks & melepas satu benefit; benefit buatan admin sendiri juga tidak disentuh.
    $this->travel(5)->seconds();
    $diskon = BenefitCluster::whereHas('benefit', fn ($q) => $q->where('slug', 'diskon'))->first();
    $diskon->update(['teks_tampil' => 'Diskon 15% (admin)']);
    BenefitCluster::whereHas('benefit', fn ($q) => $q->where('slug', 'free-bphtb'))->first()->delete();
    giveBenefits('lyra-residence', ['free-ipl' => 'IPL gratis 1 tahun']);

    file_put_contents($file, json_encode(['benefits' => [
        ['cluster_slug' => 'vega-garden', 'benefit_slug' => 'diskon', 'teks_tampil' => 'Diskon hingga 20%'],
        ['cluster_slug' => 'hana-residence', 'benefit_slug' => 'free-bphtb', 'teks_tampil' => null],
        ['cluster_slug' => 'lyra-residence', 'benefit_slug' => 'free-ipl', 'teks_tampil' => null],
        ['cluster_slug' => 'vega-garden', 'benefit_slug' => 'tanpa-dp', 'teks_tampil' => 'Tanpa DP (KPR)'],
    ]]));
    $this->travel(5)->seconds();
    $this->artisan('import:bsd-update', ['path' => $file])->assertSuccessful();

    expect($diskon->fresh()->teks_tampil)->toBe('Diskon 15% (admin)')
        ->and(BenefitCluster::whereHas('benefit', fn ($q) => $q->where('slug', 'free-bphtb'))->exists())->toBeFalse()
        ->and(BenefitCluster::whereHas('benefit', fn ($q) => $q->where('slug', 'free-ipl'))->value('teks_tampil'))->toBe('IPL gratis 1 tahun')
        // Pivot hasil import yang tidak disentuh admin tetap diperbarui.
        ->and(BenefitCluster::whereHas('benefit', fn ($q) => $q->where('slug', 'tanpa-dp'))->value('teks_tampil'))->toBe('Tanpa DP (KPR)');
});

it('mengelola Bank Benefit dan benefit cluster dari admin', function () {
    $this->actingAs(User::where('email', 'admin@example.com')->first());
    giveBenefits('vega-garden', ['tanpa-dp' => null]);
    giveBenefits('hana-residence', ['tanpa-dp' => null]);

    Livewire::test(ListBenefits::class)
        ->assertCanSeeTableRecords(Benefit::query()->orderBy('sort_order')->limit(10)->get())
        ->assertTableColumnFormattedStateSet('clusters_count', '2 cluster', Benefit::where('slug', 'tanpa-dp')->first());

    // Tab "Promo & Benefit": repeater ke pivot, bisa diurutkan.
    $lyra = benefitCluster('lyra-residence');
    $bphtb = Benefit::where('slug', 'free-bphtb')->value('id');
    $diskon = Benefit::where('slug', 'diskon')->value('id');

    Livewire::test(EditCluster::class, ['record' => $lyra->getRouteKey()])
        ->set('data.clusterBenefits', [
            'a' => ['benefit_id' => $diskon, 'teks_tampil' => 'Diskon hingga 13%'],
            'b' => ['benefit_id' => $bphtb, 'teks_tampil' => null],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($lyra->clusterBenefits()->with('benefit')->get()->map(fn (BenefitCluster $p) => [$p->benefit->slug, $p->teks_tampil, $p->urutan])->all())
        ->toBe([['diskon', 'Diskon hingga 13%', 1], ['free-bphtb', null, 2]]);

    // Tabel cluster: kolom jumlah benefit, filter, dan bulk action tambah/lepas.
    Livewire::test(ListClusters::class)
        ->assertTableColumnExists('cluster_benefits_count')
        ->assertTableColumnExists('cluster_benefits_max_updated_at')
        ->filterTable('benefit', [$bphtb])
        ->assertCanSeeTableRecords([$lyra])
        ->assertCanNotSeeTableRecords([benefitCluster('vega-garden')]);

    $targets = Cluster::whereIn('slug', ['vega-garden', 'hana-residence'])->get();

    Livewire::test(ListClusters::class)
        ->selectTableRecords($targets->modelKeys())
        ->callAction(TestAction::make('tambahBenefit')->table()->bulk(), ['benefit_id' => $bphtb, 'teks_tampil' => ''])
        ->assertHasNoFormErrors();

    expect(BenefitCluster::where('benefit_id', $bphtb)->count())->toBe(3);

    Livewire::test(ListClusters::class)
        ->selectTableRecords($targets->modelKeys())
        ->callAction(TestAction::make('lepasBenefit')->table()->bulk(), ['benefit_id' => Benefit::where('slug', 'tanpa-dp')->value('id')])
        ->assertHasNoFormErrors();

    expect(BenefitCluster::whereHas('benefit', fn ($q) => $q->where('slug', 'tanpa-dp'))->count())->toBe(0);
});
