<?php

namespace App\Console\Commands;

use App\Enums\PromoPlacement;
use App\Models\Cluster;
use App\Models\Promo;
use App\Support\PageCache;
use App\Support\Sitemaps;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use JsonException;

/**
 * Import file update data (mis. docs/data/bsd-city-update-2.json) di atas data hasil import:bsd-data.
 *
 * - tanggal_launching: diisi per cluster_slug; cluster lain yang hanya punya tahun launching diisi
 *   1 Januari tahun itu (tanggal yang sudah ada tidak diubah).
 * - promos: upsert per judul, placement "detail", relasi ke cluster lewat cluster_slugs (ditambahkan,
 *   relasi dari admin tidak dilepas). "sumber" disimpan sebagai catatan internal. is_published dari
 *   file hanya dipakai saat promo dibuat, supaya promo yang sudah dipublikasikan admin tidak ikut mati.
 *
 * Aman dijalankan berkali-kali.
 */
class ImportBsdUpdate extends Command
{
    protected $signature = 'import:bsd-update {path : File JSON update (relatif ke root project atau path absolut)}';

    protected $description = 'Import update data BSD City: tanggal launching dan promo';

    /** @var array<string, int> */
    private array $counts = [];

    public function handle(): int
    {
        $path = (string) $this->argument('path');
        $path = str_starts_with($path, '/') ? $path : base_path($path);

        if (! is_file($path)) {
            $this->error("File tidak ditemukan: {$path}");

            return self::FAILURE;
        }

        try {
            $data = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            $this->error('JSON tidak valid: '.$e->getMessage());

            return self::FAILURE;
        }

        DB::transaction(function () use ($data): void {
            $this->importLaunchDates($data['tanggal_launching'] ?? []);
            $this->importPromos($data['promos'] ?? []);
        });

        Sitemaps::flush();
        PageCache::flush();

        $this->table(['Data', 'Jumlah'], collect($this->counts)->map(fn (int $n, string $label) => [$label, $n])->values()->all());
        $this->info('Update selesai.');

        return self::SUCCESS;
    }

    /**
     * @param  list<array{cluster_slug: string, tanggal: string}>  $rows
     */
    private function importLaunchDates(array $rows): void
    {
        foreach ($rows as $row) {
            $cluster = Cluster::query()->where('slug', $row['cluster_slug'])->first();

            if (! $cluster) {
                $this->warn("Cluster tidak ditemukan: {$row['cluster_slug']}");
                $this->count('Cluster tidak ditemukan', 1);

                continue;
            }

            $cluster->tanggal_launching = Carbon::parse($row['tanggal'])->toDateString();
            $cluster->launch_year ??= (int) Carbon::parse($row['tanggal'])->year;
            $cluster->save();
            $this->count('Tanggal launching dari file', 1);
        }

        // Cluster yang baru punya tahun launching: 1 Januari tahun itu.
        Cluster::query()->whereNull('tanggal_launching')->whereNotNull('launch_year')->get()
            ->each(function (Cluster $cluster): void {
                $cluster->update(['tanggal_launching' => sprintf('%04d-01-01', $cluster->launch_year)]);
                $this->count('Tanggal launching dari tahun (1 Jan)', 1);
            });
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     */
    private function importPromos(array $rows): void
    {
        foreach (array_values($rows) as $index => $row) {
            $promo = Promo::withTrashed()->firstOrNew(['title' => $row['judul']]);
            $isNew = ! $promo->exists;

            $promo->fill([
                'placement' => PromoPlacement::Detail,
                'label' => $row['label'] ?? $promo->label,
                'description' => $row['deskripsi'] ?? $promo->description,
                'starts_at' => filled($row['starts_at'] ?? null) ? Carbon::parse($row['starts_at'])->startOfDay() : $promo->starts_at,
                'ends_at' => filled($row['ends_at'] ?? null) ? Carbon::parse($row['ends_at'])->endOfDay() : $promo->ends_at,
                'catatan_internal' => $row['sumber'] ?? $promo->catatan_internal,
            ]);

            if ($isNew) {
                $promo->is_published = (bool) ($row['is_published'] ?? false);
                $promo->sort_order = $index + 1;
            }

            $promo->save();

            if ($promo->trashed()) {
                $promo->restore();
            }

            $slugs = $row['cluster_slugs'] ?? [];
            $clusterIds = Cluster::query()->whereIn('slug', $slugs)->pluck('id', 'slug');

            foreach (array_diff($slugs, $clusterIds->keys()->all()) as $missing) {
                $this->warn("Promo \"{$row['judul']}\": cluster tidak ditemukan ({$missing})");
            }

            $promo->clusters()->syncWithoutDetaching($clusterIds->values()->all());

            $this->count($isNew ? 'Promo baru' : 'Promo diperbarui', 1);
            $this->count($promo->is_published ? '  dipublikasikan' : '  belum dipublikasikan', 1);
        }
    }

    private function count(string $label, int $n): void
    {
        $this->counts[$label] = ($this->counts[$label] ?? 0) + $n;
    }
}
