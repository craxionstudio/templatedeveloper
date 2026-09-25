<?php

namespace App\Support;

use App\Settings\ContactPageSettings;
use App\Settings\GlobalSettings;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Daftar domain CSP halaman publik: bawaan (GTM, GA4, Meta Pixel, Turnstile, Google Maps, YouTube)
 * + "Domain tambahan CSP" dari Pengaturan Global → Tracking (Super Admin) + domain yang diturunkan
 * otomatis dari settings (embed peta Kontak, disk file publik bila memakai CDN/S3).
 *
 * Input admin divalidasi ketat: hanya domain / https://domain[:port]. Wildcard (*), keyword CSP
 * ('unsafe-eval', 'unsafe-inline', …), spasi, dan titik koma ditolak supaya CSP tidak bisa dilemahkan
 * atau disusupi direktif lain.
 */
class CspSources
{
    /**
     * Key settings → nama direktif CSP.
     */
    public const DIRECTIVES = [
        'script_src' => 'script-src',
        'connect_src' => 'connect-src',
        'img_src' => 'img-src',
        'frame_src' => 'frame-src',
    ];

    public const PATTERN = '/^(https:\/\/)?(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,63}(?::\d{1,5})?$/i';

    /**
     * Bawaan per direktif (di luar 'self').
     */
    public const DEFAULTS = [
        // Hanya fallback browser lama: browser modern memakai nonce + 'strict-dynamic'.
        'script_src' => [
            'https://www.googletagmanager.com',
            'https://www.google-analytics.com',
            'https://connect.facebook.net',
            'https://challenges.cloudflare.com',
        ],
        'connect_src' => [
            'https://www.googletagmanager.com',
            'https://*.google-analytics.com',
            'https://*.analytics.google.com',
            'https://*.g.doubleclick.net',
            'https://www.google.com',
            'https://www.facebook.com',
            'https://connect.facebook.net',
            'https://challenges.cloudflare.com',
        ],
        'img_src' => [
            'https://www.googletagmanager.com',
            'https://*.google-analytics.com',
            'https://*.g.doubleclick.net',
            'https://www.google.com',
            'https://www.facebook.com',
        ],
        'frame_src' => [
            'https://www.googletagmanager.com',
            'https://td.doubleclick.net',
            'https://www.google.com',
            'https://maps.google.com',
            'https://www.youtube.com',
            'https://www.youtube-nocookie.com',
            'https://www.facebook.com',
            'https://challenges.cloudflare.com',
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
     * @param  array<string, mixed>  $extra  tracking.csp_extra dari settings
     * @return array<string, list<string>>
     */
    public static function normalizeAll(array $extra): array
    {
        return collect(self::DIRECTIVES)
            ->mapWithKeys(fn (string $directive, string $key) => [$key => collect($extra[$key] ?? [])
                ->map(fn ($value) => self::normalize(is_array($value) ? ($value['domain'] ?? null) : $value))
                ->filter()
                ->unique()
                ->values()
                ->all()])
            ->all();
    }

    /**
     * Sumber lengkap per direktif (tanpa 'self' / nonce).
     *
     * @return array<string, list<string>>
     */
    public static function sources(): array
    {
        $extra = self::normalizeAll(app(GlobalSettings::class)->section('tracking')['csp_extra'] ?? []);
        $auto = self::automatic();

        return collect(self::DIRECTIVES)
            ->mapWithKeys(fn (string $directive, string $key) => [$key => array_values(array_unique([
                ...self::DEFAULTS[$key],
                ...($auto[$key] ?? []),
                ...$extra[$key],
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
            $embed = app(ContactPageSettings::class)->section('map')['embed_url'] ?? null;

            if ($origin = self::origin($embed)) {
                $auto['frame_src'][] = $origin;
            }

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
