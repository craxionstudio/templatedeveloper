<?php

namespace App\Presenters;

use App\Models\Cluster;
use App\Models\HouseType;
use App\Support\Rupiah;
use Illuminate\Support\Collection;

/**
 * Data kartu cluster (satu komponen untuk Listing, Detail Kawasan, Beranda, "Listing lainnya").
 * Semua rentang dihitung dari tipe rumah yang dipublikasikan.
 */
class ClusterCard
{
    /**
     * @return array<string, mixed>
     */
    public static function make(Cluster $cluster): array
    {
        /** @var Collection<int, HouseType> $types */
        $types = $cluster->relationLoaded('publishedHouseTypes')
            ? $cluster->publishedHouseTypes
            : $cluster->publishedHouseTypes()->get();

        $cover = $cluster->relationLoaded('galleryItems') ? $cluster->galleryItems->first() : $cluster->galleryItems()->first();

        return [
            'id' => $cluster->id,
            'name' => $cluster->name,
            'url' => $cluster->publicPath(),
            'buildingType' => $cluster->building_type,
            'badge' => $cluster->badge?->getLabel(),
            'kawasan' => $cluster->kawasan ? ['name' => $cluster->kawasan->name, 'url' => $cluster->kawasan->publicPath()] : null,
            'typesCount' => $types->count(),
            'types' => $types->map(fn (HouseType $type): string => trim($type->name.($type->lot_size ? ' · '.$type->lot_size : '')))->values()->all(),
            'landArea' => self::range($types->min('land_area'), $types->max('land_area')),
            'bedrooms' => self::bedrooms($types),
            'price' => Rupiah::range($types->min('price_from'), $types->max('price_from')),
            'installment' => Rupiah::short($types->min('installment_from')),
            'image' => [
                'url' => $cover?->getFirstMediaUrl('image') ?: null,
                'alt' => $cover?->alt ?: 'Foto cluster '.$cluster->name,
            ],
        ];
    }

    /**
     * @param  iterable<Cluster>  $clusters
     * @return list<array<string, mixed>>
     */
    public static function collection(iterable $clusters): array
    {
        return collect($clusters)->map(fn (Cluster $cluster) => self::make($cluster))->values()->all();
    }

    /**
     * Relasi yang perlu di-eager load supaya kartu tidak memicu N+1.
     *
     * @return list<string>
     */
    public static function with(): array
    {
        return ['kawasan', 'publishedHouseTypes', 'galleryItems.media'];
    }

    private static function range(?int $min, ?int $max): ?string
    {
        if ($min === null) {
            return null;
        }

        return $min === $max ? (string) $min : "{$min}–{$max}";
    }

    /**
     * "3 – 4+1" / "3" / "4+1" — dari tipe dengan kamar tidur paling sedikit & paling banyak.
     *
     * @param  Collection<int, HouseType>  $types
     */
    private static function bedrooms(Collection $types): ?string
    {
        if ($types->isEmpty()) {
            return null;
        }

        $sorted = $types->sortBy(fn (HouseType $type) => $type->bedroomsSortValue());
        $min = $sorted->first()->bedroomsLabel();
        $max = $sorted->last()->bedroomsLabel();

        return $min === $max ? $min : "{$min} – {$max}";
    }
}
