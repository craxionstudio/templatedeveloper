<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Data dummy (teks dalam [...] wajib diganti). Isi awal settings per halaman
     * dibuat oleh migrasi di database/settings (sumbernya database/settings/defaults).
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            ProfileSeeder::class,
            PropertySeeder::class,
            ContentSeeder::class,
            ArticleSeeder::class,
        ]);
    }
}
