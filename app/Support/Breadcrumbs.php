<?php

namespace App\Support;

use App\Settings\GlobalSettings;
use App\Settings\NavigationSettings;

/**
 * Breadcrumb yang tampil (dan nanti dipakai BreadcrumbList JSON-LD di Milestone 5).
 */
class Breadcrumbs
{
    /**
     * @param  array<int, array{0: string, 1?: string|null}>  $items  [label, url]; item terakhir = halaman aktif
     * @return list<array{label: string, url: string|null}>
     */
    public static function make(array $items): array
    {
        $home = app(GlobalSettings::class)->section('labels')['home'];

        return collect([[$home, '/'], ...$items])
            ->map(fn (array $item, int $index) => ['label' => $item[0], 'url' => $item[1] ?? null])
            ->values()
            ->all();
    }

    /**
     * Label menu untuk URL tertentu (Menu Navigasi), supaya breadcrumb ikut berubah saat
     * label menu diubah admin.
     */
    public static function nav(string $url, string $fallback): string
    {
        return collect(app(NavigationSettings::class)->header_items)->firstWhere('url', $url)['label'] ?? $fallback;
    }
}
