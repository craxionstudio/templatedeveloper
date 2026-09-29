<?php

namespace App\Presenters;

use App\Models\Cluster;
use App\Models\Promo;
use Illuminate\Support\Collection;

/**
 * Kartu promo di Detail Rumah & Detail Kawasan. Catatan internal (sumber) tidak ikut dikirim.
 */
class PromoCard
{
    /**
     * @param  iterable<Cluster>  $clusters  cluster yang ditautkan di kartu (Detail Kawasan)
     * @return array<string, mixed>
     */
    public static function make(Promo $promo, string $periodPrefix, iterable $clusters = []): array
    {
        $period = $promo->period_label ?: $promo->ends_at?->translatedFormat('j F Y');

        return [
            'id' => $promo->id,
            'label' => $promo->label,
            'title' => $promo->title,
            'description' => $promo->description,
            'period' => $period ? trim($periodPrefix.' '.$period) : null,
            'items' => array_values($promo->items ?? []),
            'clusters' => collect($clusters)->map(fn (Cluster $cluster) => ['name' => $cluster->name, 'url' => $cluster->publicPath()])->values()->all(),
        ];
    }

    /**
     * @param  Collection<int, Promo>  $promos
     * @return list<array<string, mixed>>
     */
    public static function collection(Collection $promos, string $periodPrefix): array
    {
        return $promos->map(fn (Promo $promo) => self::make($promo, $periodPrefix))->values()->all();
    }
}
