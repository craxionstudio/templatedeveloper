<?php

use App\Http\Controllers\NotFoundController;
use App\Http\Middleware\CachePublicPages;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\NoIndexOutsideProduction;
use App\Http\Middleware\RedirectManager;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\StrictTransportSecurity;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Sebelum routing: URL kanonik, trailing slash/kapital, Redirect Manager; noindex di non-production.
        // SecurityHeaders global supaya admin Filament juga dapat header; nonce CSP dibuat sebelum view dirender.
        $middleware->append([SecurityHeaders::class, StrictTransportSecurity::class, NoIndexOutsideProduction::class, RedirectManager::class]);

        $middleware->web(append: [
            HandleInertiaRequests::class,
            // Cache HTML halaman publik untuk tamu (setelah session; header Link preload ikut tersimpan).
            CachePublicPages::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // 404 halaman publik → halaman 404 custom ter-render SSR (admin memakai 404 Filament).
        $exceptions->respond(function (Response $response, Throwable $e, Request $request) {
            $isPublic = ! $request->is('admin', 'admin/*', 'livewire*', 'storage/*', 'build/*') && ! $request->expectsJson();

            return in_array($response->getStatusCode(), [404, 410], true) && $isPublic
                ? NotFoundController::render($request, $response->getStatusCode())
                : $response;
        });
    })->create();
