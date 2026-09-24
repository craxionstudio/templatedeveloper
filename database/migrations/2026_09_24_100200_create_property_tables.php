<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Hierarki properti (Revisi 2): Profil Lokasi → Kawasan (opsional) → Cluster → Tipe rumah.
 * Uang disimpan dalam rupiah (integer). Repeater disimpan sebagai JSON.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Profil lokasi / kota mandiri. Satu baris saja.
        Schema::create('areas', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('location')->nullable();
            $table->text('description')->nullable();
            $table->decimal('area_ha', 8, 2)->nullable();
            $table->string('hero_alt')->nullable();
            $table->text('map_embed_url')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('map_badge_label')->nullable();
            $table->string('map_badge_value')->nullable();
            $table->json('advantages')->nullable(); // [{icon, title, description, distance}]
            $table->timestamps();
        });

        Schema::create('kawasans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('summary', 300)->nullable();
            $table->string('about_title')->nullable();
            $table->longText('description')->nullable();
            $table->decimal('area_ha', 8, 2)->nullable();
            $table->string('hero_alt')->nullable();
            $table->json('facilities')->nullable(); // [{icon, title, description}]
            $table->text('map_embed_url')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_published', 'sort_order']);
        });

        Schema::create('clusters', function (Blueprint $table) {
            $table->id();
            // null = cluster mandiri
            $table->foreignId('kawasan_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('building_type')->nullable(); // "Rumah 2 lantai"
            $table->string('property_type', 30)->default('rumah'); // App\Enums\PropertyType
            $table->string('summary', 300)->nullable();
            $table->longText('description')->nullable();
            $table->string('address')->nullable();
            $table->string('badge', 30)->nullable(); // App\Enums\ClusterBadge
            $table->string('status', 30)->default('ready_stock'); // App\Enums\ClusterStatus
            $table->unsignedBigInteger('booking_fee')->nullable();
            $table->string('price_note')->nullable();
            $table->string('installment_note')->nullable();
            $table->string('booking_fee_note')->nullable();
            $table->json('specifications')->nullable(); // [{label, value}]
            $table->string('legality')->nullable();
            $table->string('video_url')->nullable();
            $table->string('tour_360_url')->nullable();
            $table->string('marketing_name')->nullable();
            $table->string('marketing_title')->nullable();
            $table->string('marketing_whatsapp', 20)->nullable();
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamp('published_at')->nullable();

            // Kolom turunan dari house_types yang dipublikasikan (diisi observer, jangan diisi manual).
            $table->unsignedSmallInteger('house_types_count')->default(0);
            $table->unsignedBigInteger('price_min')->nullable();
            $table->unsignedBigInteger('price_max')->nullable();
            $table->unsignedBigInteger('installment_min')->nullable();
            $table->unsignedSmallInteger('land_area_min')->nullable();
            $table->unsignedSmallInteger('land_area_max')->nullable();
            $table->unsignedTinyInteger('bedrooms_min')->nullable();
            $table->unsignedTinyInteger('bedrooms_max')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_published', 'sort_order']);
            $table->index('price_min');
        });

        Schema::create('house_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cluster_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('lot_size', 20)->nullable(); // "7×15"
            $table->unsignedSmallInteger('land_area')->nullable(); // LT m²
            $table->unsignedSmallInteger('building_area')->nullable(); // LB m²
            $table->unsignedTinyInteger('bedrooms')->default(0);
            $table->unsignedTinyInteger('extra_bedrooms')->default(0); // "3+1"
            $table->unsignedTinyInteger('bathrooms')->default(0);
            $table->unsignedTinyInteger('floors')->default(1);
            $table->unsignedTinyInteger('carports')->default(0);
            $table->unsignedBigInteger('price_from')->nullable();
            $table->unsignedBigInteger('installment_from')->nullable();
            $table->unsignedSmallInteger('units_available')->nullable();
            $table->string('floorplan_alt')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();

            $table->unique(['cluster_id', 'slug']);
            $table->index(['cluster_id', 'is_published', 'sort_order']);
        });

        Schema::create('promos', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('label')->nullable(); // "Promo September"
            $table->text('description')->nullable();
            $table->json('items')->nullable(); // [{icon, title, description}] untuk "Promo rumah ini"
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->string('period_label')->nullable(); // "Berlaku s.d. [TANGGAL]"
            $table->string('placement', 30)->default('home_banner'); // App\Enums\PromoPlacement
            $table->string('cta_label')->nullable();
            $table->string('cta_url')->nullable();
            $table->string('image_alt')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['placement', 'is_published', 'starts_at', 'ends_at']);
        });

        Schema::create('cluster_promo', function (Blueprint $table) {
            $table->foreignId('cluster_id')->constrained()->cascadeOnDelete();
            $table->foreignId('promo_id')->constrained()->cascadeOnDelete();
            $table->primary(['cluster_id', 'promo_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cluster_promo');
        Schema::dropIfExists('promos');
        Schema::dropIfExists('house_types');
        Schema::dropIfExists('clusters');
        Schema::dropIfExists('kawasans');
        Schema::dropIfExists('areas');
    }
};
