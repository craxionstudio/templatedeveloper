<?php

namespace App\Support;

/**
 * Format Rupiah sesuai desain: "Rp 1,6 M", "Rp 580 jt", "Rp 9,8 jt/bln",
 * rentang "Rp 1,1 – 2,2 M" / "Rp 850 jt – 1,1 M" / "Rp 580 – 820 jt".
 */
class Rupiah
{
    private const BILLION = 1_000_000_000;

    private const MILLION = 1_000_000;

    public static function short(?int $amount): ?string
    {
        if ($amount === null) {
            return null;
        }

        return 'Rp '.self::number($amount).' '.self::unit($amount);
    }

    public static function range(?int $min, ?int $max): ?string
    {
        if ($min === null) {
            return null;
        }

        if ($max === null || $max === $min) {
            return self::short($min);
        }

        if (self::unit($min) === self::unit($max)) {
            return 'Rp '.self::number($min).' – '.self::number($max).' '.self::unit($max);
        }

        return self::short($min).' – '.self::number($max).' '.self::unit($max);
    }

    /**
     * Rupiah penuh: "Rp 1.600.000.000".
     */
    public static function full(?int $amount): ?string
    {
        return $amount === null ? null : 'Rp '.number_format($amount, 0, ',', '.');
    }

    private static function unit(int $amount): string
    {
        return $amount >= self::BILLION ? 'M' : 'jt';
    }

    private static function number(int $amount): string
    {
        if ($amount >= self::BILLION) {
            // Miliar selalu satu desimal: "1,0 M", "1,6 M".
            return number_format($amount / self::BILLION, 1, ',', '.');
        }

        $millions = $amount / self::MILLION;

        // Juta: desimal hanya kalau perlu: "580 jt", "9,8 jt".
        return fmod($millions, 1.0) === 0.0
            ? number_format($millions, 0, ',', '.')
            : number_format($millions, 1, ',', '.');
    }
}
