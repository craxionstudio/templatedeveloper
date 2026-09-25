<?php

use App\Http\Controllers\NotFoundController;
use App\Http\Middleware\CaptureAttribution;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
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
        // Cookie Meta Pixel (_fbp, _fbc) dibuat di browser, jadi tidak dienkripsi Laravel.
        $middleware->encryptCookies(except: ['_fbp', '_fbc']);

        $middleware->web(append: [
            CaptureAttribution::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Rate limit form publik: kembali ke form dengan pesan, bukan halaman error 429.
        $exceptions->render(function (ThrottleRequestsException $e, Request $request) {
            if ($request->is('lead', 'newsletter') && ! $request->expectsJson()) {
                return back()->withErrors(['form' => 'Terlalu banyak percobaan. Tunggu sebentar lalu coba lagi.']);
            }
        });

        // 404 halaman publik → halaman 404 custom ter-render SSR (admin memakai 404 Filament).
        $exceptions->respond(function (Response $response, Throwable $e, Request $request) {
            $isPublic = ! $request->is('admin', 'admin/*', 'livewire*', 'storage/*', 'build/*') && ! $request->expectsJson();

            return $response->getStatusCode() === 404 && $isPublic
                ? NotFoundController::render($request)
                : $response;
        });
    })->create();
