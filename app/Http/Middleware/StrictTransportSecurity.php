<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * HSTS di production lewat HTTPS (brief 8.6). Tanpa includeSubDomains/preload supaya aman
 * untuk subdomain lain milik pemilik; bisa ditambah setelah semua subdomain HTTPS.
 */
class StrictTransportSecurity
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (app()->isProduction() && $request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000');
        }

        return $response;
    }
}
