<?php

namespace App\Console\Commands;

use App\Support\Indexing;
use App\Support\WhatsApp;
use Illuminate\Console\Command;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Inertia\DevTools\DevTools;
use Throwable;

/**
 * Laporan status server untuk script deploy: environment, indeks Google, header robots beranda,
 * isi robots.txt, dan perilaku production (HTTPS, cookie, cache, CSP). Request dijalankan lewat
 * kernel aplikasi dengan host APP_URL (tanpa jaringan).
 */
class SiteStatus extends Command
{
    protected $signature = 'ops:site-status';

    protected $description = 'Status environment, indeks Google, robots, dan perilaku production (untuk laporan deploy)';

    public function handle(Kernel $kernel): int
    {
        $base = rtrim((string) config('app.url'), '/');
        $pageCache = (bool) config('site.page_cache.enabled');
        $devtools = DevTools::enabled();
        config(['site.page_cache.enabled' => false, 'inertia.devtools.enabled' => false]);

        try {
            $home = $kernel->handle(Request::create($base.'/'));
            $robots = $kernel->handle(Request::create($base.'/robots.txt'));
        } catch (Throwable $e) {
            $this->error('Gagal mengambil halaman: '.$e->getMessage());

            return self::FAILURE;
        }

        $secure = config('session.secure');
        $rows = [
            ['APP_ENV', app()->environment()],
            ['APP_DEBUG', config('app.debug') ? 'true' : 'false'],
            ['APP_URL', $base],
            ['LOG_LEVEL', (string) config('logging.channels.'.config('logging.default').'.level', config('logging.channels.single.level'))],
            ['Inertia DevTools', $devtools ? 'AKTIF' : 'mati'],
            ['SITE_INDEXABLE', Indexing::allowed() ? 'true' : 'false'],
            ['Beranda: status', (string) $home->getStatusCode()],
            ['Beranda: X-Robots-Tag', $home->headers->get('X-Robots-Tag') ?? '(tidak ada = boleh diindeks)'],
            ['robots.txt', str_replace("\n", ' | ', trim((string) $robots->getContent()))],
            ['Paksa host & HTTPS (APP_URL)', app()->isProduction() ? 'aktif: http -> https dan host lain -> 301 ke '.$base : 'mati (bukan production)'],
            ['HSTS', app()->isProduction() ? 'aktif untuk request HTTPS (max-age 1 tahun, tanpa includeSubDomains)' : 'mati'],
            ['Cookie session secure', $secure === null ? 'tidak diatur (SESSION_SECURE_COOKIE kosong): cookie tanpa flag Secure' : ($secure ? 'ya' : 'tidak')],
            ['Cache halaman (tamu)', $pageCache ? 'aktif' : 'mati'],
            ['Content-Security-Policy', config('site.csp.enabled') ? 'aktif' : 'mati'],
            // Deploy memberi peringatan kalau baris ini KOSONG (semua tombol WA memakai nomor ini).
            ['Nomor WhatsApp', WhatsApp::hasNumber() ? WhatsApp::number() : 'KOSONG: isi di Admin → Pengaturan Umum → WhatsApp'],
        ];

        foreach ($rows as [$label, $value]) {
            $this->line("{$label}: {$value}");
        }

        return self::SUCCESS;
    }
}
