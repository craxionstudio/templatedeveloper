<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Profil developer (Home + Tentang Kami). Satu baris saja.
        Schema::create('developer_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('headline');
            $table->text('description')->nullable();
            $table->longText('history')->nullable();
            $table->string('vision_quote')->nullable();
            $table->json('stats')->nullable(); // [{value, label}]
            $table->string('photo_alt')->nullable();
            $table->string('secondary_photo_alt')->nullable();
            $table->timestamps();
        });

        Schema::create('facility_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon', 50)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('facilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facility_category_id')->nullable()->constrained()->nullOnDelete();
            // null = berlaku di semua kawasan
            $table->foreignId('kawasan_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon', 50)->nullable();
            $table->text('description')->nullable();
            $table->string('photo_alt')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_published', 'sort_order']);
        });

        Schema::create('future_developments', function (Blueprint $table) {
            $table->id();
            $table->string('target', 30); // tahun / target, mis. "2027"
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status', 30)->default('perencanaan'); // App\Enums\DevelopmentStatus
            $table->string('image_alt')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('article_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('authors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('job_title')->nullable();
            $table->text('bio')->nullable();
            $table->string('photo_alt')->nullable();
            $table->timestamps();
        });

        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('author_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('excerpt', 500)->nullable();
            $table->longText('body')->nullable(); // HTML yang sudah disanitasi
            $table->string('cover_alt')->nullable();
            $table->boolean('is_highlight')->default(false);
            $table->unsignedSmallInteger('reading_minutes')->default(1); // dihitung otomatis
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_published', 'published_at']);
        });

        Schema::create('article_tag', function (Blueprint $table) {
            $table->foreignId('article_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->primary(['article_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('article_tag');
        Schema::dropIfExists('articles');
        Schema::dropIfExists('authors');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('article_categories');
        Schema::dropIfExists('future_developments');
        Schema::dropIfExists('facilities');
        Schema::dropIfExists('facility_categories');
        Schema::dropIfExists('developer_profiles');
    }
};
