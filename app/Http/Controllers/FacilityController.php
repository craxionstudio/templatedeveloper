<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use App\Models\FacilityCategory;
use App\Models\Kawasan;
use App\Presenters\FacilityCard;
use App\Presenters\Image;
use App\Settings\FacilityPageSettings;
use App\Support\Breadcrumbs;
use App\Support\Cta;
use App\Support\PageMeta;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FacilityController extends Controller
{
    public function __invoke(Request $request, FacilityPageSettings $settings): Response
    {
        $header = $settings->section('header');
        $list = $settings->section('list');

        $categories = FacilityCategory::query()
            ->when(filled($list['categories']), fn (Builder $q) => $q->whereKey($list['categories']))
            ->whereHas('facilities', fn (Builder $q) => $q->published())
            ->orderBy('sort_order')
            ->get();

        $kawasans = Kawasan::query()->visible()->ordered()->get(['id', 'name', 'slug']);

        $category = $categories->firstWhere('slug', (string) $request->query('kategori'));
        $kawasan = $kawasans->firstWhere('slug', (string) $request->query('kawasan'));

        $facilities = Facility::query()->published()->ordered()->with(FacilityCard::with())
            ->when($category, fn (Builder $q) => $q->where('facility_category_id', $category->id))
            // Fasilitas "semua kawasan" (kawasan_id null) tetap ikut di setiap filter kawasan.
            ->when($kawasan, fn (Builder $q) => $q->where(fn (Builder $q) => $q->whereNull('kawasan_id')->orWhere('kawasan_id', $kawasan->id)))
            ->get();

        $crumbs = Breadcrumbs::make([[Breadcrumbs::nav('/fasilitas', 'Fasilitas')]]);

        return Inertia::render('Fasilitas/Index', [
            // Filter kategori/kawasan = noindex, follow; canonical ke /fasilitas.
            'meta' => PageMeta::make($header['title'], $header['description'], $settings->section('seo'), noindex: $category !== null || $kawasan !== null, section: 'fasilitas', breadcrumbs: $crumbs),
            'breadcrumbs' => $crumbs,
            'header' => [
                'eyebrow' => $header['eyebrow'],
                'title' => $header['title'],
                'description' => $header['description'],
                'stats' => array_values($header['stats'] ?? []),
                'images' => collect($header['images'] ?? [])->values()->map(fn (array $img) => Image::path($img['image'] ?? null, $img['alt'] ?? null))->all(),
            ],
            'filters' => [
                'allLabel' => $list['all_label'],
                'categories' => $categories->map(fn (FacilityCategory $c) => ['slug' => $c->slug, 'name' => $c->name, 'icon' => $c->icon])->values()->all(),
                'activeCategory' => $category?->slug,
                'kawasan' => $list['show_kawasan_filter'] ? [
                    'label' => $list['kawasan_filter_label'],
                    'allLabel' => $list['all_kawasan_label'],
                    'options' => $kawasans->map(fn (Kawasan $k) => ['value' => $k->slug, 'label' => $k->name])->values()->all(),
                    'active' => $kawasan?->slug,
                ] : null,
            ],
            'facilities' => FacilityCard::collection($facilities, $list['everywhere_label']),
            'cta' => Cta::resolve($settings->section('cta')),
        ]);
    }
}
