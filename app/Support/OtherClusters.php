<?php

namespace App\Support;

use App\Models\Cluster;
use Illuminate\Support\Collection;

/**
 * Cluster yang tidak punya halaman sendiri (tampil_sebagai = daftar): hanya tampil sebagai nama di
 * /properti/cluster-lainnya, dikelompokkan per kawasan, dan di section "Cluster Lainnya" Detail Kawasan.
 */
class OtherClusters
{
    public const PATH = '/properti/cluster-lainnya';

    /** Anchor grup cluster tanpa kawasan. */
    public const STANDALONE_ANCHOR = 'lainnya';

    public const STANDALONE_TITLE = 'Lainnya';

    public const TITLE = 'Cluster Lainnya di BSD City';

    public const DESCRIPTION = 'Kawasan dan cluster yang telah tumbuh bersama BSD City.';

    public const META_DESCRIPTION = 'Kenali kawasan dan cluster yang telah tumbuh bersama BSD City, dikelompokkan per kawasan.';

    /**
     * Grup per kawasan (urut abjad nama kawasan), cluster tanpa kawasan di grup "Lainnya" paling bawah.
     *
     * @return list<array{id: string, title: string, clusters: list<string>}>
     */
    public static function groups(): array
    {
        return Cluster::query()->listedOnly()
            ->with('kawasan:id,name,slug')
            ->get(['id', 'name', 'kawasan_id'])
            ->groupBy(fn (Cluster $cluster): string => $cluster->kawasan?->slug ?? self::STANDALONE_ANCHOR)
            ->map(fn (Collection $clusters, string $anchor): array => [
                'id' => $anchor,
                'title' => $clusters->first()->kawasan?->name ?? self::STANDALONE_TITLE,
                'clusters' => self::names($clusters),
            ])
            ->sort(fn (array $a, array $b): int => [$a['id'] === self::STANDALONE_ANCHOR, mb_strtolower($a['title'])] <=> [$b['id'] === self::STANDALONE_ANCHOR, mb_strtolower($b['title'])])
            ->values()
            ->all();
    }

    /**
     * Nama cluster urut abjad (tanpa link, foto, atau status).
     *
     * @param  Collection<int, Cluster>  $clusters
     * @return list<string>
     */
    public static function names(Collection $clusters): array
    {
        return $clusters->pluck('name')->sort(fn (string $a, string $b): int => strnatcasecmp($a, $b))->values()->all();
    }

    public static function exists(): bool
    {
        return Cluster::query()->listedOnly()->exists();
    }
}
