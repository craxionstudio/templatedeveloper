<?php

namespace App\Notifications;

use App\Models\Lead;
use App\Settings\GlobalSettings;
use App\Support\LeadPayload;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Email ke marketing setiap ada lead baru (lewat queue).
 */
class NewLeadNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Lead $lead) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $data = LeadPayload::make($this->lead);
        $brand = app(GlobalSettings::class)->section('identity')['brand_name'];
        $interest = trim(implode(' · ', array_filter([$data['cluster'], $data['house_type']])));

        $mail = (new MailMessage)
            ->subject(trim("[{$brand}] Lead baru: {$data['name']}".($interest !== '' ? " — {$interest}" : '')))
            ->greeting('Lead baru masuk')
            ->line('**Nama:** '.$data['name'])
            ->line('**WhatsApp:** '.$data['whatsapp']);

        foreach ([
            'Email' => $data['email'],
            'Minat' => $interest ?: null,
            'Rencana pembayaran' => $data['payment_plan'],
            'Pesan' => $data['message'],
            'Form' => trim(($data['source_position'] ?? '').' · '.($data['source_page'] ?? ''), ' ·'),
            'Sumber' => collect(['utm_source', 'utm_medium', 'utm_campaign'])->map(fn ($k) => $data[$k])->filter()->implode(' / ') ?: null,
        ] as $label => $value) {
            if (filled($value)) {
                $mail->line("**{$label}:** ".$value);
            }
        }

        return $mail
            ->action('Chat WhatsApp', 'https://wa.me/'.$data['whatsapp'])
            ->line('Detail lengkap: '.$data['admin_url']);
    }
}
