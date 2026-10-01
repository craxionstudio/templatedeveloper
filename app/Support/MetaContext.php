<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Konteks pengunjung untuk Meta Conversions API (IP, user agent, cookie _fbp/_fbc).
 * Dipakai event Lead (form) dan Contact (klik WhatsApp).
 */
class MetaContext
{
    /**
     * @return array{ip: ?string, user_agent: ?string, fbp: ?string, fbc: ?string, source_url: ?string}
     */
    public static function from(Request $request, ?string $sourceUrl): array
    {
        return [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'fbp' => self::cookie($request, '_fbp'),
            'fbc' => self::cookie($request, '_fbc') ?? self::fbcFromClickId($request),
            'source_url' => $sourceUrl,
        ];
    }

    /**
     * Parameter fbc dari fbclid yang ditangkap di kunjungan iklan (kalau cookie _fbc belum ada).
     */
    private static function fbcFromClickId(Request $request): ?string
    {
        $attribution = Attribution::read($request);

        return $attribution['fbclid']
            ? sprintf('fb.1.%s.%s', $attribution['fbclid_at'] ?? now()->getTimestampMs(), $attribution['fbclid'])
            : null;
    }

    private static function cookie(Request $request, string $name): ?string
    {
        $value = $request->cookie($name);

        return is_string($value) && preg_match('/^fb\.\d\.\d+\.[\w\-.]+$/', $value) ? $value : null;
    }
}
