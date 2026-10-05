<?php

namespace App\Settings;

use Illuminate\Support\Arr;
use Spatie\LaravelSettings\Settings;

/**
 * Basis semua settings halaman.
 *
 * Isi awal tiap group ada di database/settings/defaults/{group}.php dan dipakai dua kali:
 * 1. oleh migrasi settings (isi awal di database), dan
 * 2. sebagai fallback saat admin mengosongkan field — halaman publik tidak pernah
 *    menampilkan string kosong (brief 7A).
 */
abstract class PageSettings extends Settings
{
    /**
     * @var array<string, array<string, mixed>>
     */
    private static array $defaultsCache = [];

    /**
     * @return array<string, mixed>
     */
    public static function defaults(): array
    {
        return self::$defaultsCache[static::group()] ??= require static::defaultsPath(static::group());
    }

    public static function defaultsPath(string $group): string
    {
        return database_path("settings/defaults/{$group}.php");
    }

    /**
     * Key (dot path) yang bisa diubah admin, jadi nilainya dibaca dari database. Key lain selalu memakai
     * teks tetap di kode (database/settings/defaults/{group}.php), walaupun di database masih ada nilai lama.
     * null = semua key dibaca dari database.
     *
     * Harus sama dengan field di form admin (App\Filament\Pages\Settings).
     *
     * @return list<string>|null
     */
    public static function editable(): ?array
    {
        return null;
    }

    /**
     * Section yang sudah dilengkapi fallback untuk field teks yang kosong.
     *
     * @return array<string, mixed>
     */
    public function section(string $key): array
    {
        return self::withFallback(self::onlyEditable($key, (array) ($this->{$key} ?? [])), static::defaults()[$key] ?? []);
    }

    /**
     * @param  array<string, mixed>  $stored
     * @return array<string, mixed>
     */
    private static function onlyEditable(string $key, array $stored): array
    {
        $editable = static::editable();

        if ($editable === null || in_array($key, $editable, true)) {
            return $stored;
        }

        $result = [];

        foreach ($editable as $path) {
            if (! str_starts_with($path, $key.'.')) {
                continue;
            }

            $relative = substr($path, strlen($key) + 1);

            if (Arr::has($stored, $relative)) {
                Arr::set($result, $relative, Arr::get($stored, $relative));
            }
        }

        return $result;
    }

    /**
     * Isi nilai kosong (null / string kosong) dengan nilai default, rekursif untuk array asosiatif.
     * List (repeater) tidak ditimpa supaya item yang sengaja dihapus admin tidak muncul lagi.
     *
     * @param  array<string, mixed>  $value
     * @param  array<string, mixed>  $default
     * @return array<string, mixed>
     */
    public static function withFallback(array $value, array $default): array
    {
        foreach ($default as $key => $fallback) {
            $current = $value[$key] ?? null;

            if (is_array($fallback) && ! array_is_list($fallback)) {
                $value[$key] = self::withFallback(is_array($current) ? $current : [], $fallback);

                continue;
            }

            if ($current === null || $current === '') {
                $value[$key] = $fallback;
            }
        }

        return $value;
    }
}
