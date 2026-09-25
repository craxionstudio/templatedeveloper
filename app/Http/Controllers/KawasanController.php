<?php

namespace App\Http\Controllers;

use App\Models\Cluster;
use App\Models\Kawasan;
use App\Presenters\ClusterCard;
use App\Presenters\Image;
use App\Presenters\KawasanCard;
use App\Settings\KawasanDetailPageSettings;
use App\Settings\ListingPageSettings;
use App\Support\Breadcrumbs;
use App\Support\Cta;
use App\Support\PageMeta;
use App\Support\Rupiah;
use App\Support\StructuredData;
use Inertia\Inertia;
use Inertia\Response;

class KawasanController extends Controller
{
    public function show(string $slug, KawasanDetailPageSettings $settings, ListingPageSettings $listing): Response
    {
        // Tidak dipublikasikan / tanpa cluster publik → 404.
        $kawasan = Kawasan::query()->visible()->where('slug', $slug)->with(['seo', 'media', 'galleryItems.media'])->first();

        // Kawasan yang sudah dihapus → 410 Gone.
        abort_if(! $kawasan && Kawasan::onlyTrashed()->where('slug', $slug)->exists(), 410);
        abort_unless($kawasan, 404);

        return $this->page($kawasan, $settings, $listing);
    }

    /**
     * Pratinjau admin (termasuk kawasan yang belum dipublikasikan): URL bertanda tangan, noindex.
     */
    public function preview(Kawasan $kawasan, KawasanDetailPageSettings $settings, ListingPageSettings $listing): Response
    {
        abort_unless(auth()->user()?->canManageContent(), 403);

        $kawasan->load(['seo', 'media', 'galleryItems.media']);

        return $this->page($kawasan, $settings, $listing, preview: true);
    }

    private function page(Kawasan $kawasan, KawasanDetailPageSettings $settings, ListingPageSettings $listing, bool $preview = false): Response
    {
        $clusters = $kawasan->publishedClusters()->with(ClusterCard::with())->get();
        $typesCount = $clusters->sum('house_types_count');

        $hero = $settings->section('hero');
        $about = $settings->section('about');
        $clustersSection = $settings->section('clusters');
        $others = $settings->section('others');
        $seoPattern = $settings->section('seo');
        $values = ['name' => $kawasan->name, 'kawasan' => $kawasan->name, 'summary' => $kawasan->summary, 'clusters' => $clusters->count(), 'types' => $typesCount];

        $crumbs = Breadcrumbs::make([
            [Breadcrumbs::nav('/properti', 'Properti'), '/properti'],
            [$listing->section('toggle')['kawasan_label'], '/properti/kawasan'],
            [$kawasan->name],
        ]);
        $image = $kawasan->getFirstMediaUrl('hero') ?: null;

        return Inertia::render('Kawasan/Show', [
            'meta' => PageMeta::make(
                PageMeta::fill($seoPattern['title_pattern'], $values),
                PageMeta::fill($seoPattern['description_pattern'], $values),
                [...($kawasan->seo?->toArray() ?? []), 'canonical_url' => $kawasan->seo?->canonical_url ?: $kawasan->publicPath()],
                noindex: $preview,
                image: $image,
                section: 'kawasan',
                breadcrumbs: $crumbs,
                schema: [
                    StructuredData::kawasan($kawasan, $image),
                    StructuredData::itemList('Cluster di '.$kawasan->name, $clusters->map(fn (Cluster $c) => ['name' => $c->name, 'url' => $c->publicPath()])),
                ],
            ),
            'preview' => $preview,
            'breadcrumbs' => $crumbs,
            'kawasan' => [
                'name' => $kawasan->name,
                'summary' => $kawasan->summary,
                'image' => Image::media($kawasan, 'hero', $kawasan->hero_alt, 'Foto kawasan '.$kawasan->name),
            ],
            'hero' => [
                'eyebrow' => $hero['eyebrow'],
                'stats' => array_values(array_filter([
                    $kawasan->area_ha !== null ? ['value' => rtrim(rtrim(number_format((float) $kawasan->area_ha, 2, ',', '.'), '0'), ',').' ha', 'label' => $hero['stat_area_label']] : null,
                    ['value' => (string) $clusters->count(), 'label' => $hero['stat_cluster_label']],
                    ['value' => Rupiah::short($clusters->min('price_min')) ?? '–', 'label' => $hero['stat_price_label']],
                ])),
            ],
            'about' => $about['enabled'] ? [
                'eyebrow' => $about['eyebrow'],
                'title' => $kawasan->about_title ?: $kawasan->name,
                'description' => $kawasan->description,
                'brochure' => $kawasan->getFirstMediaUrl('brochure') ?: null,
                'brochureLabel' => $about['brochure_label'],
                'mapUrl' => $kawasan->map_embed_url ?: ($kawasan->latitude ? "https://www.google.com/maps?q={$kawasan->latitude},{$kawasan->longitude}" : null),
                'mapLabel' => $about['map_label'],
            ] : null,
            'facilities' => $settings->section('facilities')['enabled'] && filled($kawasan->facilities) ? [
                'title' => $settings->section('facilities')['title'],
                'items' => array_values($kawasan->facilities),
            ] : null,
            'clusters' => $clustersSection['enabled'] ? [
                'eyebrow' => PageMeta::fill($clustersSection['eyebrow'], $values),
                'title' => PageMeta::fill($clustersSection['title'], $values),
                'link' => ['label' => $clustersSection['link_label'], 'url' => $clustersSection['link_url'].'?kawasan='.$kawasan->slug],
                'items' => ClusterCard::collection($clusters),
            ] : null,
            'others' => $others['enabled'] ? $this->others($kawasan, $others) : null,
            'cta' => Cta::resolve($settings->section('cta')),
        ]);
    }

    /**
     * @param  array<string, mixed>  $section
     * @return array<string, mixed>|null
     */
    private function others(Kawasan $kawasan, array $section): ?array
    {
        $items = Kawasan::query()->visible()->whereKeyNot($kawasan->getKey())->ordered()
            ->with(KawasanCard::with())->limit((int) $section['limit'])->get();

        return $items->isEmpty() ? null : [
            'eyebrow' => $section['eyebrow'],
            'title' => $section['title'],
            'link' => ['label' => $section['link_label'], 'url' => $section['link_url']],
            'items' => KawasanCard::collection($items),
        ];
    }
}
