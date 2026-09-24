<?php

namespace App\Presenters;

use App\Models\Cluster;
use App\Models\Kawasan;
use App\Support\Rupiah;

class KawasanCard
{
    /**
     * @return array<string, mixed>
     */
    public static function make(Kawasan $kawasan): array
    {
        $clusters = $kawasan->relationLoaded('publishedClusters')
            ? $kawasan->publishedClusters
            : $kawasan->publishedClusters()->get();

        return [
            'id' => $kawasan->id,
            'name' => $kawasan->name,
            'url' => $kawasan->publicPath(),
            'summary' => $kawasan->summary,
            'clustersCount' => $clusters->count(),
            'clusters' => $clusters->map(fn (Cluster $cluster) => $cluster->name)->values()->all(),
            'priceFrom' => Rupiah::short($clusters->min('price_min')),
            'image' => Image::media($kawasan, 'hero', $kawasan->hero_alt, 'Foto kawasan '.$kawasan->name),
        ];
    }

    /**
     * @param  iterable<Kawasan>  $kawasans
     * @return list<array<string, mixed>>
     */
    public static function collection(iterable $kawasans): array
    {
        return collect($kawasans)->map(fn (Kawasan $kawasan) => self::make($kawasan))->values()->all();
    }

    /**
     * @return list<string>
     */
    public static function with(): array
    {
        return ['publishedClusters', 'media'];
    }
}
