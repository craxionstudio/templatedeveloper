<?php

namespace App\Support;

/**
 * Nomor WhatsApp Indonesia: 08…, +62…, 62…, atau 8… dinormalisasi ke 62…
 */
class Phone
{
    /**
     * Nomor seluler Indonesia yang dinormalisasi ke 62…, atau null kalau tidak valid.
     */
    public static function normalize(?string $number): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $number);

        $digits = match (true) {
            str_starts_with($digits, '62') => $digits,
            str_starts_with($digits, '0') => '62'.substr($digits, 1),
            str_starts_with($digits, '8') => '62'.$digits,
            default => $digits,
        };

        // Seluler: 62 8xx, total 10–13 digit setelah 62.
        return preg_match('/^628\d{7,11}$/', $digits) ? $digits : null;
    }
}
