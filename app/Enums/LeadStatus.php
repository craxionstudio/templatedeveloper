<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * Status follow-up lead.
 */
enum LeadStatus: string implements HasColor, HasLabel
{
    case Baru = 'baru';
    case Dihubungi = 'dihubungi';
    case Survey = 'survey';
    case Closing = 'closing';
    case Batal = 'batal';

    public function getLabel(): string
    {
        return match ($this) {
            self::Baru => 'Baru',
            self::Dihubungi => 'Dihubungi',
            self::Survey => 'Survey',
            self::Closing => 'Closing',
            self::Batal => 'Batal',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Baru => 'info',
            self::Dihubungi => 'warning',
            self::Survey => 'primary',
            self::Closing => 'success',
            self::Batal => 'gray',
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
