<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Foto galeri cluster/kawasan: satu baris per foto (alt text wajib per foto).
        Schema::create('gallery_items', function (Blueprint $table) {
            $table->id();
            $table->morphs('galleryable');
            $table->string('alt');
            $table->string('caption')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gallery_items');
    }
};
