<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

/**
 * Security headers (brief 10) + CSP dasar untuk halaman publik.
 *
 * CSP memakai nonce per request + 'strict-dynamic': hanya script bertanda nonce (bundle Vite,
 * loader tracking) yang boleh jalan, dan script yang mereka muat (GTM, Pixel, Turnstile, chunk
 * halaman) ikut dipercaya. Admin Filament (Alpine/Livewire) tidak diberi CSP.
 */
class SecurityHeaders
{
    private const NO_CSP = ['admin', 'admin/*', 'livewire*', 'filament/*'];

    public function handle(Request $request, Closure $next): Response
    {
        $csp = config('site.csp.enabled') && ! $request->is(...self::NO_CSP);

        if ($csp) {
            Vite::useCspNonce();
        }

        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=(), usb=(), interest-cohort=()');

        // Content-Type bisa belum di-set (mis. halaman 404 dari exception handler): anggap HTML.
        $type = (string) $response->headers->get('Content-Type');

        if ($csp && ($type === '' || str_contains($type, 'text/html'))) {
            $response->headers->set('Content-Security-Policy', self::policy((string) Vite::cspNonce(), $request->isSecure()));
        }

        return $response;
    }

    public static function policy(string $nonce, bool $secure): string
    {
        $directives = [
            "default-src 'self'",
            // https: dan 'unsafe-inline' hanya fallback browser lama; diabaikan bila nonce/strict-dynamic didukung.
            "script-src 'nonce-{$nonce}' 'strict-dynamic' https: 'unsafe-inline'",
            "style-src 'self' 'unsafe-inline'",
            "img-src 'self' data: blob: https:",
            "font-src 'self' data:",
            // GA4, Meta Pixel, Turnstile.
            "connect-src 'self' https:",
            // Google Maps (facade), Turnstile, GTM noscript, video.
            "frame-src 'self' https:",
            "media-src 'self' https:",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'self'",
        ];

        if ($secure) {
            $directives[] = 'upgrade-insecure-requests';
        }

        return implode('; ', $directives);
    }
}
