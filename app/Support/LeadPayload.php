<?php

namespace App\Support;

use App\Models\Lead;

/**
 * Ringkasan lead untuk email notifikasi dan webhook.
 */
class LeadPayload
{
    /**
     * @return array<string, mixed>
     */
    public static function make(Lead $lead): array
    {
        $lead->loadMissing(['cluster', 'houseType']);

        return [
            'id' => $lead->id,
            'created_at' => $lead->created_at?->toIso8601String(),
            'name' => $lead->name,
            'whatsapp' => $lead->whatsapp,
            'email' => $lead->email,
            'cluster' => $lead->cluster?->name,
            'house_type' => $lead->houseType?->name,
            'payment_plan' => $lead->payment_plan,
            'message' => $lead->message,
            'source_page' => $lead->source_page,
            'source_position' => $lead->source_position,
            'utm_source' => $lead->utm_source,
            'utm_medium' => $lead->utm_medium,
            'utm_campaign' => $lead->utm_campaign,
            'utm_content' => $lead->utm_content,
            'utm_term' => $lead->utm_term,
            'fbclid' => $lead->fbclid,
            'gclid' => $lead->gclid,
            'landing_page' => $lead->landing_page,
            'referrer' => $lead->referrer,
            'admin_url' => url('/admin/leads/'.$lead->id.'/edit'),
        ];
    }
}
