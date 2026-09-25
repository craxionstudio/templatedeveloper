<?php

use App\Jobs\GenerateImageVariants;
use App\Support\ResponsiveImages;
use App\Support\Sitemaps;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('sitemap:refresh', function () {
    Sitemaps::refresh();
    $this->info('Sitemap dibangun ulang.');
})->purpose('Bangun ulang cache sitemap (juga otomatis saat konten berubah)');

Schedule::command('sitemap:refresh')->dailyAt('03:00');

Artisan::command('images:variants', function () {
    // Settings halaman: varian di storage/app/public/_variants.
    $paths = collect(DB::table('settings')->pluck('payload'))
        ->flatMap(fn (string $payload) => GenerateImageVariants::pathsIn((array) json_decode($payload, true)))
        ->unique()->values();
    $paths->each(fn (string $path) => ResponsiveImages::generateForPath($path));

    // Media library: konversi yang belum ada.
    $this->call('media-library:regenerate', ['--only-missing' => true, '--force' => true]);

    $this->info("Varian gambar settings: {$paths->count()} file. Konversi media library diperbarui.");
})->purpose('Buat varian WebP/AVIF untuk semua gambar yang belum punya');
