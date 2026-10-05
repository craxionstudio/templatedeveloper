<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Daftar domain CSP halaman publik: bawaan (GA4 / gtag.js, Google Maps, YouTube) + domain yang diturunkan
 * otomatis dari settings (disk file publik bila memakai CDN/S3).
 * Tanpa Meta Pixel, GTM, dan Turnstile (dihapus Okt 2026: tidak beriklan di Meta, semua lead lewat WhatsApp).
 */
class CspSources
{
    /**
     * Key → nama direktif CSP.
     */
    public const DIRECTIVES = [
        'script_src' => 'script-src',
        'connect_src' => 'connect-src',
        'img_src' => 'img-src',
        'frame_src' => 'frame-src',
    ];

    public const PATTERN = '/^(https:\/\/)?(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,63}(?::\d{1,5})?$/i';

    /**
     * Bawaan per direktif (di luar 'self'). Domain GA4 sesuai panduan CSP Google Analytics.
     */
    public const DEFAULTS = [
        // Hanya fallback browser lama: browser modern memakai nonce + 'strict-dynamic'.
        'script_src' => [
            'https://www.googletagmanager.com',
        ],
        'connect_src' => [
            'https://*.google-analytics.com',
            'https://*.analytics.google.com',
            'https://*.googletagmanager.com',
        ],
        'img_src' => [
            'https://*.google-analytics.com',
            'https://*.googletagmanager.com',
        ],
        'frame_src' => [
            'https://www.google.com',
            'https://maps.google.com',
            'https://www.youtube.com',
            'https://www.youtube-nocookie.com',
        ],
    ];

    /**
     * "example.com" / "HTTPS://Example.com/" → "https://example.com"; null kalau tidak valid.
     */
    public static function normalize(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = rtrim(strtolower(trim($value)), '/');

        if ($value === '' || ! preg_match(self::PATTERN, $value)) {
            return null;
        }

        return str_starts_with($value, 'https://') ? $value : 'https://'.$value;
    }

    /**
     * Sumber lengkap per direktif (tanpa 'self' / nonce).
     *
     * @return array<string, list<string>>
     */
    public static function sources(): array
    {
        $auto = self::automatic();

        return collect(self::DIRECTIVES)
            ->mapWithKeys(fn (string $directive, string $key) => [$key => array_values(array_unique([
                ...self::DEFAULTS[$key],
                ...($auto[$key] ?? []),
            ]))])
            ->all();
    }

    /**
     * Domain yang sudah jelas dari settings, supaya admin tidak perlu mengisinya manual.
     *
     * @return array<string, list<string>>
     */
    private static function automatic(): array
    {
        $auto = ['frame_src' => [], 'img_src' => []];

        try {
            // Foto di CDN / bucket S3 (disk public dengan URL domain lain).
            if ($origin = self::origin(Storage::disk('public')->url('x'))) {
                $auto['img_src'][] = $origin;
            }
        } catch (Throwable) {
            // Settings belum dimigrasi (mis. saat instalasi): pakai bawaan saja.
        }

        return $auto;
    }

    private static function origin(?string $url): ?string
    {
        $parts = $url ? parse_url($url) : null;

        if (($parts['scheme'] ?? null) !== 'https' || empty($parts['host'])) {
            return null;
        }

        $appHost = parse_url((string) config('app.url'), PHP_URL_HOST);

        return strcasecmp($parts['host'], (string) $appHost) === 0
            ? null
            : self::normalize($parts['host'].(isset($parts['port']) ? ':'.$parts['port'] : ''));
    }
}
