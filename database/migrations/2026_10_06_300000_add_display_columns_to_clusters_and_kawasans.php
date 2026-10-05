<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Update 3 (6 Okt 2026): hanya sebagian cluster punya halaman sendiri; sisanya hanya tampil sebagai nama
 * di /properti/cluster-lainnya. Kawasan tanpa halaman sendiri hanya jadi judul grup di daftar itu.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clusters', function (Blueprint $table) {
            $table->string('tampil_sebagai', 20)->default('halaman')->index()->after('is_published');
        });

        Schema::table('kawasans', function (Blueprint $table) {
            $table->boolean('punya_halaman')->default(true)->after('is_published');
        });
    }

    public function down(): void
    {
        Schema::table('clusters', function (Blueprint $table) {
            $table->dropIndex(['tampil_sebagai']);
            $table->dropColumn('tampil_sebagai');
        });

        Schema::table('kawasans', function (Blueprint $table) {
            $table->dropColumn('punya_halaman');
        });
    }
};
