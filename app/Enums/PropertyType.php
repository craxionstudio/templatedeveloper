<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Tipe properti cluster (untuk filter listing).
 */
enum PropertyType: string implements HasLabel
{
    case Rumah = 'rumah';
    case Townhouse = 'townhouse';
    case Ruko = 'ruko';
    case Kavling = 'kavling';

    public function getLabel(): string
    {
        return match ($this) {
            self::Rumah => 'Rumah',
            self::Townhouse => 'Townhouse',
            self::Ruko => 'Ruko',
            self::Kavling => 'Kavling',
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
