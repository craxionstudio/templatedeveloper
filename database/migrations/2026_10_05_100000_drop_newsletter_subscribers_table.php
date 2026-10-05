<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Newsletter dihapus (keputusan pemilik 5 Okt 2026): form di halaman Artikel, menu admin, dan tabelnya.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('newsletter_subscribers');
    }

    public function down(): void
    {
        Schema::create('newsletter_subscribers', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('status', 20)->default('aktif');
            $table->string('source')->nullable();
            $table->timestamps();
        });
    }
};
