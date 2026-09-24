<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * Sumber data section: "auto" (query bawaan) atau "manual" (ID pilihan admin, urutan dipertahankan).
 */
class DataSource
{
    /**
     * @param  array<string, mixed>  $section  berisi source, items, limit
     * @param  Builder  $published  query dasar (sudah difilter publik)
     * @param  callable(Builder): Builder  $auto  urutan/filter mode otomatis
     */
    public static function resolve(array $section, Builder $published, callable $auto, ?int $defaultLimit = null): Collection
    {
        $limit = (int) ($section['limit'] ?? $defaultLimit ?? 0) ?: null;

        if (($section['source'] ?? 'auto') === 'manual') {
            $ids = collect($section['items'] ?? [])->map(fn ($id) => (int) (is_array($id) ? ($id['id'] ?? 0) : $id))->filter()->values();

            $items = (clone $published)->whereKey($ids->all())->get()
                ->sortBy(fn ($model) => $ids->search($model->getKey()))
                ->values();

            return $limit ? $items->take($limit)->values() : $items;
        }

        $query = $auto(clone $published);

        return ($limit ? $query->limit($limit) : $query)->get();
    }
}
