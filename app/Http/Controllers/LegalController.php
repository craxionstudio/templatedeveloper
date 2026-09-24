<?php

namespace App\Http\Controllers;

use App\Settings\PrivacyPageSettings;
use App\Settings\ThankYouPageSettings;
use App\Support\Breadcrumbs;
use App\Support\PageMeta;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class LegalController extends Controller
{
    public function privacy(PrivacyPageSettings $settings): Response
    {
        $content = $settings->section('content');

        return Inertia::render('Privacy', [
            'meta' => PageMeta::make($content['title'], strip_tags((string) $content['body']), $settings->section('seo')),
            'breadcrumbs' => Breadcrumbs::make([[$content['title']]]),
            'content' => [
                'title' => $content['title'],
                'body' => $content['body'],
                'effective' => $content['effective_date']
                    ? trim($content['effective_label'].' '.Carbon::parse($content['effective_date'])->translatedFormat('j F Y'))
                    : null,
            ],
        ]);
    }

    /**
     * Halaman setelah submit form (selalu noindex). Tombol lanjut WA & event konversi di Milestone 4.
     */
    public function thankYou(ThankYouPageSettings $settings): Response
    {
        $content = $settings->section('content');

        return Inertia::render('ThankYou', [
            'meta' => PageMeta::make($settings->section('seo')['meta_title'] ?: $content['title'], $content['message'], noindex: true),
            'content' => [
                'title' => $content['title'],
                'message' => $content['message'],
                'links' => array_values($content['links'] ?? []),
            ],
        ]);
    }
}
