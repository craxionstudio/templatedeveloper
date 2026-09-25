<?php

namespace App\Http\Middleware;

use App\Support\Attribution;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Menangkap UTM/fbclid/gclid/landing page di halaman publik (GET) ke cookie 30 hari.
 */
class CaptureAttribution
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->isMethod('GET') && ! $request->is('admin', 'admin/*', 'livewire*')
            && ($cookie = Attribution::capture($request))) {
            $response->headers->setCookie($cookie);
        }

        return $response;
    }
}
