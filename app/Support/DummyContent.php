<?php

namespace App\Support;

use App\Models\Article;
use App\Models\Facility;
use App\Models\FutureDevelopment;
use Database\Seeders\ArticleSeeder;
use Database\Seeders\ContentSeeder;
use Illuminate\Support\Str;

/**
 * Konten contoh dari seeder (fasilitas, pengembangan mendatang, artikel, promo) yang
 * belum diganti data asli. Tidak dihapus, hanya tidak dipublikasikan, supaya bisa
 * dipakai ulang sebagai contoh isian di admin.
 */
class DummyContent
{
    /**
     * @return array<string, int> jenis konten → jumlah yang dinonaktifkan
     */
    public static function unpublish(): array
    {
        $counts = [
            'fasilitas' => Facility::query()->whereIn('name', array_column(ContentSeeder::facilityData(), 0))->where('is_published', true)->update(['is_published' => false]),
            'pengembangan mendatang' => FutureDevelopment::query()->whereIn('title', array_column(ContentSeeder::developmentData(), 1))->where('is_published', true)->update(['is_published' => false]),
            'artikel' => Article::query()->whereIn('slug', array_map(fn (array $article) => Str::slug($article[0]), ArticleSeeder::articleData()))->where('is_published', true)->update(['is_published' => false]),
        ];

        // Update massal tidak memicu event model: buang cache halaman & sitemap manual.
        Sitemaps::flush();
        PageCache::flush();

        return $counts;
    }
}
