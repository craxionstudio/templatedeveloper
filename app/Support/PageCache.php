<?php

namespace App\Support;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Cache HTML halaman publik (brief 8.6). Kunci memuat "versi konten" yang dinaikkan setiap
 * konten/media/settings berubah, dan hash build aset — jadi deploy atau edit admin langsung
 * membuat cache lama tidak terpakai (tanpa perlu menghapus satu per satu).
 */
class PageCache
{
    public const VERSION_KEY = 'page-cache:version';

    /**
     * Parameter yang tidak mengubah isi halaman (atribusi kampanye).
     */
    public const IGNORED_QUERY = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term', 'fbclid', 'gclid', 'gbraid', 'wbraid', 'msclkid'];

    public static function store(): Repository
    {
        return Cache::store(config('site.page_cache.store'));
    }

    public static function enabled(): bool
    {
        return (bool) config('site.page_cache.enabled');
    }

    public static function flush(): void
    {
        $store = self::store();
        $store->forever(self::VERSION_KEY, (int) $store->get(self::VERSION_KEY, 0) + 1);
    }

    public static function key(Request $request): string
    {
        $query = collect($request->query())->except(self::IGNORED_QUERY)->sortKeys()->all();
        $manifest = public_path('build/manifest.json');

        return 'page:'.self::store()->get(self::VERSION_KEY, 0).':'.md5(implode('|', [
            $request->getSchemeAndHttpHost(),
            $request->path(),
            http_build_query($query),
            is_file($manifest) ? filemtime($manifest) : '',
        ]));
    }
}
