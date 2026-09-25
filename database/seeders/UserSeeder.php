<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Akun admin awal. Lokal: password "password". Production: SEED_ADMIN_PASSWORD, atau password
     * acak yang dicetak sekali di terminal. Akun yang sudah ada tidak diubah (password tidak ter-reset
     * kalau seeder dijalankan ulang). Ganti email & password setelah login pertama.
     */
    public function run(): void
    {
        $password = app()->isProduction()
            ? (env('SEED_ADMIN_PASSWORD') ?: Str::password(16, symbols: false))
            : 'password';

        $users = [
            ['name' => 'Super Admin', 'email' => 'admin@example.com', 'role' => UserRole::SuperAdmin],
            ['name' => 'Admin Konten', 'email' => 'konten@example.com', 'role' => UserRole::AdminKonten],
            ['name' => 'Marketing', 'email' => 'marketing@example.com', 'role' => UserRole::Marketing],
        ];

        $created = [];

        foreach ($users as $user) {
            $model = User::query()->firstOrCreate(
                ['email' => $user['email']],
                [...$user, 'password' => $password, 'email_verified_at' => now()],
            );

            if ($model->wasRecentlyCreated) {
                $created[] = $user['email'];
            }
        }

        if ($created !== [] && app()->isProduction() && ! env('SEED_ADMIN_PASSWORD')) {
            $this->command?->warn('Akun admin dibuat: '.implode(', ', $created));
            $this->command?->warn("Password sementara (catat sekarang, tidak ditampilkan lagi): {$password}");
        }
    }
}
