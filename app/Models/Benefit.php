<?php

namespace App\Models;

use App\Enums\BenefitCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Bank Benefit: benefit tetap (Tanpa DP, Free BPHTB, …) yang dicentang per cluster.
 */
class Benefit extends Model
{
    protected $fillable = ['name', 'slug', 'category', 'icon', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return [
            'category' => BenefitCategory::class,
            'is_active' => 'boolean',
        ];
    }

    public function clusters(): BelongsToMany
    {
        return $this->belongsToMany(Cluster::class)
            ->using(BenefitCluster::class)
            ->withPivot(['id', 'teks_tampil', 'urutan'])
            ->withTimestamps();
    }

    public function clusterBenefits(): HasMany
    {
        return $this->hasMany(BenefitCluster::class);
    }

    /**
     * Teks di website untuk benefit yang dimuat lewat relasi cluster: teks_tampil, atau nama benefit.
     */
    public function displayText(): string
    {
        $custom = $this->pivot?->teks_tampil ?? null;

        return filled($custom) ? trim($custom) : $this->name;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where($this->qualifyColumn('is_active'), true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy($this->qualifyColumn('sort_order'))->orderBy($this->qualifyColumn('name'));
    }

    /**
     * Pilihan Select dikelompokkan per kategori (admin).
     *
     * @return array<string, array<int, string>>
     */
    public static function groupedOptions(): array
    {
        return static::query()->ordered()->get()
            ->sortBy(fn (self $benefit) => $benefit->category->order())
            ->groupBy(fn (self $benefit) => $benefit->category->getLabel())
            ->map(fn ($items) => $items->mapWithKeys(fn (self $benefit) => [
                $benefit->id => $benefit->name.($benefit->is_active ? '' : ' (nonaktif)'),
            ])->all())
            ->all();
    }
}
