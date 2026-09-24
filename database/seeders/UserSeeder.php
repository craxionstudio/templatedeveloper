<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Akun admin awal. Ganti password setelah login pertama.
     */
    public function run(): void
    {
        $users = [
            ['name' => 'Super Admin', 'email' => 'admin@example.com', 'role' => UserRole::SuperAdmin],
            ['name' => 'Admin Konten', 'email' => 'konten@example.com', 'role' => UserRole::AdminKonten],
            ['name' => 'Marketing', 'email' => 'marketing@example.com', 'role' => UserRole::Marketing],
        ];

        foreach ($users as $user) {
            User::query()->updateOrCreate(
                ['email' => $user['email']],
                [...$user, 'password' => 'password', 'email_verified_at' => now()],
            );
        }
    }
}
