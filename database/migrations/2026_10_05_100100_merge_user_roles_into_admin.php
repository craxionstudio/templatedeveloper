<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Role disederhanakan jadi satu: Admin. Semua user lama (Super Admin, Admin Konten, Marketing)
 * dipindahkan ke role Admin dan mendapat akses ke semua menu.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')->update(['role' => 'admin']);

        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 30)->default('admin')->change();
        });
    }

    public function down(): void
    {
        // Role lama per user tidak bisa dipulihkan; semua dikembalikan ke Super Admin supaya tetap bisa login.
        DB::table('users')->update(['role' => 'super_admin']);

        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 30)->default('admin_konten')->change();
        });
    }
};
