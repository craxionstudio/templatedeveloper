<?php

namespace App\Support;

use App\Models\Facility;
use App\Models\HouseType;
use App\Models\Kawasan;
use Database\Seeders\ContentSeeder;
use Database\Seeders\PropertySeeder;
use Illuminate\Support\Str;

/**
 * Nilai dummy yang dikarang saat seeding (tidak ada di desain). Dipakai untuk menampilkan
 * penanda "Data dummy" di admin selama nilainya belum diganti. Daftar lengkap: docs/DATA-DUMMY.md.
 */
class DummyData
{
    public const HINT = 'Data dummy';

    public const TOOLTIP = 'Nilai ini dikarang untuk data contoh, ganti dengan data asli. Daftar lengkap di docs/DATA-DUMMY.md.';

    /**
     * Kolom tipe rumah yang dikarang → indeks di PropertySeeder::clusterData().
     */
    private const HOUSE_TYPE_FIELDS = ['building_area' => 3, 'bathrooms' => 6, 'carports' => 8, 'units_available' => 11];

    /**
     * @return array<string, array<string, int>> "cluster-slug/tipe-slug" => [kolom => nilai]
     */
    public static function houseTypes(): array
    {
        $values = [];

        foreach (PropertySeeder::clusterData() as $cluster) {
            foreach ($cluster[7] as $type) {
                $key = Str::slug($cluster[0]).'/'.Str::slug($type[0]);
                $values[$key] = array_map(fn (int $index) => $type[$index], self::HOUSE_TYPE_FIELDS);
            }
        }

        return $values;
    }

    /**
     * @return array<string, string|null> slug fasilitas => slug kawasan (null = semua kawasan)
     */
    public static function facilityKawasan(): array
    {
        return collect(ContentSeeder::facilityData())
            ->mapWithKeys(fn (array $facility) => [Str::slug($facility[0]) => $facility[2]])
            ->all();
    }

    /**
     * @return array<string, list<array<string, string>>> slug kawasan => fasilitas kawasan dummy
     */
    public static function kawasanFacilities(): array
    {
        return collect(PropertySeeder::kawasanData())
            ->reject(fn (array $kawasan) => $kawasan['name'] === 'Arunika Garden') // dari desain 02c
            ->mapWithKeys(fn (array $kawasan) => [Str::slug($kawasan['name']) => $kawasan['facilities']])
            ->all();
    }

    public static function isHouseTypeValue(?HouseType $type, string $field, mixed $state): bool
    {
        if (! $type || $state === null || $state === '') {
            return false;
        }

        $dummy = self::houseTypes()[$type->cluster?->slug.'/'.$type->slug][$field] ?? null;

        return $dummy !== null && (int) $state === $dummy;
    }

    public static function isFacilityKawasan(?Facility $facility, mixed $kawasanId): bool
    {
        if (! $facility || ! array_key_exists($facility->slug, self::facilityKawasan())) {
            return false;
        }

        $dummySlug = self::facilityKawasan()[$facility->slug];
        $currentSlug = $kawasanId ? Kawasan::query()->whereKey($kawasanId)->value('slug') : null;

        return $dummySlug === $currentSlug;
    }

    /**
     * @param  array<int|string, array<string, mixed>>|null  $state
     */
    public static function isKawasanFacilities(?Kawasan $kawasan, ?array $state): bool
    {
        $dummy = $kawasan ? (self::kawasanFacilities()[$kawasan->slug] ?? null) : null;

        if ($dummy === null || $state === null) {
            return false;
        }

        $normalize = fn (array $items) => array_map(
            fn (array $item) => [(string) ($item['icon'] ?? ''), (string) ($item['title'] ?? ''), (string) ($item['description'] ?? '')],
            array_values($items),
        );

        return $normalize($state) === $normalize($dummy);
    }
}
