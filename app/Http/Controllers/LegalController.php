<?php

namespace App\Http\Controllers;

use App\Settings\PrivacyPageSettings;
use App\Support\Breadcrumbs;
use App\Support\PageMeta;
use App\Support\RichText;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class LegalController extends Controller
{
    public function privacy(PrivacyPageSettings $settings): Response
    {
        $content = $settings->section('content');

        $crumbs = Breadcrumbs::make([[$content['title']]]);

        return Inertia::render('Privacy', [
            'meta' => PageMeta::make($content['title'], strip_tags((string) $content['body']), $settings->section('seo'), breadcrumbs: $crumbs),
            'breadcrumbs' => $crumbs,
            'content' => [
                'title' => $content['title'],
                'body' => RichText::sanitize($content['body']),
                'effective' => $content['effective_date']
                    ? trim($content['effective_label'].' '.Carbon::parse($content['effective_date'])->translatedFormat('j F Y'))
                    : null,
            ],
        ]);
    }
}
