<?php

namespace App\Http\Controllers;

use App\Jobs\SendMetaContactEvent;
use App\Services\MetaConversions;
use App\Support\MetaContext;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * POST /track/contact: klik WhatsApp di browser → event Contact Meta CAPI (server) dengan
 * event_id yang sama dengan Pixel. Tanpa CAPI dikonfigurasi, permintaan diterima tanpa efek.
 */
class ContactEventController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $data = $request->validate([
            'event_id' => ['required', 'uuid'],
            'cluster' => ['nullable', 'string', 'max:255'],
            'source' => ['nullable', 'string', 'max:40'],
            'page_url' => ['nullable', 'string', 'max:500'],
        ]);

        if (MetaConversions::enabled()) {
            $pageUrl = $data['page_url'] ?? null;
            // Hanya URL situs ini yang dipakai sebagai event_source_url.
            $sourceUrl = is_string($pageUrl) && str_starts_with($pageUrl, url('/')) ? $pageUrl : $request->headers->get('referer');

            SendMetaContactEvent::dispatch([
                'event_id' => $data['event_id'],
                'cluster' => $data['cluster'] ?? null,
                'source' => $data['source'] ?? null,
                'event_time' => now()->getTimestamp(),
            ], MetaContext::from($request, $sourceUrl));
        }

        return response()->noContent();
    }
}
