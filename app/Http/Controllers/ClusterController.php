<?php

namespace App\Http\Controllers;

use App\Enums\PromoPlacement;
use App\Models\Cluster;
use App\Models\GalleryItem;
use App\Models\HouseType;
use App\Presenters\ClusterCard;
use App\Presenters\Image;
use App\Settings\ClusterDetailPageSettings;
use App\Settings\GlobalSettings;
use App\Support\Breadcrumbs;
use App\Support\Cta;
use App\Support\DataSource;
use App\Support\PageMeta;
use App\Support\Rupiah;
use App\Support\SiteLayout;
use App\Support\StructuredData;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Detail Rumah: satu halaman per cluster, tab tipe rumah di dalamnya (?tipe= opsional).
 */
class ClusterController extends Controller
{
    public function show(Request $request, string $slug, ClusterDetailPageSettings $settings, GlobalSettings $global): Response
    {
        $cluster = Cluster::query()->published()->where('slug', $slug)
            ->with(['kawasan', 'seo', 'media', 'galleryItems.media', 'publishedHouseTypes.media'])
            ->first();

        // Cluster yang sudah dihapus → 410 Gone (kecuali ada redirect di Redirect Manager).
        abort_if(! $cluster && Cluster::onlyTrashed()->where('slug', $slug)->exists(), 410);
        abort_unless($cluster, 404);

        return $this->page($request, $cluster, $cluster->publishedHouseTypes, $settings, $global);
    }

    /**
     * Pratinjau admin (termasuk cluster/tipe yang belum dipublikasikan): URL bertanda tangan, noindex.
     */
    public function preview(Request $request, Cluster $cluster, ClusterDetailPageSettings $settings, GlobalSettings $global): Response
    {
        abort_unless(auth()->user()?->canManageContent(), 403);

        $cluster->load(['kawasan', 'seo', 'media', 'galleryItems.media', 'houseTypes.media']);

        return $this->page($request, $cluster, $cluster->houseTypes->sortBy('sort_order')->values(), $settings, $global, preview: true);
    }

    /**
     * @param  Collection<int, HouseType>  $types
     */
    private function page(Request $request, Cluster $cluster, Collection $types, ClusterDetailPageSettings $settings, GlobalSettings $global, bool $preview = false): Response
    {
        $selected = $types->firstWhere('slug', (string) $request->query('tipe')) ?? $types->first();

        $sections = $settings->section('sections');
        $pricing = $settings->section('pricing');
        $form = $settings->section('form');
        $contact = $global->section('contact');
        $mobile = $global->section('mobile');
        $seoPattern = $settings->section('seo');
        $values = [
            'name' => $cluster->name,
            'cluster' => $cluster->name,
            'building_type' => $cluster->building_type,
            'kawasan' => $cluster->kawasan?->name,
            'summary' => $cluster->summary,
        ];

        $marketingWhatsapp = $cluster->marketing_whatsapp ?: ($form['marketing_whatsapp'] ?: $contact['whatsapp']);
        $whatsappTemplate = $form['whatsapp_message'];

        $crumbs = Breadcrumbs::make(array_values(array_filter([
            [Breadcrumbs::nav('/properti', 'Properti'), '/properti'],
            $cluster->kawasan ? [$cluster->kawasan->name, $cluster->kawasan->publicPath()] : null,
            [$cluster->name],
        ])));
        $images = $cluster->galleryItems->map(fn (GalleryItem $item) => $item->getFirstMediaUrl('image'))->filter()->values()->all();

        return Inertia::render('Cluster/Show', [
            // ?tipe= tidak mengubah canonical (tetap /properti/{slug}).
            'meta' => PageMeta::make(
                PageMeta::fill($seoPattern['title_pattern'], $values),
                PageMeta::fill($seoPattern['description_pattern'], $values) ?: strip_tags((string) $cluster->description),
                [...($cluster->seo?->toArray() ?? []), 'canonical_url' => $cluster->seo?->canonical_url ?: $cluster->publicPath()],
                noindex: $preview,
                image: $images[0] ?? null,
                section: 'rumah',
                breadcrumbs: $crumbs,
                schema: [StructuredData::cluster($cluster, $types, $images)],
            ),
            'preview' => $preview,
            'breadcrumbs' => $crumbs,
            'cluster' => [
                'id' => $cluster->id,
                'name' => $cluster->name,
                'url' => $cluster->publicPath(),
                'kawasan' => $cluster->kawasan ? ['name' => $cluster->kawasan->name, 'url' => $cluster->kawasan->publicPath()] : null,
                'buildingType' => $cluster->building_type,
                'badge' => $cluster->badge?->getLabel(),
                'status' => $cluster->status->getLabel(),
                'address' => $cluster->address,
                'description' => $cluster->description,
                'legality' => $cluster->legality,
                'bookingFee' => Rupiah::short($cluster->booking_fee),
                'brochure' => $cluster->getFirstMediaUrl('brochure') ?: null,
                'pricelist' => $cluster->getFirstMediaUrl('pricelist') ?: null,
                'specifications' => array_values($cluster->specifications ?? []),
            ],
            'gallery' => [
                'items' => $cluster->galleryItems->map(fn (GalleryItem $item) => [
                    ...Image::media($item, 'image', $item->alt, $cluster->name),
                    'caption' => $item->caption,
                ])->values()->all(),
                // Placeholder selama galeri belum diunggah (label dari desain 03).
                'placeholders' => ['Foto fasad utama', 'Ruang keluarga', 'Dapur', 'Kamar utama', 'Taman belakang'],
                'videoUrl' => $cluster->video_url,
                'tourUrl' => $cluster->tour_360_url,
            ],
            'types' => $types->map(fn (HouseType $type) => [
                'id' => $type->id,
                'slug' => $type->slug,
                'name' => $type->name,
                'lotSize' => $type->lot_size,
                'landArea' => $type->land_area,
                'buildingArea' => $type->building_area,
                'bedrooms' => $type->bedroomsLabel(),
                'bathrooms' => $type->bathrooms,
                'floors' => $type->floors,
                'carports' => $type->carports,
                'price' => Rupiah::short($type->price_from),
                'installment' => Rupiah::short($type->installment_from),
                'unitsAvailable' => $type->units_available,
                'floorplan' => Image::media($type, 'floorplan', $type->floorplan_alt, 'Denah tipe '.$type->name),
                'whatsappUrl' => SiteLayout::whatsappUrl($marketingWhatsapp, PageMeta::fill($whatsappTemplate, ['cluster' => $cluster->name, 'type' => $type->name])) ?? '/kontak',
            ])->values()->all(),
            'selectedType' => $selected?->slug,
            'pricing' => [
                'priceLabel' => $pricing['price_label'],
                'priceNote' => $cluster->price_note ?: $pricing['price_note'],
                'installmentLabel' => $pricing['installment_label'],
                'installmentNote' => $cluster->installment_note ?: $pricing['installment_note'],
                'perMonth' => $global->section('labels')['per_month'],
                'bookingFeeLabel' => $pricing['booking_fee_label'],
                'bookingFeeNote' => $cluster->booking_fee_note ?: $pricing['booking_fee_note'],
                'kprLink' => $pricing['kpr_link_url'] ? ['label' => $pricing['kpr_link_label'], 'url' => $pricing['kpr_link_url']] : ['label' => $pricing['kpr_link_label'], 'url' => '/kontak'],
            ],
            'promo' => $sections['promo']['enabled'] ? $this->promo($cluster, $sections['promo']) : null,
            'sections' => [
                'specs' => $sections['specs']['enabled'] ? $sections['specs']['title'] : null,
                'types' => $sections['types']['enabled'] ? PageMeta::fill($sections['types']['title'], ['cluster' => $cluster->name]) : null,
                'description' => $sections['description']['enabled'] ? $sections['description']['title'] : null,
            ],
            'specLabels' => $settings->section('spec_labels'),
            'downloads' => [
                'brochure' => $global->section('labels')['download_brochure'],
                'pricelist' => $global->section('labels')['download_pricelist'],
            ],
            'marketing' => [
                'name' => $cluster->marketing_name ?: $form['marketing_name'],
                'title' => $cluster->marketing_title ?: $form['marketing_title'],
                'photo' => $cluster->hasMedia('marketing_photo')
                    ? Image::media($cluster, 'marketing_photo', null, 'Foto '.($cluster->marketing_name ?: 'marketing'))
                    : Image::path($form['marketing_photo'], 'Foto '.$form['marketing_name']),
            ],
            'form' => [
                'nameLabel' => $form['name_label'],
                'namePlaceholder' => $form['name_placeholder'],
                'whatsappLabel' => $form['whatsapp_label'],
                'whatsappPlaceholder' => $form['whatsapp_placeholder'],
                'submitLabel' => $form['submit_label'],
                'whatsappButtonLabel' => $form['whatsapp_button_label'],
                'surveyButtonLabel' => $form['survey_button_label'],
                'surveyUrl' => $global->section('cta')['visit_url'],
            ],
            'mobileBar' => [
                'priceLabel' => $mobile['sticky_price_label'],
                'whatsappLabel' => $mobile['sticky_whatsapp_label'],
                'surveyLabel' => $mobile['sticky_survey_label'],
            ],
            'others' => $sections['others']['enabled'] ? $this->others($cluster, $sections['others'], $settings->section('others')) : null,
            'cta' => Cta::resolve(
                $settings->section('cta'),
                PageMeta::fill($whatsappTemplate, ['cluster' => $cluster->name, 'type' => $selected?->name]),
                ['clusterId' => $cluster->id, 'houseTypeId' => $selected?->id],
            ),
        ]);
    }

    /**
     * @param  array<string, mixed>  $section
     * @return array<string, mixed>|null
     */
    private function promo(Cluster $cluster, array $section): ?array
    {
        $promo = $cluster->promos()->active()->placement(PromoPlacement::Detail)->orderBy('sort_order')->first();

        if (! $promo || blank($promo->items)) {
            return null;
        }

        $period = $promo->period_label ?: $promo->ends_at?->translatedFormat('j F Y');

        return [
            'title' => $section['title'],
            'period' => $period ? trim($section['period_prefix'].' '.$period) : null,
            'items' => array_values($promo->items),
        ];
    }

    /**
     * "Listing lainnya": otomatis mengutamakan cluster lain di kawasan yang sama.
     *
     * @param  array<string, mixed>  $section
     * @param  array<string, mixed>  $source
     * @return array<string, mixed>|null
     */
    private function others(Cluster $cluster, array $section, array $source): ?array
    {
        $items = DataSource::resolve(
            $source,
            Cluster::query()->published()->whereKeyNot($cluster->getKey())->with(ClusterCard::with()),
            fn (Builder $q) => $q
                ->orderByRaw('CASE WHEN kawasan_id = ? THEN 0 ELSE 1 END', [$cluster->kawasan_id ?? 0])
                ->ordered(),
            3,
        );

        return $items->isEmpty() ? null : [
            'eyebrow' => $section['eyebrow'],
            'title' => $section['title'],
            'link' => ['label' => $section['link_label'], 'url' => $section['link_url']],
            'items' => ClusterCard::collection($items),
        ];
    }
}
