<?php

namespace App\Services;

use App\Support\Tracking;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Verifikasi Cloudflare Turnstile di server.
 */
class Turnstile
{
    public const ENDPOINT = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    public static function enabled(): bool
    {
        return Tracking::turnstileSiteKey() !== null;
    }

    public static function verify(?string $token, ?string $ip): bool
    {
        if (! self::enabled()) {
            return true;
        }

        if (blank($token)) {
            return false;
        }

        try {
            $response = Http::asForm()->timeout(5)->post(self::ENDPOINT, [
                'secret' => Tracking::turnstileSecret(),
                'response' => $token,
                'remoteip' => $ip,
            ]);

            return $response->ok() && $response->json('success') === true;
        } catch (Throwable $e) {
            Log::warning('Verifikasi Turnstile gagal dihubungi', ['error' => $e->getMessage()]);

            return false;
        }
    }
}
