<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * Status pengembangan mendatang.
 */
enum DevelopmentStatus: string implements HasColor, HasLabel
{
    case Beroperasi = 'beroperasi';
    case Konstruksi = 'konstruksi';
    case Perencanaan = 'perencanaan';

    public function getLabel(): string
    {
        return match ($this) {
            self::Beroperasi => 'Beroperasi',
            self::Konstruksi => 'Dalam konstruksi',
            self::Perencanaan => 'Perencanaan',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Beroperasi => 'success',
            self::Konstruksi => 'warning',
            self::Perencanaan => 'gray',
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
