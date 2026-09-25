<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Redirect extends Model
{
    protected $fillable = ['from_path', 'to_path', 'status_code', 'is_automatic'];

    public const CACHE_KEY = 'redirects.map';

    protected static function booted(): void
    {
        // Path disimpan dalam bentuk yang sama dengan pencocokan: huruf kecil, tanpa trailing slash & query.
        static::saving(function (self $redirect): void {
            $redirect->from_path = self::normalize((string) $redirect->from_path);
        });

        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }

    public static function normalize(string $path): string
    {
        $path = strtolower((string) parse_url($path, PHP_URL_PATH));

        return '/'.trim($path, '/');
    }

    /**
     * Peta from_path → [to_path, status_code], di-cache sampai ada perubahan redirect.
     *
     * @return array<string, array{0: ?string, 1: int}>
     */
    public static function map(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, fn (): array => static::query()
            ->get(['from_path', 'to_path', 'status_code'])
            ->mapWithKeys(fn (self $r) => [$r->from_path => [$r->to_path, $r->status_code]])
            ->all());
    }

    protected function casts(): array
    {
        return [
            'status_code' => 'integer',
            'hits' => 'integer',
            'last_hit_at' => 'datetime',
            'is_automatic' => 'boolean',
        ];
    }

    /**
     * Catat redirect 301 dari → ke. Rantai lama yang menunjuk ke `$from` ikut diarahkan
     * langsung ke `$to`, dan redirect yang dari `$to` dihapus supaya tidak berputar.
     */
    public static function remember(string $from, string $to): void
    {
        if ($from === $to) {
            return;
        }

        static::query()->where('from_path', $to)->delete();
        static::query()->where('to_path', $from)->update(['to_path' => $to]);

        static::query()->updateOrCreate(
            ['from_path' => $from],
            ['to_path' => $to, 'status_code' => 301, 'is_automatic' => true],
        );
    }
}
