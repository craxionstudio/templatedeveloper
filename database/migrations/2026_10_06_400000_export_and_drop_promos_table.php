<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

/**
 * Sistem promo lama dihapus (6 Okt 2026): diganti Bank Benefit (benefit per cluster, update-4).
 * Sebelum tabel dihapus, semua promo diekspor ke storage/app/backup/promos-{tanggal}.csv beserta
 * slug cluster & kawasan yang terhubung. File lama dengan nama sama tidak ditimpa.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('promos')) {
            $this->export();
        }

        Schema::dropIfExists('kawasan_promo');
        Schema::dropIfExists('cluster_promo');
        Schema::dropIfExists('promos');
    }

    private function export(): void
    {
        $directory = storage_path('app/backup');
        File::ensureDirectoryExists($directory);

        $path = $directory.'/promos-'.now()->format('Y-m-d').'.csv';
        for ($i = 2; File::exists($path); $i++) {
            $path = $directory.'/promos-'.now()->format('Y-m-d')."-{$i}.csv";
        }

        $related = function (string $pivot, string $table, string $key): array {
            if (! Schema::hasTable($pivot)) {
                return [];
            }

            return DB::table($pivot)->join($table, "{$table}.id", '=', "{$pivot}.{$key}")
                ->orderBy("{$table}.slug")->get(["{$pivot}.promo_id", "{$table}.slug"])
                ->groupBy('promo_id')->map(fn ($rows) => $rows->pluck('slug')->implode(', '))->all();
        };
        $clusters = $related('cluster_promo', 'clusters', 'cluster_id');
        $kawasans = $related('kawasan_promo', 'kawasans', 'kawasan_id');

        $columns = Schema::getColumnListing('promos');
        $handle = fopen($path, 'w');
        fwrite($handle, "\u{FEFF}"); // BOM supaya Excel membaca UTF-8.
        fputcsv($handle, [...$columns, 'cluster_slugs', 'kawasan_slugs'], escape: '\\');

        DB::table('promos')->orderBy('id')->chunk(500, function ($rows) use ($handle, $columns, $clusters, $kawasans): void {
            foreach ($rows as $row) {
                $row = (array) $row;
                fputcsv($handle, [...array_map(fn (string $column) => $row[$column] ?? null, $columns), $clusters[$row['id']] ?? '', $kawasans[$row['id']] ?? ''], escape: '\\');
            }
        });

        fclose($handle);
    }

    public function down(): void
    {
        // Struktur dasar saja; isi promo ada di file CSV backup.
        Schema::create('promos', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('label')->nullable();
            $table->text('description')->nullable();
            $table->string('placement', 30)->default('home_banner');
            $table->boolean('is_published')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('cluster_promo', function (Blueprint $table) {
            $table->foreignId('cluster_id')->constrained()->cascadeOnDelete();
            $table->foreignId('promo_id')->constrained()->cascadeOnDelete();
            $table->primary(['cluster_id', 'promo_id']);
        });

        Schema::create('kawasan_promo', function (Blueprint $table) {
            $table->foreignId('kawasan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('promo_id')->constrained()->cascadeOnDelete();
            $table->primary(['kawasan_id', 'promo_id']);
        });
    }
};
