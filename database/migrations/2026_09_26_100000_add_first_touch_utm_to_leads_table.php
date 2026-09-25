<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * First-touch UTM: kampanye pertama yang membawa pengunjung (utm_* yang sudah ada = kampanye terakhir).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->string('first_utm_source')->nullable()->after('utm_term');
            $table->string('first_utm_medium')->nullable()->after('first_utm_source');
            $table->string('first_utm_campaign')->nullable()->after('first_utm_medium');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn(['first_utm_source', 'first_utm_medium', 'first_utm_campaign']);
        });
    }
};
