<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Status penjualan cluster (ready stock / inden / sold out) opsional: boleh kosong.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clusters', function (Blueprint $table) {
            $table->string('status', 30)->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        Schema::table('clusters', function (Blueprint $table) {
            $table->string('status', 30)->nullable(false)->default('ready_stock')->change();
        });
    }
};
