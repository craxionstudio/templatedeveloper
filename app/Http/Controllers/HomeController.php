<?php

namespace App\Http\Controllers;

use App\Enums\PromoPlacement;
use App\Models\Area;
use App\Models\Article;
use App\Models\Cluster;
use App\Models\DeveloperProfile;
use App\Models\Facility;
use App\Models\FutureDevelopment;
use App\Models\Promo;
use App\Presenters\ArticleCard;
use App\Presenters\ClusterCard;
use App\Presenters\FacilityCard;
use App\Presenters\Image;
use App\Settings\HomePageSettings;
use App\Support\Cta;
use App\Support\DataSource;
use App\Support\PageMeta;
use Illuminate\Database\Eloquent\Builder;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(HomePageSettings $settings): Response
    {
        $hero = $settings->section('hero');

        return Inertia::render('Home', [
            'meta' => PageMeta::make($hero['title'], $settings->section('seo')['meta_description'], $settings->section('seo'), isHome: true),
            'hero' => $hero['enabled'] ? [
                'eyebrow' => $hero['eyebrow'],
                'title' => $hero['title'],
                'description' => $hero['description'],
                'image' => Image::path($hero['image'], $hero['image_alt']),
                'imageMobile' => $hero['image_mobile'] ? Image::path($hero['image_mobile'], $hero['image_alt']) : null,
                'videoUrl' => $hero['video_url'] ?: null,
                'primary' => ['label' => $hero['primary_label'], 'url' => $hero['primary_url']],
                'secondary' => ['label' => $hero['secondary_label'], 'url' => $hero['secondary_url'] ?: null],
            ] : null,
            'about' => $this->about($settings->section('about')),
            'promos' => $this->promos($settings->section('promo')),
            'listing' => $this->listing($settings->section('listing')),
            'region' => $this->region($settings->section('region')),
            'facilities' => $this->facilities($settings->section('facilities')),
            'developments' => $this->developments($settings->section('developments')),
            'articles' => $this->articles($settings->section('articles')),
            'cta' => Cta::resolve($settings->section('cta')),
        ]);
    }

    /**
     * @param  array<string, mixed>  $section
     * @return array<string, mixed>|null
     */
    private function about(array $section): ?array
    {
        if (! $section['enabled']) {
            return null;
        }

        $profile = DeveloperProfile::current();

        return [
            'eyebrow' => $section['eyebrow'],
            'title' => $section['use_profile'] || blank($section['title']) ? $profile->headline : $section['title'],
            'description' => $section['use_profile'] || blank($section['description']) ? $profile->description : $section['description'],
            'history' => $section['use_profile'] ? trim(strip_tags((string) $profile->history)) : null,
            'quote' => $profile->vision_quote,
            'stats' => array_values($profile->stats ?? []),
            'photo' => Image::media($profile, 'photo', $profile->photo_alt, 'Foto kantor / kawasan'),
            'secondaryPhoto' => Image::media($profile, 'secondary_photo', $profile->secondary_photo_alt, 'Foto tim'),
            'link' => ['label' => $section['link_label'], 'url' => $section['link_url']],
        ];
    }

    /**
     * @param  array<string, mixed>  $section
     * @return array<string, mixed>|null
     */
    private function promos(array $section): ?array
    {
        if (! $section['enabled']) {
            return null;
        }

        $promos = DataSource::resolve(
            [...$section, 'limit' => null],
            Promo::query()->active()->placement(PromoPlacement::HomeBanner)->with('media'),
            fn (Builder $q) => $q->orderBy('sort_order'),
        );

        if ($promos->isEmpty()) {
            return null;
        }

        return [
            'autoplay' => (bool) $section['autoplay'],
            'items' => $promos->map(fn (Promo $promo) => [
                'id' => $promo->id,
                'label' => $promo->label,
                'title' => $promo->title,
                'description' => $promo->description,
                'cta' => $promo->cta_label ? ['label' => $promo->cta_label, 'url' => $promo->cta_url ?: '/properti'] : null,
                'image' => Image::media($promo, 'image_desktop', $promo->image_alt, 'Visual promo'),
                'imageMobile' => $promo->hasMedia('image_mobile') ? Image::media($promo, 'image_mobile', $promo->image_alt) : null,
            ])->values()->all(),
        ];
    }

    /**
     * @param  array<string, mixed>  $section
     * @return array<string, mixed>|null
     */
    private function listing(array $section): ?array
    {
        if (! $section['enabled']) {
            return null;
        }

        $clusters = DataSource::resolve(
            $section,
            Cluster::query()->published()->with(ClusterCard::with()),
            fn (Builder $q) => $q->orderByDesc('is_featured')->ordered(),
            4,
        );

        return [
            'eyebrow' => $section['eyebrow'],
            'title' => $section['title'],
            'items' => ClusterCard::collection($clusters),
            'button' => ['label' => $section['button_label'], 'url' => $section['button_url']],
        ];
    }

    /**
     * @param  array<string, mixed>  $section
     * @return array<string, mixed>|null
     */
    private function region(array $section): ?array
    {
        if (! $section['enabled']) {
            return null;
        }

        $area = Area::current();

        return [
            'eyebrow' => $section['eyebrow'],
            'title' => $section['title'],
            'description' => $section['description'],
            'map' => Image::media($area, 'map', null, 'Peta kawasan & aksesibilitas'),
            'mapEmbedUrl' => $area->map_embed_url,
            'badge' => $area->map_badge_label ? ['label' => $area->map_badge_label, 'value' => $area->map_badge_value] : null,
            'points' => array_values($area->advantages ?? []),
        ];
    }

    /**
     * @param  array<string, mixed>  $section
     * @return array<string, mixed>|null
     */
    private function facilities(array $section): ?array
    {
        if (! $section['enabled']) {
            return null;
        }

        $facilities = DataSource::resolve(
            $section,
            Facility::query()->published()->with(FacilityCard::with()),
            fn (Builder $q) => $q->orderByDesc('is_featured')->ordered(),
            4,
        );

        return [
            'eyebrow' => $section['eyebrow'],
            'title' => $section['title'],
            'items' => FacilityCard::collection($facilities),
            'link' => ['label' => $section['link_label'], 'url' => $section['link_url']],
        ];
    }

    /**
     * @param  array<string, mixed>  $section
     * @return array<string, mixed>|null
     */
    private function developments(array $section): ?array
    {
        if (! $section['enabled']) {
            return null;
        }

        $items = DataSource::resolve(
            $section,
            FutureDevelopment::query()->published()->with('media'),
            fn (Builder $q) => $q->ordered(),
            4,
        );

        return [
            'eyebrow' => $section['eyebrow'],
            'title' => $section['title'],
            'description' => $section['description'],
            'disclaimer' => $section['disclaimer'],
            'items' => $items->map(fn (FutureDevelopment $item) => [
                'id' => $item->id,
                'target' => $item->target,
                'title' => $item->title,
                'description' => $item->description,
                'status' => $item->status->value,
                'statusLabel' => $item->status->getLabel(),
                'image' => Image::media($item, 'image', $item->image_alt, 'Render '.$item->title),
            ])->values()->all(),
        ];
    }

    /**
     * @param  array<string, mixed>  $section
     * @return array<string, mixed>|null
     */
    private function articles(array $section): ?array
    {
        if (! $section['enabled']) {
            return null;
        }

        $published = Article::query()->published()->with(ArticleCard::with());

        $main = $section['main_article_id']
            ? (clone $published)->find($section['main_article_id'])
            : null;
        $main ??= (clone $published)->where('is_highlight', true)->latest('published_at')->first()
            ?? (clone $published)->latest('published_at')->first();

        if (! $main) {
            return null;
        }

        $others = DataSource::resolve(
            $section,
            (clone $published)->whereKeyNot($main->getKey()),
            fn (Builder $q) => $q->latest('published_at'),
            3,
        );

        return [
            'eyebrow' => $section['eyebrow'],
            'title' => $section['title'],
            'main' => ArticleCard::make($main),
            'items' => ArticleCard::collection($others),
            'link' => ['label' => $section['link_label'], 'url' => $section['link_url']],
        ];
    }
}
