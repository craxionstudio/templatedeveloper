<?php

use App\Support\BenefitCatalog;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bank Benefit (pengganti sistem Promo): daftar benefit tetap yang dicentang per cluster.
 * Tidak ada periode/tanggal berakhir: benefit tampil selama terhubung ke cluster.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('benefits', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80);
            $table->string('slug', 80)->unique();
            $table->string('category', 20); // App\Enums\BenefitCategory
            $table->string('icon', 40)->nullable(); // App\Support\IconOptions
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });

        Schema::create('benefit_cluster', function (Blueprint $table) {
            $table->id();
            $table->foreignId('benefit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cluster_id')->constrained()->cascadeOnDelete();
            $table->string('teks_tampil', 40)->nullable(); // kosong = nama benefit
            $table->unsignedInteger('urutan')->default(0);
            $table->timestamps();

            $table->unique(['cluster_id', 'benefit_id']);
            $table->index('benefit_id');
        });

        // Jejak import:bsd-update: pivot yang dibuat/diubah import. Kalau pivot berubah atau dihapus
        // di admin setelah import, import berikutnya tidak menimpa atau membuatnya lagi.
        Schema::create('benefit_cluster_imports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('benefit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cluster_id')->constrained()->cascadeOnDelete();
            $table->timestamp('imported_at');

            $table->unique(['cluster_id', 'benefit_id']);
        });

        BenefitCatalog::seed();
    }

    public function down(): void
    {
        Schema::dropIfExists('benefit_cluster_imports');
        Schema::dropIfExists('benefit_cluster');
        Schema::dropIfExists('benefits');
    }
};
