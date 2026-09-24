<?php

use App\Settings\PageSettings;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

/**
 * Milestone 3: template pesan WhatsApp di Detail Rumah (menyebut nama cluster & tipe).
 */
return new class extends SettingsMigration
{
    public function up(): void
    {
        $default = (require PageSettings::defaultsPath('page_cluster_detail'))['form']['whatsapp_message'];

        // Nilai tersimpan di-decode sebagai objek; ubah ke array dulu.
        $this->migrator->update('page_cluster_detail.form', function (array|object $form) use ($default): array {
            $form = json_decode(json_encode($form), true);

            return [...$form, 'whatsapp_message' => $form['whatsapp_message'] ?? $default];
        });
    }
};
