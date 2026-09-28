<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Field untuk data asli (import:bsd-data):
 * - Catatan internal & checklist "perlu dilengkapi" (hanya di admin, tidak tampil di website).
 * - Prioritas cluster (1–10), fasilitas cluster, tahun launching / dibuka, lokasi & akses kawasan.
 * - Tipe rumah boleh tanpa nama (harga "mulai" tingkat cluster) dan tanpa jumlah lantai.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kawasans', function (Blueprint $table) {
            $table->json('access')->nullable()->after('facilities'); // ["Akses tol …", …]
            $table->unsignedSmallInteger('opened_year')->nullable()->after('area_ha');
        });

        Schema::table('clusters', function (Blueprint $table) {
            $table->json('facilities')->nullable()->after('specifications'); // ["Clubhouse", …]
            $table->unsignedSmallInteger('launch_year')->nullable()->after('building_type');
            $table->unsignedTinyInteger('prioritas')->nullable()->after('sort_order');
            $table->text('catatan_internal')->nullable();
            $table->json('perlu_dilengkapi')->nullable(); // [{item, selesai}]
            // Jumlah item perlu_dilengkapi yang belum selesai (untuk badge, filter, dan urutan di admin).
            $table->unsignedSmallInteger('perlu_dilengkapi_count')->default(0);

            $table->index('prioritas');
            $table->index('perlu_dilengkapi_count');
        });

        Schema::table('house_types', function (Blueprint $table) {
            $table->string('name')->nullable()->change();
            $table->unsignedTinyInteger('floors')->nullable()->default(null)->change();
            $table->text('catatan_internal')->nullable();
        });
    }

    public function down(): void
    {
        // Kolom kembali wajib: isi dulu nilai kosong.
        DB::table('house_types')->whereNull('name')->update(['name' => DB::raw('slug')]);
        DB::table('house_types')->whereNull('floors')->update(['floors' => 1]);

        Schema::table('house_types', function (Blueprint $table) {
            $table->dropColumn('catatan_internal');
            $table->unsignedTinyInteger('floors')->default(1)->change();
            $table->string('name')->nullable(false)->change();
        });

        Schema::table('clusters', function (Blueprint $table) {
            $table->dropIndex(['prioritas']);
            $table->dropIndex(['perlu_dilengkapi_count']);
            $table->dropColumn(['facilities', 'launch_year', 'prioritas', 'catatan_internal', 'perlu_dilengkapi', 'perlu_dilengkapi_count']);
        });

        Schema::table('kawasans', function (Blueprint $table) {
            $table->dropColumn(['access', 'opened_year']);
        });
    }
};
