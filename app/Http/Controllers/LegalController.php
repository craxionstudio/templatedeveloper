<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Settings\ClusterDetailPageSettings;
use App\Settings\GlobalSettings;
use App\Settings\PrivacyPageSettings;
use App\Settings\ThankYouPageSettings;
use App\Support\Breadcrumbs;
use App\Support\PageMeta;
use App\Support\SiteLayout;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
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
    public function thankYou(Request $request, ThankYouPageSettings $settings, GlobalSettings $global, ClusterDetailPageSettings $clusterSettings): Response
    {
        $content = $settings->section('content');
        $conversion = $request->session()->get('lead_conversion');
        $lead = is_array($conversion) ? Lead::query()->with(['cluster', 'houseType'])->find($conversion['lead_id'] ?? null) : null;

        return Inertia::render('ThankYou', [
            'meta' => PageMeta::make($settings->section('seo')['meta_title'] ?: $content['title'], $content['message'], noindex: true),
            'content' => [
                'title' => $content['title'],
                'message' => $content['message'],
                'links' => array_values($content['links'] ?? []),
            ],
            // Hanya ada tepat setelah submit (flash), jadi refresh tidak menghitung konversi dua kali.
            'conversion' => $lead ? [
                'eventId' => $lead->event_id,
                'cluster' => $lead->cluster?->name,
                'houseType' => $lead->houseType?->name,
                'position' => $lead->source_position,
            ] : null,
            'whatsapp' => $lead ? $this->whatsapp($lead, $content, $global, $clusterSettings) : null,
        ]);
    }

    /**
     * Tombol lanjut chat WA: nomor marketing cluster (kalau ada), pesan menyebut nama & cluster.
     *
     * @param  array<string, mixed>  $content
     * @return array{label: string, url: string}|null
     */
    private function whatsapp(Lead $lead, array $content, GlobalSettings $global, ClusterDetailPageSettings $clusterSettings): ?array
    {
        $number = $lead->cluster?->marketing_whatsapp
            ?: ($lead->cluster ? $clusterSettings->section('form')['marketing_whatsapp'] : null)
            ?: $global->section('contact')['whatsapp'];

        $message = PageMeta::fill($content['whatsapp_message'], [
            'name' => Str::before($lead->name, ' '),
            'cluster' => $lead->cluster?->name ?? $global->section('identity')['brand_name'],
        ]);

        $url = SiteLayout::whatsappUrl($number, $message);

        return $url ? ['label' => $content['whatsapp_label'], 'url' => $url] : null;
    }
}
