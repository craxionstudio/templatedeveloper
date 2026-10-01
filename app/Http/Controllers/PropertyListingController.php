<?php

namespace App\Http\Controllers;

use App\Enums\ClusterStatus;
use App\Enums\PropertyType;
use App\Models\Area;
use App\Models\Benefit;
use App\Models\Cluster;
use App\Models\Kawasan;
use App\Presenters\ClusterCard;
use App\Presenters\Image;
use App\Presenters\KawasanCard;
use App\Settings\ListingPageSettings;
use App\Support\Breadcrumbs;
use App\Support\Cta;
use App\Support\PageMeta;
use App\Support\Rupiah;
use App\Support\StructuredData;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Produk Listing: tampilan Cluster (/properti) dan tampilan Kawasan (/properti/kawasan).
 * Keduanya dua URL terpisah (bukan tab JS), ter-render SSR.
 */
class PropertyListingController extends Controller
{
    public const FILTERS = ['kawasan', 'tipe', 'kamar', 'harga', 'status', 'benefit'];

    public function clusters(Request $request, ListingPageSettings $settings): Response
    {
        $view = $settings->section('cluster_view');
        $filters = $this->filters($request, $view);
        $sort = array_key_exists((string) $request->query('urut'), $view['sort_options']) ? $request->query('urut') : $view['default_sort'];

        $query = $this->filteredQuery($filters, $view);
        $typesCount = (clone $query)->sum('house_types_count');

        $paginator = $this->sorted($query, $sort)
            ->with(ClusterCard::with())
            ->paginate((int) $view['per_page'])
            ->withQueryString();

        $header = $settings->section('header');
        $seo = $settings->section('seo_cluster');
        $options = $this->filterOptions($view);

        // Satu benefit tanpa filter/urut lain = halaman SEO sendiri ("Rumah Tanpa DP di BSD City"),
        // self-canonical dan boleh diindex. Kombinasi benefit / filter lain = noindex.
        $benefitPage = $this->benefitPage($filters, $request, $seo);

        if ($benefitPage) {
            $header = [...$header, 'title' => $benefitPage['title'], 'description' => $benefitPage['description']];
            $seo = [...$seo, 'meta_title' => $benefitPage['title'], 'meta_description' => $benefitPage['description'], 'canonical_url' => $benefitPage['canonical']];
        }

        if (isset($filters['benefit']) && str_contains($filters['benefit'], ',')) {
            // Kombinasi dari URL: tampil sebagai pilihan sendiri di select filter.
            $labels = collect(explode(',', $filters['benefit']))->map(fn (string $slug) => collect($options['benefit'])->firstWhere('value', $slug)['label'] ?? $slug);
            $options['benefit'][] = ['value' => $filters['benefit'], 'label' => $labels->implode(' + ')];
        }

        $crumbs = Breadcrumbs::make([[Breadcrumbs::nav('/properti', 'Properti')]]);

        return Inertia::render('Properti/Index', [
            // Filter/urut = noindex, follow; canonical ke /properti (page ≥ 2 self-canonical).
            'meta' => PageMeta::make(
                $header['title'],
                $header['description'],
                $seo,
                noindex: ! $benefitPage && ($filters !== [] || $request->filled('urut')),
                section: 'properti',
                breadcrumbs: $crumbs,
                schema: [StructuredData::itemList($header['title'], collect($paginator->items())->map(fn (Cluster $c) => ['name' => $c->name, 'url' => $c->publicPath()]))],
            ),
            'breadcrumbs' => $crumbs,
            ...$this->shared($settings, 'cluster', $benefitPage ? ['title' => $benefitPage['title'], 'description' => $benefitPage['description']] : []),
            'filters' => [
                'active' => $filters,
                'sort' => $sort,
                'visible' => array_values(array_intersect(self::FILTERS, $view['filters'])),
                'labels' => $view['filter_labels'],
                'options' => $options,
                'sortOptions' => collect($view['sort_options'])->map(fn ($label, $value) => ['value' => $value, 'label' => $label])->values()->all(),
                'text' => [
                    'all' => $view['all_label'],
                    'allKawasan' => $view['all_kawasan_label'],
                    'apply' => $view['apply_label'],
                    'mobile' => $view['mobile_filter_label'],
                    'sort' => $view['sort_label'],
                ],
            ],
            'result' => [
                'clusters' => $paginator->total(),
                'types' => (int) $typesCount,
                'template' => $view['result_template'],
            ],
            'clusters' => Inertia::scroll(fn () => $paginator->through(fn (Cluster $cluster) => ClusterCard::make($cluster))),
            'emptyState' => $settings->section('empty_state'),
        ]);
    }

    public function kawasans(ListingPageSettings $settings): Response
    {
        $view = $settings->section('kawasan_view');
        $kawasans = Kawasan::query()->visible()->ordered()->with(KawasanCard::with())->get();
        $standalone = Cluster::query()->published()->standalone()->pricedFirst()->ordered()->with(ClusterCard::with())->get();
        $header = $settings->section('header');

        $crumbs = Breadcrumbs::make([[Breadcrumbs::nav('/properti', 'Properti'), '/properti'], [$settings->section('toggle')['kawasan_label']]]);
        $title = $settings->section('toggle')['kawasan_label'].' — '.$header['title'];

        return Inertia::render('Properti/Kawasan', [
            'meta' => PageMeta::make(
                $title,
                $header['description'],
                $settings->section('seo_kawasan'),
                section: 'kawasan',
                breadcrumbs: $crumbs,
                // Kawasan + cluster mandiri.
                schema: [StructuredData::itemList($title, $kawasans->map(fn (Kawasan $k) => ['name' => $k->name, 'url' => $k->publicPath()])
                    ->concat($standalone->map(fn (Cluster $c) => ['name' => $c->name, 'url' => $c->publicPath()])))],
            ),
            'breadcrumbs' => $crumbs,
            ...$this->shared($settings, 'kawasan'),
            'summary' => PageMeta::fill($view['summary_template'], [
                'kawasan' => $kawasans->count(),
                'clusters' => $kawasans->sum(fn (Kawasan $k) => $k->publishedClusters->count()),
                'standalone' => $standalone->count(),
            ]),
            'kawasanEyebrow' => $view['kawasan_eyebrow'],
            'viewLabel' => $view['view_button_label'],
            'kawasans' => KawasanCard::collection($kawasans),
            'standalone' => $view['standalone']['enabled'] && $standalone->isNotEmpty() ? [
                'eyebrow' => $view['standalone']['eyebrow'],
                'title' => $view['standalone']['title'],
                'description' => $view['standalone']['description'],
                'items' => ClusterCard::collection($standalone),
            ] : null,
        ]);
    }

    /**
     * Header, toggle, dan CTA yang sama untuk kedua tampilan. $headerText menimpa judul/deskripsi
     * header (halaman per benefit).
     *
     * @param  array{title?: string, description?: string}  $headerText
     * @return array<string, mixed>
     */
    private function shared(ListingPageSettings $settings, string $active, array $headerText = []): array
    {
        $header = [...$settings->section('header'), ...$headerText];
        $toggle = $settings->section('toggle');
        $area = Area::current();

        $kawasanCount = Kawasan::query()->visible()->count();
        $clusterCount = Cluster::query()->published()->count();

        return [
            'header' => [
                'eyebrow' => $header['eyebrow'],
                'title' => $header['title'],
                'description' => $header['description'],
                'image' => $header['image'] ? Image::path($header['image'], $header['image_alt']) : Image::media($area, 'hero', $area->hero_alt, $header['image_alt']),
                'imageMobile' => $header['image_mobile'] ? Image::path($header['image_mobile'], $header['image_alt']) : null,
                'stats' => [
                    ['value' => (string) $kawasanCount, 'label' => $header['stat_kawasan_label']],
                    ['value' => (string) $clusterCount, 'label' => $header['stat_cluster_label']],
                    ['value' => Rupiah::short(Cluster::query()->published()->min('price_min')) ?? '–', 'label' => $header['stat_price_label']],
                ],
            ],
            'toggle' => [
                'active' => $active,
                'items' => [
                    ['key' => 'cluster', 'label' => $toggle['cluster_label'], 'url' => '/properti', 'count' => $clusterCount],
                    ['key' => 'kawasan', 'label' => $toggle['kawasan_label'], 'url' => '/properti/kawasan', 'count' => $kawasanCount],
                ],
            ],
            'cta' => Cta::resolve($settings->section('cta')),
        ];
    }

    /**
     * Filter aktif yang valid dari query string. ?benefit= menerima beberapa slug dipisah koma
     * (cluster harus punya semuanya) dan selalu dibaca walau filternya tidak ditampilkan.
     *
     * @param  array<string, mixed>  $view
     * @return array<string, string>
     */
    private function filters(Request $request, array $view): array
    {
        $options = $this->filterOptions($view);

        $filters = collect(self::FILTERS)
            ->reject(fn (string $key) => $key === 'benefit')
            ->filter(fn (string $key) => in_array($key, $view['filters'], true))
            ->mapWithKeys(fn (string $key) => [$key => (string) $request->query($key, '')])
            ->filter(fn (string $value, string $key) => $value !== '' && collect($options[$key])->contains('value', $value))
            ->all();

        $benefits = collect(explode(',', (string) $request->query('benefit', '')))
            ->map(fn (string $slug) => trim($slug))
            ->filter(fn (string $slug) => collect($options['benefit'])->contains('value', $slug))
            ->unique()
            ->values();

        if ($benefits->isNotEmpty()) {
            $filters['benefit'] = $benefits->implode(',');
        }

        return $filters;
    }

    /**
     * @param  array<string, string>  $filters
     * @param  array<string, mixed>  $seo
     * @return array{title: string, description: string, canonical: string}|null
     */
    private function benefitPage(array $filters, Request $request, array $seo): ?array
    {
        if (array_keys($filters) !== ['benefit'] || str_contains($filters['benefit'], ',') || $request->filled('urut')) {
            return null;
        }

        $benefit = Benefit::query()->active()->where('slug', $filters['benefit'])->first();

        if (! $benefit) {
            return null;
        }

        $page = (int) $request->query('page');

        return [
            'title' => PageMeta::fill($seo['benefit_title_pattern'], ['benefit' => $benefit->name]),
            'description' => PageMeta::fill($seo['benefit_description_pattern'], ['benefit' => $benefit->name]),
            'canonical' => '/properti?benefit='.$benefit->slug.($page > 1 ? '&page='.$page : ''),
        ];
    }

    /**
     * @param  array<string, mixed>  $view
     * @return array<string, list<array{value: string, label: string}>>
     */
    private function filterOptions(array $view): array
    {
        $kawasans = Kawasan::query()->visible()->ordered()->get(['name', 'slug'])
            ->map(fn (Kawasan $k) => ['value' => $k->slug, 'label' => $k->name])->all();

        if (Cluster::query()->published()->standalone()->exists()) {
            $kawasans[] = ['value' => Cluster::STANDALONE_FILTER, 'label' => $view['standalone_label']];
        }

        $usedTypes = Cluster::query()->published()->distinct()->pluck('property_type')->map(fn ($t) => $t instanceof PropertyType ? $t->value : $t)->all();

        return [
            'kawasan' => $kawasans,
            'tipe' => collect(PropertyType::cases())->filter(fn (PropertyType $t) => in_array($t->value, $usedTypes, true))
                ->map(fn (PropertyType $t) => ['value' => $t->value, 'label' => $t->getLabel()])->values()->all(),
            'kamar' => collect($view['bedroom_options'])->values()
                ->map(fn ($n, $i) => ['value' => (string) $n, 'label' => $n.($i === count($view['bedroom_options']) - 1 ? '+' : '').' KT'])->all(),
            'harga' => collect($view['price_ranges'])->values()->map(fn (array $range, int $i) => ['value' => (string) ($i + 1), 'label' => $range['label']])->all(),
            // Tanpa "Sold out": label sold out tidak boleh tampil di website.
            'status' => collect(ClusterStatus::cases())->reject(fn (ClusterStatus $s) => $s === ClusterStatus::SoldOut)
                ->map(fn (ClusterStatus $s) => ['value' => $s->value, 'label' => $s->getLabel()])->values()->all(),
            // Benefit aktif yang dipakai minimal satu cluster terbit.
            'benefit' => Benefit::query()->active()->ordered()
                ->whereHas('clusters', fn (Builder $q) => $q->published())
                ->get(['slug', 'name'])
                ->map(fn (Benefit $b) => ['value' => $b->slug, 'label' => $b->name])->all(),
        ];
    }

    /**
     * @param  array<string, string>  $filters
     * @param  array<string, mixed>  $view
     */
    private function filteredQuery(array $filters, array $view): Builder
    {
        $query = Cluster::query()->published()->inKawasan($filters['kawasan'] ?? null);

        if (isset($filters['tipe'])) {
            $query->where('property_type', $filters['tipe']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['benefit'])) {
            $query->withBenefits(explode(',', $filters['benefit']));
        }

        if (isset($filters['kamar'])) {
            $bedrooms = (int) $filters['kamar'];
            $isMax = $bedrooms === (int) max($view['bedroom_options']);
            $query->whereHas('houseTypes', fn (Builder $q) => $q->where('is_published', true)->where('bedrooms', $isMax ? '>=' : '=', $bedrooms));
        }

        if (isset($filters['harga'])) {
            $range = array_values($view['price_ranges'])[(int) $filters['harga'] - 1];
            $query->whereHas('houseTypes', fn (Builder $q) => $q->where('is_published', true)
                ->when($range['min'], fn (Builder $q) => $q->where('price_from', '>=', $range['min']))
                ->when($range['max'], fn (Builder $q) => $q->where('price_from', '<', $range['max'])));
        }

        return $query;
    }

    private function sorted(Builder $query, string $sort): Builder
    {
        return match ($sort) {
            // Urut harga: cluster tanpa harga selalu di bawah.
            'harga-terendah' => $query->pricedFirst()->orderBy('price_min')->orderBy('sort_order'),
            'harga-tertinggi' => $query->pricedFirst()->orderByDesc('price_max')->orderBy('sort_order'),
            // "Promo": benefit (aktif) terbanyak di atas, lalu urutan Terbaru.
            'promo' => $query->withCount(['benefits as active_benefits_count' => fn (Builder $q) => $q->active()])
                ->orderByDesc('active_benefits_count')->latestLaunched(),
            // "Terbaru" (default): tanggal launching terbaru, kosong paling bawah; lalu prioritas, lalu nama.
            default => $query->latestLaunched(),
        };
    }
}
