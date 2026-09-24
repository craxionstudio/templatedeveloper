<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * Status ketersediaan cluster.
 */
enum ClusterStatus: string implements HasColor, HasLabel
{
    case ReadyStock = 'ready_stock';
    case Inden = 'inden';
    case SoldOut = 'sold_out';

    public function getLabel(): string
    {
        return match ($this) {
            self::ReadyStock => 'Ready stock',
            self::Inden => 'Inden',
            self::SoldOut => 'Sold out',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::ReadyStock => 'success',
            self::Inden => 'warning',
            self::SoldOut => 'gray',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $case) => [$case->value => $case->getLabel()])->all();
    }
}
