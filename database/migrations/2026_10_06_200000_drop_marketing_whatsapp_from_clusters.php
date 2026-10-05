<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Satu nomor WhatsApp (6 Okt 2026): semua tombol memakai nomor global di Pengaturan Umum,
 * jadi nomor WA per cluster dihapus.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('clusters', 'marketing_whatsapp')) {
            Schema::table('clusters', function (Blueprint $table) {
                $table->dropColumn('marketing_whatsapp');
            });
        }
    }

    public function down(): void
    {
        Schema::table('clusters', function (Blueprint $table) {
            $table->string('marketing_whatsapp', 20)->nullable()->after('marketing_title');
        });
    }
};
