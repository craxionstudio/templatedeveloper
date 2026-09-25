<?php

namespace App\Jobs;

use App\Models\Lead;
use App\Services\MetaConversions;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Kirim event Lead ke Meta Conversions API lewat queue (tidak menahan respons form).
 * IP & user agent mentah hanya ada di payload job, tidak disimpan di tabel leads.
 */
class SendMetaLeadEvent implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /**
     * @param  array{ip?: ?string, user_agent?: ?string, fbp?: ?string, fbc?: ?string, source_url?: ?string}  $context
     */
    public function __construct(public Lead $lead, public array $context) {}

    /**
     * @return list<int>
     */
    public function backoff(): array
    {
        return [60, 300];
    }

    public function handle(): void
    {
        MetaConversions::sendLead($this->lead, $this->context);
    }
}
