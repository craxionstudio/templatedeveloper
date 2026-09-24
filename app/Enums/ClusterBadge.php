<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * Badge di kartu cluster.
 */
enum ClusterBadge: string implements HasColor, HasLabel
{
    case Terlaris = 'terlaris';
    case Baru = 'baru';
    case Promo = 'promo';
    case Segera = 'segera';

    public function getLabel(): string
    {
        return match ($this) {
            self::Terlaris => 'Terlaris',
            self::Baru => 'Baru',
            self::Promo => 'Promo',
            self::Segera => 'Segera',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Terlaris => 'primary',
            self::Baru => 'success',
            self::Promo => 'warning',
            self::Segera => 'info',
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
