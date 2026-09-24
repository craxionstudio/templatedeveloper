<?php

namespace App\Presenters;

use App\Models\Facility;

class FacilityCard
{
    /**
     * @return array<string, mixed>
     */
    public static function make(Facility $facility, string $everywhereLabel = 'Semua kawasan'): array
    {
        return [
            'id' => $facility->id,
            'name' => $facility->name,
            'description' => $facility->description,
            'icon' => $facility->icon ?? $facility->category?->icon,
            'category' => $facility->category ? ['name' => $facility->category->name, 'slug' => $facility->category->slug, 'icon' => $facility->category->icon] : null,
            'kawasan' => $facility->kawasan?->name ?? $everywhereLabel,
            'image' => Image::media($facility, 'photo', $facility->photo_alt, 'Foto '.$facility->name),
        ];
    }

    /**
     * @param  iterable<Facility>  $facilities
     * @return list<array<string, mixed>>
     */
    public static function collection(iterable $facilities, string $everywhereLabel = 'Semua kawasan'): array
    {
        return collect($facilities)->map(fn (Facility $facility) => self::make($facility, $everywhereLabel))->values()->all();
    }

    /**
     * @return list<string>
     */
    public static function with(): array
    {
        return ['category', 'kawasan', 'media'];
    }
}
