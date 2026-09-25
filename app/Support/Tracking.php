<?php

namespace App\Support;

use App\Settings\GlobalSettings;

/**
 * Konfigurasi tracking dari Pengaturan Global → Tracking (hanya Super Admin yang bisa mengubah).
 * Hanya ID publik yang dikirim ke browser; rahasia tetap di server.
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

    public static function gtmId(): ?string
    {
        return self::match(self::settings()['gtm_id'], '/^GTM-[A-Z0-9]+$/i');
    }

    public static function ga4Id(): ?string
    {
        return self::match(self::settings()['ga4_id'], '/^G-[A-Z0-9]+$/i');
    }

    public static function pixelId(): ?string
    {
        return self::match(self::settings()['meta_pixel_id'], '/^\d{5,20}$/');
    }

    /**
     * Pixel ID tujuan Conversions API. Bisa diisi terpisah, karena Pixel di browser boleh
     * dipasang lewat GTM (kolom Meta Pixel ID langsung dikosongkan).
     */
    public static function capiPixelId(): ?string
    {
        return self::match(self::settings()['meta_capi_pixel_id'] ?? null, '/^\d{5,20}$/') ?? self::pixelId();
    }

    public static function capiToken(): ?string
    {
        return Secret::decrypt(self::settings()['meta_capi_token'] ?? null);
    }

    public static function capiTestCode(): ?string
    {
        return trim((string) (self::settings()['meta_test_event_code'] ?? '')) ?: null;
    }

    /**
     * Turnstile aktif hanya kalau site key DAN secret key terisi. Kosong (lokal/dev) = dilewati.
     */
    public static function turnstileSiteKey(): ?string
    {
        $siteKey = trim((string) self::settings()['turnstile_site_key']);

        return $siteKey !== '' && self::turnstileSecret() !== null ? $siteKey : null;
    }

    public static function turnstileSecret(): ?string
    {
        return Secret::decrypt(self::settings()['turnstile_secret_key'] ?? null);
    }

    /**
     * Data untuk shared prop `site.tracking`.
     *
     * @return array{gtmId: ?string, ga4Id: ?string, pixelId: ?string, turnstileSiteKey: ?string}
     */
    public static function browser(): array
    {
        return [
            'gtmId' => self::gtmId(),
            'ga4Id' => self::ga4Id(),
            'pixelId' => self::pixelId(),
            'turnstileSiteKey' => self::turnstileSiteKey(),
        ];
    }

    private static function match(?string $value, string $pattern): ?string
    {
        $value = trim((string) $value);

        return preg_match($pattern, $value) ? $value : null;
    }
}
