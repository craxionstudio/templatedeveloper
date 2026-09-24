<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Redirect extends Model
{
    protected $fillable = ['from_path', 'to_path', 'status_code', 'is_automatic'];

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
