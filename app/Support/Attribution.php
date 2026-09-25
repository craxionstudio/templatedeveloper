<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Cookie;

/**
 * Atribusi kampanye (brief 9): UTM, fbclid, gclid, landing page pertama, dan referrer.
 * Ditangkap di kunjungan pertama dan disimpan di cookie selama 30 hari, lalu disalin ke lead.
 *
 * Landing page & referrer = kunjungan pertama. UTM & click ID diperbarui kalau pengunjung
 * datang lagi lewat iklan/kampanye baru (klik terakhir yang bertanda), supaya fbclid/gclid
 * yang dikirim ke Meta/Google tetap yang terbaru.
 */
class Attribution
{
    public const COOKIE = 'arunika_attribution';

    public const MINUTES = 60 * 24 * 30;

    public const CAMPAIGN_KEYS = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term', 'fbclid', 'gclid'];

    /**
     * @return array<string, string|null>
     */
    public static function read(Request $request): array
    {
        $data = json_decode((string) $request->cookie(self::COOKIE), true);

        return collect([...self::CAMPAIGN_KEYS, 'landing_page', 'referrer', 'fbclid_at'])
            ->mapWithKeys(fn (string $key) => [$key => is_array($data) && is_scalar($data[$key] ?? null) ? (string) $data[$key] : null])
            ->all();
    }

    /**
     * Cookie baru kalau belum ada atau request membawa parameter kampanye; null kalau tidak berubah.
     */
    public static function capture(Request $request): ?Cookie
    {
        $current = array_filter(self::read($request), fn ($v) => $v !== null);
        $campaign = collect(self::CAMPAIGN_KEYS)
            ->mapWithKeys(fn (string $key) => [$key => self::clean($request->query($key))])
            ->filter()
            ->all();

        if ($current !== [] && $campaign === []) {
            return null;
        }

        $data = $current === []
            ? [
                'landing_page' => Str::limit($request->fullUrl(), 1000, ''),
                'referrer' => self::externalReferrer($request),
            ]
            : array_intersect_key($current, array_flip(['landing_page', 'referrer']));

        if ($campaign !== []) {
            $data = [...$data, ...$campaign];
            // Waktu klik iklan untuk parameter fbc Meta (fb.1.{ms}.{fbclid}).
            $data['fbclid_at'] = isset($campaign['fbclid']) ? (string) now()->getTimestampMs() : ($current['fbclid_at'] ?? null);
        } else {
            $data = [...$current, ...$data];
        }

        return cookie(self::COOKIE, (string) json_encode(array_filter($data)), self::MINUTES, sameSite: 'lax');
    }

    private static function clean(mixed $value): ?string
    {
        return is_string($value) && trim($value) !== '' ? Str::limit(trim($value), 250, '') : null;
    }

    private static function externalReferrer(Request $request): ?string
    {
        $referrer = (string) $request->headers->get('referer');
        $host = parse_url($referrer, PHP_URL_HOST);

        return $referrer !== '' && $host !== $request->getHost() ? Str::limit($referrer, 1000, '') : null;
    }
}
