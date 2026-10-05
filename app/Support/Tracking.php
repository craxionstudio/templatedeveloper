<?php

namespace App\Support;

use App\Settings\GlobalSettings;

/**
 * Tracking organik: GA4 langsung lewat gtag.js + verifikasi Search Console (Pengaturan Umum).
 * Tanpa GA4 Measurement ID tidak ada script tracking yang dimuat.
 */
class Tracking
{
    /**
     * @return array<string, mixed>
     */
    private static function settings(): array
    {
        return app(GlobalSettings::class)->section('tracking');
    }

    public static function ga4Id(): ?string
    {
        return self::match(self::settings()['ga4_id'] ?? null, '/^G-[A-Z0-9]+$/i');
    }

    /**
     * Isi atribut content meta tag verifikasi. Admin boleh menempelkan seluruh meta tag; yang diambil
     * hanya nilai content-nya.
     */
    public static function googleVerification(): ?string
    {
        return self::verification(self::settings()['google_verification'] ?? null);
    }

    public static function bingVerification(): ?string
    {
        return self::verification(self::settings()['bing_verification'] ?? null);
    }

    /**
     * Data untuk shared prop `site.tracking`.
     *
     * @return array{ga4Id: ?string}
     */
    public static function browser(): array
    {
        return ['ga4Id' => self::ga4Id()];
    }

    private static function verification(?string $value): ?string
    {
        $value = trim((string) $value);

        if (preg_match('/content=["\']([^"\']+)["\']/i', $value, $match)) {
            $value = $match[1];
        }

        return preg_match('/^[A-Za-z0-9_\-]{8,200}$/', $value) ? $value : null;
    }

    private static function match(?string $value, string $pattern): ?string
    {
        $value = trim((string) $value);

        return preg_match($pattern, $value) ? $value : null;
    }
}
