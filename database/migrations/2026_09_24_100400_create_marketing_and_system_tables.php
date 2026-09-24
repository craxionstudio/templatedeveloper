<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('whatsapp', 20); // dinormalisasi ke 62…
            $table->string('email')->nullable();
            $table->foreignId('cluster_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('house_type_id')->nullable()->constrained()->nullOnDelete();
            $table->string('payment_plan', 30)->nullable(); // KPR / cash bertahap / cash keras
            $table->text('message')->nullable();
            $table->string('source_page')->nullable();
            $table->string('source_position', 50)->nullable(); // sidebar / inline / modal / kontak
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->string('utm_content')->nullable();
            $table->string('utm_term')->nullable();
            $table->string('fbclid')->nullable();
            $table->string('gclid')->nullable();
            $table->text('landing_page')->nullable();
            $table->text('referrer')->nullable();
            $table->string('ip_hash', 64)->nullable();
            $table->text('user_agent')->nullable();
            $table->boolean('consent')->default(false);
            $table->string('status', 20)->default('baru'); // App\Enums\LeadStatus
            $table->text('notes')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('utm_source');
        });

        Schema::create('newsletter_subscribers', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('status', 20)->default('aktif'); // aktif / berhenti
            $table->string('source')->nullable();
            $table->timestamps();
        });

        Schema::create('seo_meta', function (Blueprint $table) {
            $table->id();
            $table->morphs('seoable');
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 320)->nullable();
            $table->string('canonical_url')->nullable();
            $table->string('og_image')->nullable(); // path di disk public
            $table->boolean('noindex')->default(false);
            $table->timestamps();

            $table->unique(['seoable_type', 'seoable_id']);
        });

        Schema::create('redirects', function (Blueprint $table) {
            $table->id();
            $table->string('from_path')->unique();
            $table->string('to_path')->nullable(); // kosong untuk 410
            $table->unsignedSmallInteger('status_code')->default(301);
            $table->unsignedInteger('hits')->default(0);
            $table->timestamp('last_hit_at')->nullable();
            $table->boolean('is_automatic')->default(false); // dibuat otomatis saat slug berubah
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('redirects');
        Schema::dropIfExists('seo_meta');
        Schema::dropIfExists('newsletter_subscribers');
        Schema::dropIfExists('leads');
    }
};
