<?php

namespace App\Http\Controllers;

use App\Settings\GlobalSettings;
use App\Support\PageMeta;
use App\Support\SiteLayout;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

/**
 * Halaman 404 custom (SSR, status 404 asli — bukan soft-404).
 */
class NotFoundController extends Controller
{
    public static function render(Request $request): Response
    {
        $content = app(GlobalSettings::class)->section('not_found');

        // Route yang tidak cocok tidak melewati middleware web, jadi shared prop diisi manual.
        Inertia::share('site', fn () => SiteLayout::data());

        return Inertia::render('Errors/NotFound', [
            'meta' => PageMeta::make($content['title'], $content['message'], noindex: true),
            'content' => [
                'eyebrow' => $content['eyebrow'],
                'title' => $content['title'],
                'message' => $content['message'],
                'links' => array_values($content['links'] ?? []),
            ],
        ])->toResponse($request)->setStatusCode(404);
    }
}
