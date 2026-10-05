<?php

namespace App\Presenters;

use App\Enums\ClusterBadge;
use App\Models\Benefit;
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
            'badges' => self::badges($cluster),
            // Maksimal 3 chip benefit + "+N" (tanpa tanda *).
            'benefits' => self::benefits($cluster)->take(3)->map(fn (Benefit $benefit) => $benefit->displayText())->values()->all(),
            'benefitsMore' => max(0, self::benefits($cluster)->count() - 3),
            'kawasan' => $cluster->kawasan ? ['name' => $cluster->kawasan->name, 'url' => $cluster->kawasan->pagePath()] : null,
            'typesCount' => $types->count(),
            // Chip nama tipe; tipe tanpa nama (harga tingkat cluster) tidak dibuat chip.
            'types' => $types->filter(fn (HouseType $type) => $type->displayName() !== null)
                ->map(fn (HouseType $type): string => trim($type->name.($type->lot_size ? ' · '.$type->lot_size : '')))->values()->all(),
            'landArea' => self::range(self::positive($types, 'land_area')->min(), self::positive($types, 'land_area')->max()),
            'bedrooms' => self::bedrooms($types->filter(fn (HouseType $type) => $type->bedrooms > 0)),
            // Tanpa harga → null; kartu menampilkan "Hubungi kami untuk harga".
            'price' => Rupiah::range(self::positive($types, 'price_from')->min(), self::positive($types, 'price_from')->max()),
            'installment' => Rupiah::short(self::positive($types, 'installment_from')->min()),
            'image' => Image::media($cover, 'image', $cover?->alt, 'Foto cluster '.$cluster->name),
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
        return ['kawasan', 'publishedHouseTypes', 'galleryItems.media', 'activeBenefits'];
    }

    /**
     * Nilai kolom yang benar-benar diisi (> 0); 0/null = belum ada data.
     *
     * @param  Collection<int, HouseType>  $types
     * @return Collection<int, int>
     */
    private static function positive(Collection $types, string $column): Collection
    {
        return $types->pluck($column)->filter(fn ($value) => $value !== null && $value > 0)->values();
    }

    /**
     * Maksimal 2 badge: badge pilihan admin (mis. "Baru") tetap pertama, lalu "Promo" otomatis
     * kalau cluster punya minimal 1 benefit (tidak dobel kalau badge admin sudah "Promo").
     *
     * @return list<string>
     */
    private static function badges(Cluster $cluster): array
    {
        $badges = $cluster->badge ? [$cluster->badge->getLabel()] : [];

        if (self::benefits($cluster)->isNotEmpty() && $cluster->badge !== ClusterBadge::Promo) {
            $badges[] = ClusterBadge::Promo->getLabel();
        }

        return $badges;
    }

    /**
     * @return Collection<int, Benefit>
     */
    private static function benefits(Cluster $cluster): Collection
    {
        if (! $cluster->relationLoaded('activeBenefits')) {
            $cluster->load('activeBenefits');
        }

        return $cluster->activeBenefits;
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
