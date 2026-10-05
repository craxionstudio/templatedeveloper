<?php

namespace App\Console\Commands;

use App\Enums\ClusterDisplay;
use App\Enums\PromoPlacement;
use App\Models\Benefit;
use App\Models\BenefitCluster;
use App\Models\Cluster;
use App\Models\Kawasan;
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
 * - cluster_tampilan (update 3): slug → tampil_sebagai ("halaman" / "daftar"); kawasan_tampilan: slug →
 *   punya_halaman (true / false). Nilai dari file selalu dipakai (bisa diubah lagi di admin, lalu ditimpa
 *   kalau file yang sama di-import ulang).
 *
 * - benefits: benefit per cluster (Bank Benefit), upsert per (cluster, benefit); pivot yang diubah
 *   atau dilepas di admin setelah import sebelumnya tidak disentuh.
 *
 * Aman dijalankan berkali-kali dan tidak pernah meminta konfirmasi (tidak ada yang dihapus), jadi
 * --force hanya diterima supaya script deploy bisa memanggil semua import dengan flag yang sama.
 */
class ImportBsdUpdate extends Command
{
    protected $signature = 'import:bsd-update
        {path : File JSON update (relatif ke root project atau path absolut)}
        {--force : Jalankan di production tanpa konfirmasi (untuk script deploy)}';

    protected $description = 'Import update data BSD City: tanggal launching, promo, benefit, dan tampilan cluster/kawasan';

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
            $this->importBenefits($data['benefits'] ?? []);
            $this->importClusterDisplay($data['cluster_tampilan'] ?? []);
            $this->importKawasanPages($data['kawasan_tampilan'] ?? []);
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
     * Daftar {slug, tampil_sebagai} atau peta slug → tampil_sebagai.
     *
     * @param  array<int|string, mixed>  $rows
     */
    private function importClusterDisplay(array $rows): void
    {
        foreach (self::pairs($rows, 'tampil_sebagai') as $slug => $value) {
            $display = ClusterDisplay::tryFrom((string) $value);

            if (! $display) {
                $this->warn("Tampilan cluster tidak dikenal untuk {$slug}: {$value}");

                continue;
            }

            if (Cluster::query()->where('slug', $slug)->update(['tampil_sebagai' => $display->value]) === 0) {
                $this->warn("Cluster tidak ditemukan: {$slug}");
                $this->count('Cluster tidak ditemukan', 1);

                continue;
            }

            $this->count($display === ClusterDisplay::Halaman ? 'Cluster dengan halaman sendiri' : 'Cluster di daftar Cluster Lainnya', 1);
        }
    }

    /**
     * Daftar {slug, punya_halaman} atau peta slug → punya_halaman.
     *
     * @param  array<int|string, mixed>  $rows
     */
    private function importKawasanPages(array $rows): void
    {
        foreach (self::pairs($rows, 'punya_halaman') as $slug => $value) {
            $hasPage = filter_var($value, FILTER_VALIDATE_BOOL);

            if (Kawasan::query()->where('slug', $slug)->update(['punya_halaman' => $hasPage]) === 0) {
                $this->warn("Kawasan tidak ditemukan: {$slug}");
                $this->count('Kawasan tidak ditemukan', 1);

                continue;
            }

            $this->count($hasPage ? 'Kawasan dengan halaman sendiri' : 'Kawasan tanpa halaman sendiri', 1);
        }
    }

    /**
     * @param  array<int|string, mixed>  $rows
     * @return array<string, mixed>
     */
    private static function pairs(array $rows, string $key): array
    {
        return array_is_list($rows)
            ? collect($rows)->filter(fn ($row): bool => is_array($row) && filled($row['slug'] ?? null))->mapWithKeys(fn (array $row): array => [$row['slug'] => $row[$key] ?? null])->all()
            : $rows;
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

    /**
     * Benefit per cluster (Bank Benefit), upsert per (cluster, benefit). Pivot yang diubah atau
     * dihapus di admin setelah import sebelumnya tidak ditimpa / tidak dibuat ulang; pivot yang dibuat
     * admin sendiri juga dibiarkan.
     *
     * @param  list<array{cluster_slug: string, benefit_slug: string, teks_tampil?: ?string}>  $rows
     */
    private function importBenefits(array $rows): void
    {
        $clusters = Cluster::query()->whereIn('slug', array_column($rows, 'cluster_slug'))->pluck('id', 'slug');
        $benefits = Benefit::query()->whereIn('slug', array_column($rows, 'benefit_slug'))->pluck('id', 'slug');

        foreach ($rows as $row) {
            $clusterId = $clusters[$row['cluster_slug']] ?? null;
            $benefitId = $benefits[$row['benefit_slug']] ?? null;

            if (! $clusterId || ! $benefitId) {
                $this->warn(sprintf('Benefit dilewati: %s ← %s (%s tidak ditemukan)', $row['cluster_slug'], $row['benefit_slug'], $clusterId ? 'benefit' : 'cluster'));
                $this->count('Benefit dilewati (tidak ditemukan)', 1);

                continue;
            }

            $text = filled($row['teks_tampil'] ?? null) ? mb_substr(trim($row['teks_tampil']), 0, 40) : null;
            $pivot = BenefitCluster::query()->where(['cluster_id' => $clusterId, 'benefit_id' => $benefitId])->first();
            $log = DB::table('benefit_cluster_imports')->where(['cluster_id' => $clusterId, 'benefit_id' => $benefitId])->first();

            if ($pivot && (! $log || $pivot->updated_at->gt(Carbon::parse($log->imported_at)))) {
                // Dibuat atau diubah admin setelah import terakhir.
                $this->count('Benefit dilewati (diubah di admin)', 1);

                continue;
            }

            if (! $pivot && $log) {
                // Pernah diimport lalu dilepas di admin.
                $this->count('Benefit dilewati (dilepas di admin)', 1);

                continue;
            }

            $now = now()->startOfSecond();
            $pivot ??= new BenefitCluster([
                'cluster_id' => $clusterId,
                'benefit_id' => $benefitId,
                'urutan' => (int) BenefitCluster::query()->where('cluster_id', $clusterId)->max('urutan') + 1,
            ]);
            $isNew = ! $pivot->exists;
            $pivot->teks_tampil = $text;
            $pivot->updated_at = $now;
            $pivot->save();

            DB::table('benefit_cluster_imports')->updateOrInsert(
                ['cluster_id' => $clusterId, 'benefit_id' => $benefitId],
                ['imported_at' => $pivot->updated_at],
            );

            $this->count($isNew ? 'Benefit baru' : 'Benefit diperbarui', 1);
        }
    }

    private function count(string $label, int $n): void
    {
        $this->counts[$label] = ($this->counts[$label] ?? 0) + $n;
    }
}
