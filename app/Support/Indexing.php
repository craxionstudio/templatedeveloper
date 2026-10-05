<?php

namespace App\Support;

/**
 * Status indeks mesin pencari (SITE_INDEXABLE, config site.indexable), tidak bergantung pada APP_ENV.
 */
class Indexing
{
    public static function allowed(): bool
    {
        return (bool) config('site.indexable');
    }

    public static function label(): string
    {
        return self::allowed()
            ? 'Website boleh diindeks Google (SITE_INDEXABLE=true).'
            : 'Website belum diindeks Google (domain sementara). Semua halaman noindex, robots.txt memblokir semua.';
    }
}
