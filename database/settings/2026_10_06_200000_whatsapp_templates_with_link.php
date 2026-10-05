<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

/**
 * Template pesan WhatsApp (6 Okt 2026): template bawaan ditambah baris {link_halaman}, plus template baru
 * untuk Detail Kawasan. Teks yang sudah diubah admin tidak ditimpa.
 */
return new class extends SettingsMigration
{
    /** Teks bawaan lama per template. */
    private const OLD_DEFAULTS = [
        'whatsapp_message' => ['', 'Halo, saya ingin konsultasi rumah di BSD City.', 'Halo, saya ingin info tentang rumah di BSD City.'],
        'whatsapp_cluster_message' => ['', 'Halo, saya tertarik dengan {nama_cluster}. Boleh minta info harga & brosurnya?'],
        'whatsapp_promo_message' => ['', 'Halo, saya tertarik dengan promo di {nama_cluster}. Boleh minta informasi lengkapnya?'],
        'whatsapp_survey_message' => ['', 'Halo, saya ingin jadwalkan survey ke {nama_cluster}.'],
        'whatsapp_kawasan_message' => [''],
    ];

    public function up(): void
    {
        if (! $this->migrator->exists('global.contact')) {
            return;
        }

        $defaults = (require database_path('settings/defaults/global.php'))['contact'];

        $this->migrator->update('global.contact', function (array|object $contact) use ($defaults): array {
            $contact = json_decode(json_encode($contact), true);

            foreach (self::OLD_DEFAULTS as $key => $old) {
                if (in_array(trim((string) ($contact[$key] ?? '')), $old, true)) {
                    $contact[$key] = $defaults[$key];
                }
            }

            return $contact;
        });
    }
};
