<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Update 2:
 * - clusters.tanggal_launching: dasar urutan "Terbaru" (kosong paling bawah). Cluster yang baru punya
 *   tahun launching diisi 1 Januari tahun itu.
 * - promos.catatan_internal: sumber data promo (hanya di admin).
 * - kawasan_promo: promo bisa dihubungkan langsung ke kawasan (opsional).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clusters', function (Blueprint $table) {
            $table->date('tanggal_launching')->nullable()->after('launch_year');
            $table->index('tanggal_launching');
        });

        DB::table('clusters')->whereNull('tanggal_launching')->whereNotNull('launch_year')
            ->orderBy('id')
            ->each(fn (object $cluster) => DB::table('clusters')->where('id', $cluster->id)
                ->update(['tanggal_launching' => sprintf('%04d-01-01', $cluster->launch_year)]));

        Schema::table('promos', function (Blueprint $table) {
            $table->text('catatan_internal')->nullable();
        });

        Schema::create('kawasan_promo', function (Blueprint $table) {
            $table->foreignId('kawasan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('promo_id')->constrained()->cascadeOnDelete();
            $table->primary(['kawasan_id', 'promo_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kawasan_promo');

        Schema::table('promos', function (Blueprint $table) {
            $table->dropColumn('catatan_internal');
        });

        Schema::table('clusters', function (Blueprint $table) {
            $table->dropIndex(['tanggal_launching']);
            $table->dropColumn('tanggal_launching');
        });
    }
};
