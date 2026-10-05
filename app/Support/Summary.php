<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Ringkasan otomatis dari deskripsi/isi (dipakai kalau field ringkasan dikosongkan).
 */
class Summary
{
    public static function from(?string $html, int $limit = 160): ?string
    {
        $text = trim((string) preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags(str_replace(['</p>', '<br>', '<br/>', '<br />'], ' ', (string) $html)), ENT_QUOTES | ENT_HTML5)));

        return $text === '' ? null : Str::limit($text, $limit, '…', preserveWords: true);
    }
}
