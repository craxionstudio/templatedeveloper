<?php

namespace App\Support;

use App\Models\Cluster;
use App\Settings\GlobalSettings;

/**
 * Semua ajakan menghubungi lewat WhatsApp (form lead dihapus Okt 2026).
 *
 * Nomor: nomor WA cluster kalau diisi, kalau kosong nomor global (Pengaturan Umum).
 * Pesan: template per konteks di Pengaturan Umum, placeholder {nama_cluster}.
 */
class WhatsApp
{
    public const GENERAL = 'whatsapp_message';

    public const CLUSTER = 'whatsapp_cluster_message';

    public const PROMO = 'whatsapp_promo_message';

    public const SURVEY = 'whatsapp_survey_message';

    public static function number(?Cluster $cluster = null): ?string
    {
        return $cluster?->marketing_whatsapp ?: app(GlobalSettings::class)->section('contact')['whatsapp'];
    }

    /**
     * @param  string|null  $subject  pengisi {nama_cluster}; null = nama cluster, atau nama brand di luar Detail Rumah
     */
    public static function message(string $context, ?Cluster $cluster = null, ?string $subject = null): string
    {
        $global = app(GlobalSettings::class);
        $template = (string) ($global->section('contact')[$context] ?? $global->section('contact')[self::GENERAL]);
        $name = $subject ?? $cluster?->name ?? $global->section('identity')['brand_name'];

        // {cluster} = placeholder lama, tetap didukung.
        return str_replace(['{nama_cluster}', '{cluster}'], $name, $template);
    }

    /**
     * Link wa.me; tanpa nomor sama sekali = info kontak di footer (halaman Kontak sudah dihapus).
     */
    public static function url(string $context = self::GENERAL, ?Cluster $cluster = null, ?string $subject = null): string
    {
        return SiteLayout::whatsappUrl(self::number($cluster), self::message($context, $cluster, $subject)) ?? '#info-kontak';
    }
}
