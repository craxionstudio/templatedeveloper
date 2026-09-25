<?php

namespace App\Services;

use App\Models\Lead;
use App\Support\Tracking;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Meta Conversions API: event Lead dari server dengan event_id yang sama dengan Pixel
 * (deduplikasi). Data pribadi dinormalisasi lalu di-hash SHA-256 sesuai spesifikasi Meta.
 *
 * @see https://developers.facebook.com/docs/marketing-api/conversions-api/parameters/customer-information-parameters
 */
class MetaConversions
{
    /**
     * CAPI dilewati tanpa error kalau Pixel ID atau access token kosong.
     */
    public static function enabled(): bool
    {
        return Tracking::pixelId() !== null && Tracking::capiToken() !== null;
    }

    public static function endpoint(): string
    {
        return sprintf('https://graph.facebook.com/%s/%s/events', config('services.meta.graph_version'), Tracking::pixelId());
    }

    /**
     * @param  array{ip?: ?string, user_agent?: ?string, fbp?: ?string, fbc?: ?string, source_url?: ?string}  $context
     */
    public static function sendLead(Lead $lead, array $context): ?Response
    {
        if (! self::enabled()) {
            return null;
        }

        $payload = ['data' => [self::leadEvent($lead, $context)]];

        if ($code = Tracking::capiTestCode()) {
            $payload['test_event_code'] = $code;
        }

        return Http::timeout(10)
            ->withQueryParameters(['access_token' => Tracking::capiToken()])
            ->post(self::endpoint(), $payload)
            ->throw();
    }

    /**
     * @param  array{ip?: ?string, user_agent?: ?string, fbp?: ?string, fbc?: ?string, source_url?: ?string}  $context
     * @return array<string, mixed>
     */
    public static function leadEvent(Lead $lead, array $context): array
    {
        $lead->loadMissing('cluster');
        [$first, $last] = self::splitName($lead->name);

        $userData = array_filter([
            'ph' => self::hashList(self::normalizePhone($lead->whatsapp)),
            'em' => self::hashList(self::normalizeEmail($lead->email)),
            'fn' => self::hashList(self::normalizeName($first)),
            'ln' => self::hashList(self::normalizeName($last)),
            'country' => self::hashList('id'),
            'external_id' => self::hashList((string) $lead->getKey()),
            'client_ip_address' => $context['ip'] ?? null,
            'client_user_agent' => $context['user_agent'] ?? null,
            'fbp' => $context['fbp'] ?? null,
            'fbc' => $context['fbc'] ?? null,
        ]);

        return [
            'event_name' => 'Lead',
            'event_time' => $lead->created_at?->getTimestamp() ?? now()->getTimestamp(),
            'event_id' => $lead->event_id,
            'event_source_url' => $context['source_url'] ?? null,
            'action_source' => 'website',
            'user_data' => $userData,
            'custom_data' => array_filter([
                'content_name' => $lead->cluster?->name,
                'content_category' => $lead->source_position,
            ]),
        ];
    }

    public static function hash(string $value): string
    {
        return hash('sha256', $value);
    }

    /**
     * Nomor telepon: hanya digit, dengan kode negara, tanpa 0 di depan (62812…).
     */
    public static function normalizePhone(?string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);

        return $digits !== '' ? $digits : null;
    }

    public static function normalizeEmail(?string $email): ?string
    {
        $email = Str::lower(trim((string) $email));

        return $email !== '' ? $email : null;
    }

    /**
     * Nama: huruf kecil, tanpa tanda baca dan spasi.
     */
    public static function normalizeName(?string $name): ?string
    {
        $name = preg_replace('/[^\p{L}\p{M}]+/u', '', Str::lower((string) $name));

        return $name !== '' ? $name : null;
    }

    /**
     * @return list<string>|null
     */
    private static function hashList(?string $value): ?array
    {
        return $value === null ? null : [self::hash($value)];
    }

    /**
     * @return array{0: ?string, 1: ?string}
     */
    private static function splitName(string $name): array
    {
        $parts = preg_split('/\s+/', trim($name), 2) ?: [];

        return [$parts[0] ?? null, $parts[1] ?? null];
    }
}
