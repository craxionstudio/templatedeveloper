<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * Peran user admin.
 */
enum UserRole: string implements HasColor, HasLabel
{
    case SuperAdmin = 'super_admin';
    case AdminKonten = 'admin_konten';
    case Marketing = 'marketing';

    public function getLabel(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::AdminKonten => 'Admin Konten',
            self::Marketing => 'Marketing',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::SuperAdmin => 'danger',
            self::AdminKonten => 'primary',
            self::Marketing => 'success',
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
