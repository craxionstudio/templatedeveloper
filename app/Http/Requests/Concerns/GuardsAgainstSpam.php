<?php

namespace App\Http\Requests\Concerns;

use App\Services\Turnstile;
use Illuminate\Validation\Validator;

/**
 * Anti-spam form publik: honeypot (field `website` yang disembunyikan) + Cloudflare Turnstile.
 * Rate limit per IP dipasang di route (throttle:leads / throttle:newsletter).
 */
trait GuardsAgainstSpam
{
    public const HONEYPOT = 'website';

    /**
     * Honeypot terisi = bot. Request tidak disimpan, tapi tetap dijawab "sukses" supaya bot
     * tidak belajar dari pesan error.
     */
    public function isHoneypotFilled(): bool
    {
        return filled($this->input(self::HONEYPOT));
    }

    /**
     * @return array<string, mixed>
     */
    protected function spamRules(): array
    {
        return [
            self::HONEYPOT => ['nullable', 'string', 'max:255'],
            'turnstile_token' => ['nullable', 'string', 'max:2048'],
        ];
    }

    /**
     * @return list<callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($this->isHoneypotFilled() || $validator->errors()->isNotEmpty()) {
                    return;
                }

                if (method_exists($this, 'exceedsNumberLimit') && $this->exceedsNumberLimit()) {
                    $validator->errors()->add('whatsapp', 'Nomor ini sudah mengirim beberapa permintaan hari ini. Tim kami akan segera menghubungi kamu, atau chat langsung lewat WhatsApp.');

                    return;
                }

                if (! Turnstile::verify($this->input('turnstile_token'), $this->ip())) {
                    $validator->errors()->add('turnstile_token', 'Verifikasi keamanan gagal. Muat ulang halaman lalu coba lagi.');
                }
            },
        ];
    }
}
