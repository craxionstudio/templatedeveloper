<?php

namespace App\Support;

use App\Models\Cluster;
use App\Models\Kawasan;
use App\Settings\GlobalSettings;

/**
 * Semua ajakan menghubungi lewat WhatsApp (form lead dihapus Okt 2026).
 *
 * - Satu nomor: nomor WA global di Pengaturan Umum (wajib diisi) untuk semua tombol.
 * - Selalu link langsung https://wa.me/{nomor}?text={pesan}; pesan di-URL-encode (baris baru = %0A).
 * - Pesan: template per konteks di Pengaturan Umum dengan placeholder {nama_cluster}, {nama_kawasan},
 *   {judul_halaman}, {link_halaman}. Placeholder yang tidak dipakai di template tidak menambah apa pun.
 */
class WhatsApp
{
    public const GENERAL = 'whatsapp_message';

    public const CLUSTER = 'whatsapp_cluster_message';

    public const PROMO = 'whatsapp_promo_message';

    public const SURVEY = 'whatsapp_survey_message';

    public const KAWASAN = 'whatsapp_kawasan_message';

    /** Placeholder template → keterangan (ditampilkan di bawah field template di admin). */
    public const PLACEHOLDERS = [
        '{nama_cluster}' => 'nama cluster (di luar Detail Rumah: nama brand)',
        '{nama_kawasan}' => 'nama kawasan (di Detail Rumah: kawasan cluster; di halaman lain: nama brand)',
        '{judul_halaman}' => 'judul halaman yang sedang dibuka',
        '{link_halaman}' => 'URL lengkap halaman yang sedang dibuka, tanpa query string',
    ];

    /** Atribut request tempat judul halaman disimpan (diisi PageMeta::make). */
    private const TITLE_ATTRIBUTE = 'whatsapp.page_title';

    /**
     * Nomor WA global, dinormalisasi ke 62…; string kosong kalau belum diisi.
     */
    public static function number(): string
    {
        return self::digits(app(GlobalSettings::class)->section('contact')['whatsapp'] ?? null);
    }

    public static function hasNumber(): bool
    {
        return (bool) preg_match('/^62\d{8,13}$/', self::number());
    }

    /**
     * @param  string|null  $subject  pengisi {nama_cluster}; null = nama cluster, atau nama brand di luar Detail Rumah
     */
    public static function message(string $context, ?Cluster $cluster = null, ?string $subject = null, ?Kawasan $kawasan = null): string
    {
        $global = app(GlobalSettings::class);
        $contact = $global->section('contact');
        $brand = $global->section('identity')['brand_name'];

        return self::render((string) ($contact[$context] ?? $contact[self::GENERAL]), [
            'nama_cluster' => $subject ?? $cluster?->name ?? $brand,
            'nama_kawasan' => $kawasan?->name ?? $cluster?->kawasan?->name ?? $brand,
            'judul_halaman' => self::pageTitle(),
            'link_halaman' => self::pageUrl(),
        ]);
    }

    /**
     * Isi placeholder template. Baris baru Windows (\r\n dari textarea) disamakan jadi \n.
     *
     * @param  array{nama_cluster: string, nama_kawasan: string, judul_halaman: string, link_halaman: string}  $values
     */
    public static function render(string $template, array $values): string
    {
        return trim(strtr(str_replace(["\r\n", "\r"], "\n", $template), [
            '{nama_cluster}' => $values['nama_cluster'],
            // {cluster} = placeholder lama, tetap didukung.
            '{cluster}' => $values['nama_cluster'],
            '{nama_kawasan}' => $values['nama_kawasan'],
            '{judul_halaman}' => $values['judul_halaman'],
            '{link_halaman}' => $values['link_halaman'],
        ]));
    }

    /**
     * Link wa.me untuk konteks tombol di halaman yang sedang dibuka.
     */
    public static function url(string $context = self::GENERAL, ?Cluster $cluster = null, ?string $subject = null, ?Kawasan $kawasan = null): string
    {
        return self::link(self::number(), self::message($context, $cluster, $subject, $kawasan));
    }

    /**
     * https://wa.me/{nomor}?text={pesan}. Nomor 08… dinormalisasi ke 628…; pesan di-URL-encode (RFC 3986,
     * spasi = %20, baris baru = %0A).
     */
    public static function link(?string $number, ?string $message = null): string
    {
        $url = 'https://wa.me/'.self::digits($number);

        return filled($message) ? $url.'?text='.rawurlencode($message) : $url;
    }

    /**
     * URL lengkap halaman yang sedang dibuka: APP_URL + path, tanpa query string.
     */
    public static function pageUrl(): string
    {
        return StructuredData::url(request()->path());
    }

    /**
     * Judul halaman yang sedang dibuka (judul yang diberikan ke PageMeta, tanpa akhiran nama brand).
     */
    public static function pageTitle(): string
    {
        return (string) (request()->attributes->get(self::TITLE_ATTRIBUTE) ?: app(GlobalSettings::class)->section('identity')['brand_name']);
    }

    public static function setPageTitle(string $title): void
    {
        request()->attributes->set(self::TITLE_ATTRIBUTE, $title);
    }

    private static function digits(?string $number): string
    {
        $digits = (string) preg_replace('/\D+/', '', (string) $number);

        return str_starts_with($digits, '0') ? '62'.substr($digits, 1) : $digits;
    }
}
