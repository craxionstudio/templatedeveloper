<?php

namespace App\Support;

use App\Enums\BenefitCategory;
use App\Models\Benefit;
use Illuminate\Support\Str;

/**
 * Isi awal Bank Benefit. Idempotent per slug: benefit yang belum ada dibuat, yang sudah ada
 * (mungkin sudah diubah admin) tidak disentuh.
 */
class BenefitCatalog
{
    /**
     * @return array<string, list<array{0: string, 1: string, 2?: string}>> kategori => [[nama, ikon, slug?], …]
     */
    public static function defaults(): array
    {
        return [
            BenefitCategory::Pembayaran->value => [
                ['Tanpa DP', 'tag'],
                ['Free Biaya KPR', 'gift'],
                ['Free BPHTB', 'gift'],
                ['Free PPN', 'gift'],
                ['Free Biaya Surat (AJB/BBN)', 'check', 'free-biaya-surat'],
                ['Free IPL', 'house'],
            ],
            BenefitCategory::BonusUnit->value => [
                ['Free Kitchen Set', 'gift'],
                ['Free Water Heater', 'zap'],
                ['Free Solar Panel', 'zap'],
                ['Free CCTV', 'shield'],
                ['Free Smart Door Lock', 'shield'],
                ['Free Smart Home System', 'zap'],
            ],
            BenefitCategory::Material->value => [
                ['Full Marmer', 'house'],
                ['Full Imported Marble', 'house'],
            ],
            BenefitCategory::Diskon->value => [
                ['Diskon', 'tag'],
            ],
        ];
    }

    /**
     * @return int jumlah benefit baru
     */
    public static function seed(): int
    {
        $created = 0;
        $order = 0;

        foreach (self::defaults() as $category => $items) {
            foreach ($items as $item) {
                [$name, $icon] = $item;
                $order++;
                $benefit = Benefit::query()->firstOrCreate(['slug' => $item[2] ?? Str::slug($name)], [
                    'name' => $name,
                    'category' => $category,
                    'icon' => $icon,
                    'sort_order' => $order,
                    'is_active' => true,
                ]);
                $created += (int) $benefit->wasRecentlyCreated;
            }
        }

        return $created;
    }
}
