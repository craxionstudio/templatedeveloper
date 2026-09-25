<?php

namespace App\Support;

use App\Settings\GlobalSettings;

/**
 * Section CTA di bawah halaman: CTA global atau override per halaman.
 */
class Cta
{
    /**
     * @param  array<string, mixed>  $section  section "cta" dari settings halaman
     * @param  array{clusterId?: ?int, houseTypeId?: ?int}  $lead  konteks lead untuk form modal
     * @return array<string, mixed>|null null = section dimatikan
     */
    public static function resolve(array $section, ?string $whatsappMessage = null, array $lead = []): ?array
    {
        if (! ($section['enabled'] ?? true)) {
            return null;
        }

        $global = app(GlobalSettings::class);
        $cta = $global->section('cta');
        $contact = $global->section('contact');
        $useGlobal = (bool) ($section['use_global'] ?? true);

        $text = fn (string $key): string => ! $useGlobal && filled($section[$key] ?? null) ? $section[$key] : $cta[$key];

        return [
            'eyebrow' => $text('eyebrow'),
            'title' => $text('title'),
            'description' => $text('description'),
            'whatsappLabel' => $cta['whatsapp_label'],
            'whatsappUrl' => SiteLayout::whatsappUrl($contact['whatsapp'], $whatsappMessage ?? $contact['whatsapp_message']) ?? '/kontak',
            'visitLabel' => $cta['visit_label'],
            'visitUrl' => $cta['visit_url'],
            // Konteks form lead di modal tombol kunjungan (cluster/tipe di Detail Rumah).
            'lead' => [
                'clusterId' => $lead['clusterId'] ?? null,
                'houseTypeId' => $lead['houseTypeId'] ?? null,
            ],
        ];
    }
}
