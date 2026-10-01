<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Kategori Bank Benefit. Urutan case = urutan kelompok di Detail Rumah.
 */
enum BenefitCategory: string implements HasLabel
{
    case Pembayaran = 'pembayaran';
    case BonusUnit = 'bonus_unit';
    case Material = 'material';
    case Diskon = 'diskon';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pembayaran => 'Pembayaran',
            self::BonusUnit => 'Bonus unit',
            self::Material => 'Material',
            self::Diskon => 'Diskon',
        };
    }

    public function order(): int
    {
        return array_search($this, self::cases(), true);
    }
}
