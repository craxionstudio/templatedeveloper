<?php

use App\Support\Sitemaps;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('sitemap:refresh', function () {
    Sitemaps::refresh();
    $this->info('Sitemap dibangun ulang.');
})->purpose('Bangun ulang cache sitemap (juga otomatis saat konten berubah)');

Schedule::command('sitemap:refresh')->dailyAt('03:00');
