<?php

namespace Database\Factories;

use App\Models\Kawasan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Kawasan>
 */
class KawasanFactory extends Factory
{
    public function definition(): array
    {
        $name = 'Kawasan '.fake()->unique()->word();

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'summary' => fake()->sentence(),
            'is_published' => true,
            'published_at' => now()->subDay(),
        ];
    }
}
