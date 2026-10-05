<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

/**
 * WhatsApp only + GA4 (Tahap C, 5 Okt 2026):
 * - hapus settings halaman Terima Kasih, form Kontak, dan notifikasi lead;
 * - tracking hanya GA4 + verifikasi (GTM, Meta Pixel/CAPI, Turnstile, domain CSP tambahan dibuang);
 * - template WhatsApp global bawaan diganti teks baru;
 * - Kebijakan Privasi yang masih draf awal diganti draf baru (tanpa form & Meta, dengan GA4).
 */
return new class extends SettingsMigration
{
    public function up(): void
    {
        foreach (['page_thank_you.content', 'page_thank_you.seo', 'page_contact.form', 'global.notifications'] as $property) {
            $this->migrator->deleteIfExists($property);
        }

        if ($this->migrator->exists('global.tracking')) {
            $this->migrator->update('global.tracking', fn (array|object $tracking): array => array_intersect_key(
                json_decode(json_encode($tracking), true),
                array_flip(['ga4_id', 'google_verification', 'bing_verification']),
            ));
        }

        // Template WA global: teks bawaan lama diganti teks baru; teks yang sudah diubah admin dipertahankan.
        if ($this->migrator->exists('global.contact')) {
            $this->migrator->update('global.contact', function (array|object $contact): array {
                $contact = json_decode(json_encode($contact), true);

                if (in_array($contact['whatsapp_message'] ?? '', ['', 'Halo, saya ingin info tentang rumah di BSD City.'], true)) {
                    $contact['whatsapp_message'] = 'Halo, saya ingin konsultasi rumah di BSD City.';
                }

                return $contact;
            });
        }

        if ($this->migrator->exists('page_privacy.content')) {
            $this->migrator->update('page_privacy.content', function (array|object $content): array {
                $content = json_decode(json_encode($content), true);
                $body = (string) ($content['body'] ?? '');

                // Hanya draf bawaan yang belum pernah diisi; teks yang sudah ditulis admin tidak ditimpa.
                if ($body === '' || str_contains($body, '[ISI KEBIJAKAN PRIVASI')) {
                    $content['body'] = (require database_path('settings/defaults/page_privacy.php'))['content']['body'];
                }

                return $content;
            });
        }
    }
};
