<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

/**
 * Halaman Kontak dihapus (6 Okt 2026): /kontak 301 ke beranda, info kontak pindah ke footer (Pengaturan Umum),
 * menu Kontak membuka WhatsApp.
 * - settings halaman Kontak (judul, info, peta, SEO) dibuang;
 * - Kebijakan Privasi: kalimat "lewat halaman Kontak" diganti, sisa teks admin tidak diubah.
 */
return new class extends SettingsMigration
{
    public function up(): void
    {
        foreach (['header', 'info', 'map', 'cta', 'seo', 'form'] as $section) {
            $this->migrator->deleteIfExists('page_contact.'.$section);
        }

        if ($this->migrator->exists('page_privacy.content')) {
            $this->migrator->update('page_privacy.content', function (array|object $content): array {
                $content = json_decode(json_encode($content), true);
                $content['body'] = str_replace(
                    'dengan menghubungi kami lewat halaman Kontak.',
                    'dengan menghubungi kami lewat WhatsApp atau email di bagian bawah situs.',
                    (string) ($content['body'] ?? ''),
                );

                return $content;
            });
        }
    }
};
