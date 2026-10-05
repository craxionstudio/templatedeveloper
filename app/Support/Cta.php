<?php

namespace App\Support;

use App\Models\Cluster;
use App\Models\Kawasan;
use App\Settings\GlobalSettings;

/**
 * Section CTA di bawah halaman: teks CTA global + dua tombol WhatsApp (konsultasi & jadwal survey).
 */
class Cta
{
    /**
     * @param  array<string, mixed>  $section  section "cta" dari settings halaman
     * @param  Cluster|null  $cluster  konteks Detail Rumah (template pesan cluster)
     * @param  Kawasan|null  $kawasan  konteks Detail Kawasan (template pesan kawasan)
     * @return array<string, mixed>
     */
    public static function resolve(array $section, ?Cluster $cluster = null, ?Kawasan $kawasan = null): array
    {
        $cta = app(GlobalSettings::class)->section('cta');
        $useGlobal = (bool) ($section['use_global'] ?? true);

        $text = fn (string $key): string => ! $useGlobal && filled($section[$key] ?? null) ? $section[$key] : $cta[$key];

        return [
            'eyebrow' => $text('eyebrow'),
            'title' => $text('title'),
            'description' => $text('description'),
            'whatsappLabel' => $cta['whatsapp_label'],
            'whatsappUrl' => WhatsApp::url(match (true) {
                $cluster !== null => WhatsApp::CLUSTER,
                $kawasan !== null => WhatsApp::KAWASAN,
                default => WhatsApp::GENERAL,
            }, $cluster, kawasan: $kawasan),
            'surveyLabel' => $cta['visit_label'],
            'surveyUrl' => WhatsApp::url(WhatsApp::SURVEY, $cluster),
            'cluster' => $cluster?->name,
        ];
    }
}
