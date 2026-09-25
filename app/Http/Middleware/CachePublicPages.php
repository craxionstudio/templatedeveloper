<?php

namespace App\Http\Middleware;

use App\Support\PageCache;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response as IlluminateResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Cache HTML awal halaman publik untuk tamu (hemat query DB + render SSR).
 *
 * Tidak di-cache: request Inertia (JSON), non-GET, admin/Livewire/pratinjau/terima kasih, user
 * login, dan request yang membawa flash/error di session (hasil submit form). Cookie & header
 * per pengunjung (session, XSRF, atribusi, X-Robots-Tag) ditambahkan middleware luar setelah ini,
 * jadi tidak ikut tersimpan.
 */
class CachePublicPages
{
    private const SKIP = ['admin', 'admin/*', 'livewire*', 'pratinjau/*', 'terima-kasih', 'lead', 'newsletter', 'up'];

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->cacheable($request)) {
            return $next($request);
        }

        $key = PageCache::key($request);
        $cached = PageCache::store()->get($key);

        if (is_array($cached)) {
            return new IlluminateResponse($cached['content'], 200, [...$cached['headers'], 'X-Page-Cache' => 'HIT']);
        }

        $response = $next($request);

        if ($response->getStatusCode() === 200 && str_contains((string) $response->headers->get('Content-Type'), 'text/html') && ! $response->headers->getCookies()) {
            PageCache::store()->put($key, [
                'content' => $response->getContent(),
                'headers' => array_filter([
                    'Content-Type' => $response->headers->get('Content-Type'),
                    'Vary' => $response->headers->get('Vary'),
                    'Link' => $response->headers->get('Link'),
                ]),
            ], config('site.page_cache.ttl'));
            $response->headers->set('X-Page-Cache', 'MISS');
        }

        return $response;
    }

    private function cacheable(Request $request): bool
    {
        if (! PageCache::enabled() || ! $request->isMethod('GET') || $request->header('X-Inertia') || $request->is(...self::SKIP)) {
            return false;
        }

        if ($request->user()) {
            return false;
        }

        $session = $request->hasSession() ? $request->session() : null;

        return ! $session || (! $session->has('errors') && $session->get('_flash.old', []) === []);
    }
}
