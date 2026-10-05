<?php

namespace App\Support;

use App\Models\Cluster;
use App\Settings\GlobalSettings;

/**
 * Section CTA di bawah halaman: teks CTA global + dua tombol WhatsApp (konsultasi & jadwal survey).
 */
class Cta
{
    /**
     * @param  array<string, mixed>  $section  section "cta" dari settings halaman
     * @param  Cluster|null  $cluster  konteks Detail Rumah (nomor & template pesan cluster)
     * @return array<string, mixed>
     */
    public static function resolve(array $section, ?Cluster $cluster = null): array
    {
        $cta = app(GlobalSettings::class)->section('cta');
        $useGlobal = (bool) ($section['use_global'] ?? true);

        $text = fn (string $key): string => ! $useGlobal && filled($section[$key] ?? null) ? $section[$key] : $cta[$key];

        return [
            'eyebrow' => $text('eyebrow'),
            'title' => $text('title'),
            'description' => $text('description'),
            'whatsappLabel' => $cta['whatsapp_label'],
            'whatsappUrl' => WhatsApp::url($cluster ? WhatsApp::CLUSTER : WhatsApp::GENERAL, $cluster),
            'surveyLabel' => $cta['visit_label'],
            'surveyUrl' => WhatsApp::url(WhatsApp::SURVEY, $cluster),
            'cluster' => $cluster?->name,
        ];
    }
}
