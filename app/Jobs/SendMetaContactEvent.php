<?php

namespace App\Jobs;

use App\Services\MetaConversions;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Kirim event Contact (klik WhatsApp) ke Meta Conversions API lewat queue.
 */
class SendMetaContactEvent implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /**
     * @param  array{event_id: string, cluster?: ?string, source?: ?string, event_time?: int}  $event
     * @param  array{ip?: ?string, user_agent?: ?string, fbp?: ?string, fbc?: ?string, source_url?: ?string}  $context
     */
    public function __construct(public array $event, public array $context) {}

    /**
     * @return list<int>
     */
    public function backoff(): array
    {
        return [60, 300];
    }

    public function handle(): void
    {
        MetaConversions::sendContact($this->event, $this->context);
    }
}
