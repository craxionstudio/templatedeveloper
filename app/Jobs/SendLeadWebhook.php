<?php

namespace App\Jobs;

use App\Models\Lead;
use App\Support\LeadPayload;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;

/**
 * Webhook lead opsional (WA gateway, Google Sheet, dsb). URL dari Pengaturan Global → Notifikasi lead.
 */
class SendLeadWebhook implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public Lead $lead, public string $url) {}

    /**
     * @return list<int>
     */
    public function backoff(): array
    {
        return [60, 300];
    }

    public function handle(): void
    {
        Http::timeout(10)->acceptJson()->post($this->url, [
            'event' => 'lead.created',
            'lead' => LeadPayload::make($this->lead),
        ])->throw();
    }
}
