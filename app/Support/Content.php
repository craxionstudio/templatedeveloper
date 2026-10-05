<?php

namespace App\Support;

/**
 * Cek isi konten untuk aturan "section tersembunyi otomatis kalau datanya kosong".
 * Teks contoh dalam kurung siku ("[VISI PERUSAHAAN]") dianggap kosong.
 */
class Content
{
    public static function blank(mixed $value): bool
    {
        if (is_array($value)) {
            return collect($value)->every(fn (mixed $item): bool => self::blank($item));
        }

        if (is_string($value)) {
            $text = trim(strip_tags($value));

            return $text === '' || (bool) preg_match('/^\[[^\]]*\]$/u', $text);
        }

        return $value === null || $value === false;
    }

    public static function filled(mixed $value): bool
    {
        return ! self::blank($value);
    }

    /**
     * Item list (repeater) yang benar-benar berisi.
     *
     * @param  array<int, mixed>|null  $items
     * @return list<mixed>
     */
    public static function items(?array $items): array
    {
        return collect($items ?? [])->filter(fn (mixed $item): bool => self::filled($item))->values()->all();
    }
}
