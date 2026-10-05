<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

/**
 * Form lead dihapus (keputusan pemilik 5 Okt 2026): semua lead lewat WhatsApp.
 * Sebelum tabel dihapus, semua lead diekspor ke storage/app/backup/leads-{tanggal}.csv
 * (nama cluster & tipe ikut ditulis). File lama dengan nama sama tidak ditimpa.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('leads')) {
            return;
        }

        $directory = storage_path('app/backup');
        File::ensureDirectoryExists($directory);

        $path = $directory.'/leads-'.now()->format('Y-m-d').'.csv';
        for ($i = 2; File::exists($path); $i++) {
            $path = $directory.'/leads-'.now()->format('Y-m-d')."-{$i}.csv";
        }

        $columns = Schema::getColumnListing('leads');
        $handle = fopen($path, 'w');
        fwrite($handle, "\u{FEFF}"); // BOM supaya Excel membaca UTF-8.
        fputcsv($handle, [...$columns, 'cluster_name', 'house_type_name'], escape: '\\');

        DB::table('leads')
            ->leftJoin('clusters', 'clusters.id', '=', 'leads.cluster_id')
            ->leftJoin('house_types', 'house_types.id', '=', 'leads.house_type_id')
            ->select(['leads.*', 'clusters.name as cluster_name', 'house_types.name as house_type_name'])
            ->orderBy('leads.id')
            ->chunk(500, function ($rows) use ($handle, $columns): void {
                foreach ($rows as $row) {
                    $row = (array) $row;
                    fputcsv($handle, [...array_map(fn (string $column) => $row[$column] ?? null, $columns), $row['cluster_name'], $row['house_type_name']], escape: '\\');
                }
            });

        fclose($handle);

        Schema::drop('leads');
    }

    public function down(): void
    {
        // Struktur dasar saja; isi lead ada di file CSV backup.
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('whatsapp', 20);
            $table->string('email')->nullable();
            $table->foreignId('cluster_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('house_type_id')->nullable()->constrained()->nullOnDelete();
            $table->text('message')->nullable();
            $table->string('status', 20)->default('baru');
            $table->timestamps();
        });
    }
};
