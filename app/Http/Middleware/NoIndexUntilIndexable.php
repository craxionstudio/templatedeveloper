<?php

namespace App\Http\Middleware;

use App\Support\Indexing;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Selama website belum boleh diindeks (SITE_INDEXABLE=false: lokal, staging, atau production di domain
 * sementara): header X-Robots-Tag di semua respons. Tidak bergantung pada APP_ENV.
 */
class NoIndexUntilIndexable
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! Indexing::allowed()) {
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
        }

        return $response;
    }
}
