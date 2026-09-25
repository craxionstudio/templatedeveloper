<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Milestone 4: event_id konversi, dipakai bersama oleh Meta Pixel (browser) dan
 * Conversions API (server) untuk deduplikasi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->uuid('event_id')->nullable()->unique()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropUnique(['event_id']);
            $table->dropColumn('event_id');
        });
    }
};
