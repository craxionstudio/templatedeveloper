<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Throwable;

/**
 * Cek bahwa halaman benar-benar dirender di server (SSR Inertia), bukan hanya <div id="app"> kosong.
 * Request dijalankan langsung lewat kernel aplikasi (tanpa jaringan, CDN, atau cache halaman).
 * Dipakai di akhir script deploy: php artisan ssr:check /properti
 */
class CheckSsr extends Command
{
    protected $signature = 'ssr:check {path=/properti : Halaman yang dicek}';

    protected $description = 'Cek HTML halaman dirender di server (SSR): status 200, H1, dan isi halaman';

    public function handle(Kernel $kernel): int
    {
        $path = '/'.ltrim((string) $this->argument('path'), '/');
        config(['site.page_cache.enabled' => false]);

        try {
            // Pakai skema & host APP_URL: di production host lain diarahkan 301 ke host kanonik.
            $response = $kernel->handle(Request::create(rtrim((string) config('app.url'), '/').$path));
            $html = (string) $response->getContent();
            $status = $response->getStatusCode();
        } catch (Throwable $e) {
            $this->error("SSR GAGAL {$path}: {$e->getMessage()}");

            return self::FAILURE;
        }

        $problems = self::problems($status, $html);

        if ($problems !== []) {
            $this->error("SSR GAGAL {$path}: ".implode('; ', $problems));

            return self::FAILURE;
        }

        preg_match('/<h1[^>]*>(.*?)<\/h1>/s', $html, $h1);
        $this->info("SSR OK {$path}: H1 \"".trim(strip_tags($h1[1] ?? '')).'", '.number_format(strlen($html)).' byte HTML');

        return self::SUCCESS;
    }

    /**
     * @return list<string>
     */
    public static function problems(int $status, string $html): array
    {
        $problems = [];

        if ($status !== 200) {
            $problems[] = "status {$status}";
        }

        if (! str_contains($html, 'data-server-rendered="true"')) {
            $problems[] = 'tidak dirender di server (div #app kosong, server SSR tidak terhubung)';
        }

        if (preg_match_all('/<h1[\s>]/i', $html) < 1) {
            $problems[] = 'tanpa H1';
        }

        return $problems;
    }
}
