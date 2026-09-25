<?php

namespace App\Http\Middleware;

use App\Models\Redirect;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * URL & status code (brief 8.5), dijalankan sebelum routing (global middleware):
 * 1. Host & skema kanonik (APP_URL) di production: http→https, www ↔ non-www → 301.
 * 2. Trailing slash dan huruf kapital di path → 301 ke versi bersih.
 * 3. Redirect Manager (tabel `redirects`, di-cache): 301/302 ke tujuan, atau 410 Gone.
 */
class RedirectManager
{
    /**
     * Path yang tidak dinormalisasi (file statis, admin, Livewire).
     */
    private const SKIP = ['admin', 'admin/*', 'livewire*', 'storage/*', 'build/*', 'fonts/*', 'css/*', 'js/*', 'og/*', 'up'];

    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->isMethod('GET') && ! $request->isMethod('HEAD')) {
            return $next($request);
        }

        if ($target = $this->canonicalHost($request)) {
            return new RedirectResponse($target, 301);
        }

        if ($request->is(...self::SKIP)) {
            return $next($request);
        }

        $pathInfo = $request->getPathInfo();
        $clean = $pathInfo === '/' ? '/' : strtolower(rtrim($pathInfo, '/'));

        if ($clean !== $pathInfo && $clean !== '') {
            return $this->redirectTo($request, $clean, 301);
        }

        $match = Redirect::map()[$clean] ?? null;

        if ($match !== null) {
            [$to, $status] = $match;

            Redirect::query()->where('from_path', $clean)->update(['hits' => DB::raw('hits + 1'), 'last_hit_at' => now()]);

            abort_if($status === 410, 410);

            return $this->redirectTo($request, (string) $to, $status === 302 ? 302 : 301);
        }

        return $next($request);
    }

    /**
     * Di production, samakan skema & host dengan APP_URL.
     */
    private function canonicalHost(Request $request): ?string
    {
        if (! app()->isProduction()) {
            return null;
        }

        $base = parse_url((string) config('app.url'));
        $scheme = $base['scheme'] ?? null;
        $host = $base['host'] ?? null;

        if (! $scheme || ! $host || ($request->getScheme() === $scheme && strcasecmp($request->getHost(), $host) === 0)) {
            return null;
        }

        return rtrim((string) config('app.url'), '/').$request->getRequestUri();
    }

    private function redirectTo(Request $request, string $to, int $status): RedirectResponse
    {
        $query = $request->getQueryString();
        $url = str_starts_with($to, 'http') ? $to : url($to);

        // Query ikut dibawa kalau tujuan belum punya query sendiri.
        if ($query && ! str_contains($url, '?')) {
            $url .= '?'.$query;
        }

        return new RedirectResponse($url, $status);
    }
}
