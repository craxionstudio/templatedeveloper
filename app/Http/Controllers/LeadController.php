<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeadRequest;
use App\Jobs\SendLeadWebhook;
use App\Jobs\SendMetaLeadEvent;
use App\Models\Lead;
use App\Notifications\NewLeadNotification;
use App\Services\MetaConversions;
use App\Settings\GlobalSettings;
use App\Support\Attribution;
use App\Support\MetaContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

/**
 * Simpan lead dari semua form (Detail Rumah, modal CTA, Kontak), lalu ke /terima-kasih.
 */
class LeadController extends Controller
{
    public function store(StoreLeadRequest $request): RedirectResponse
    {
        if ($request->isHoneypotFilled()) {
            return redirect()->route('terima-kasih');
        }

        $lead = Lead::query()->create([
            ...$request->safe()->only(['name', 'email', 'cluster_id', 'house_type_id', 'payment_plan', 'message', 'source_position']),
            ...array_intersect_key(Attribution::read($request), array_flip(Attribution::LEAD_KEYS)),
            'event_id' => (string) Str::uuid(),
            'name' => Str::squish($request->string('name')->toString()),
            'whatsapp' => $request->normalizedWhatsapp(),
            'email' => $request->filled('email') ? Str::lower(trim($request->string('email')->toString())) : null,
            'source_page' => self::path($request->input('source_page')),
            'ip_hash' => $request->ip() ? hash_hmac('sha256', $request->ip(), (string) config('app.key')) : null,
            'user_agent' => Str::limit((string) $request->userAgent(), 500, ''),
            'consent' => true,
        ]);

        $this->dispatchFollowUps($lead, $request);

        // Dibaca sekali di /terima-kasih: event konversi (Pixel + GA4) dengan event_id yang sama dengan CAPI.
        $request->session()->flash('lead_conversion', [
            'event_id' => $lead->event_id,
            'lead_id' => $lead->id,
            'position' => $lead->source_position,
        ]);

        return redirect()->route('terima-kasih');
    }

    private function dispatchFollowUps(Lead $lead, Request $request): void
    {
        $notifications = app(GlobalSettings::class)->section('notifications');

        $emails = collect($notifications['emails'] ?? [])
            ->map(fn ($email) => trim((string) (is_array($email) ? ($email['email'] ?? '') : $email)))
            ->filter(fn (string $email) => filter_var($email, FILTER_VALIDATE_EMAIL))
            ->unique()
            ->values()
            ->all();

        if ($emails !== []) {
            Notification::route('mail', $emails)->notify(new NewLeadNotification($lead));
        }

        if (filter_var($notifications['webhook_url'] ?? '', FILTER_VALIDATE_URL)) {
            SendLeadWebhook::dispatch($lead, $notifications['webhook_url']);
        }

        if (MetaConversions::enabled()) {
            SendMetaLeadEvent::dispatch($lead, MetaContext::from(
                $request,
                $lead->source_page ? url($lead->source_page) : $request->headers->get('referer'),
            ));
        }
    }

    /**
     * Simpan path halaman asal saja (tanpa domain), maksimal 500 karakter.
     */
    private static function path(mixed $value): ?string
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        $path = parse_url($value, PHP_URL_PATH) ?: '/';
        $query = parse_url($value, PHP_URL_QUERY);

        return Str::limit('/'.ltrim($path, '/').($query ? '?'.$query : ''), 500, '');
    }
}
