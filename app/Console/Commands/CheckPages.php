<?php

namespace App\Console\Commands;

use App\Support\Sitemaps;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * QA SSR (brief 8.1 & 11.7): ambil setiap URL di sitemap lewat HTTP (seperti crawler, tanpa
 * JavaScript) dan pastikan HTML awal berisi satu H1, canonical, JSON-LD, og:image, dan
 * meta robots. Jalankan setelah deploy: php artisan qa:pages --base=https://domain-anda.com
 */
class CheckPages extends Command
{
    protected $signature = 'qa:pages
        {--base= : URL dasar yang dicek (default APP_URL)}
        {--limit=0 : Batasi jumlah URL (0 = semua)}';

    protected $description = 'Cek HTML awal (SSR) semua URL di sitemap: status 200, satu H1, canonical, JSON-LD, og:image, robots';

    public function handle(): int
    {
        $base = rtrim((string) ($this->option('base') ?: config('app.url')), '/');
        $paths = $this->paths();

        if ((int) $this->option('limit') > 0) {
            $paths = array_slice($paths, 0, (int) $this->option('limit'));
        }

        $rows = [];
        $failed = 0;

        foreach ($paths as $path) {
            try {
                $response = Http::timeout(20)->withHeaders(['User-Agent' => 'ArunikaQA/1.0'])->get($base.$path);
                $problems = self::problems($response->status(), $response->body());
            } catch (Throwable $e) {
                $problems = ['gagal diambil: '.$e->getMessage()];
            }

            $failed += $problems === [] ? 0 : 1;
            $rows[] = [$path, $problems === [] ? 'OK' : 'GAGAL: '.implode('; ', $problems)];
        }

        $this->table(['URL', 'Hasil'], $rows);
        $this->line(sprintf('%d URL dicek, %d bermasalah.', count($paths), $failed));

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }

    /**
     * @return list<string>
     */
    public static function problems(int $status, string $html): array
    {
        $head = explode('</head>', $html)[0];
        $problems = [];

        if ($status !== 200) {
            $problems[] = "status {$status}";
        }

        $h1 = preg_match_all('/<h1[\s>]/i', $html);

        if ($h1 !== 1) {
            $problems[] = "{$h1} H1";
        }

        foreach ([
            'canonical' => '/<link[^>]+rel="canonical"/i',
            'JSON-LD' => '/<script[^>]+application\/ld\+json/i',
            'og:image' => '/<meta[^>]+property="og:image"/i',
            'meta robots' => '/<meta[^>]+name="robots"/i',
            'title' => '/<title[^>]*>[^<]+<\/title>/i',
        ] as $label => $pattern) {
            if (! preg_match($pattern, $head)) {
                $problems[] = "tanpa {$label}";
            }
        }

        return [...$problems, ...self::structuredDataProblems($head)];
    }

    /**
     * Setiap blok JSON-LD harus JSON valid dengan @context schema.org dan @type yang dikenal.
     *
     * @return list<string>
     */
    public static function structuredDataProblems(string $head): array
    {
        preg_match_all('#<script[^>]+application/ld\+json[^>]*>(.*?)</script>#is', $head, $blocks);
        $problems = [];

        foreach ($blocks[1] as $json) {
            $data = json_decode(html_entity_decode($json), true);

            if (! is_array($data)) {
                $problems[] = 'JSON-LD tidak valid';

                continue;
            }

            if (($data['@context'] ?? null) !== 'https://schema.org') {
                $problems[] = 'JSON-LD tanpa @context schema.org';
            }

            foreach (self::types($data) as $type) {
                if (! class_exists('Spatie\\SchemaOrg\\'.$type)) {
                    $problems[] = "@type tidak dikenal: {$type}";
                }
            }
        }

        return array_values(array_unique($problems));
    }

    /**
     * @param  array<mixed>  $data
     * @return list<string>
     */
    private static function types(array $data): array
    {
        $types = [];

        foreach ($data as $key => $value) {
            if ($key === '@type') {
                $types = [...$types, ...(array) $value];
            } elseif (is_array($value)) {
                $types = [...$types, ...self::types($value)];
            }
        }

        return array_values(array_unique($types));
    }

    /**
     * @return list<string>
     */
    private function paths(): array
    {
        $paths = [];

        foreach (Sitemaps::NAMES as $name) {
            preg_match_all('#<loc>([^<]+)</loc>#', Sitemaps::render($name), $matches);

            foreach ($matches[1] as $url) {
                $paths[] = (string) (parse_url(html_entity_decode($url), PHP_URL_PATH) ?: '/');
            }
        }

        return array_values(array_unique($paths));
    }
}
