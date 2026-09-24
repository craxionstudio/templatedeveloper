<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Tempat promo ditampilkan.
 */
enum PromoPlacement: string implements HasLabel
{
    case HomeBanner = 'home_banner';
    case ListingBadge = 'listing_badge';
    case Detail = 'detail';

    public function getLabel(): string
    {
        return match ($this) {
            self::HomeBanner => 'Banner Beranda',
            self::ListingBadge => 'Badge di listing',
            self::Detail => 'Detail Rumah (Promo rumah ini)',
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
