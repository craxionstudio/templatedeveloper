<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * Peran user admin. Sejak Okt 2026 hanya satu: Admin (semua menu). Kolom `role` dipertahankan
 * supaya peran baru bisa ditambah lagi tanpa migrasi struktur.
 */
enum UserRole: string implements HasColor, HasLabel
{
    case Admin = 'admin';

    public function getLabel(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Admin => 'primary',
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
