<?php

namespace App\Filament\Pages\Settings;

use App\Enums\PromoPlacement;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Cluster;
use App\Models\Facility;
use App\Models\FacilityCategory;
use App\Models\FutureDevelopment;
use App\Models\Promo;

/**
 * Pilihan item untuk field "sumber data: pilih manual" di settings halaman.
 */
class Options
{
    /** @return array<int, string> */
    public static function clusters(): array
    {
        return Cluster::query()->ordered()->pluck('name', 'id')->all();
    }

    /** @return array<int, string> */
    public static function facilities(): array
    {
        return Facility::query()->ordered()->pluck('name', 'id')->all();
    }

    /** @return array<int, string> */
    public static function facilityCategories(): array
    {
        return FacilityCategory::query()->orderBy('sort_order')->pluck('name', 'id')->all();
    }

    /** @return array<int, string> */
    public static function developments(): array
    {
        return FutureDevelopment::query()->ordered()->pluck('title', 'id')->all();
    }

    /** @return array<int, string> */
    public static function articles(): array
    {
        return Article::query()->latest('published_at')->pluck('title', 'id')->all();
    }

    /** @return array<int, string> */
    public static function articleCategories(): array
    {
        return ArticleCategory::query()->orderBy('sort_order')->pluck('name', 'id')->all();
    }

    /** @return array<int, string> */
    public static function homePromos(): array
    {
        return Promo::query()->where('placement', PromoPlacement::HomeBanner->value)->orderBy('sort_order')->pluck('title', 'id')->all();
    }
}
