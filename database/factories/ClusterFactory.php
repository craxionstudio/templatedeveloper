<?php

namespace Database\Factories;

use App\Enums\ClusterStatus;
use App\Enums\PropertyType;
use App\Models\Cluster;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Cluster>
 */
class ClusterFactory extends Factory
{
    public function definition(): array
    {
        $name = 'Cluster '.fake()->unique()->word();

        return [
            'kawasan_id' => null,
            'name' => $name,
            'slug' => Str::slug($name),
            'building_type' => 'Rumah 2 lantai',
            'property_type' => PropertyType::Rumah,
            'status' => ClusterStatus::ReadyStock,
            'is_published' => true,
            'published_at' => now()->subDay(),
        ];
    }
}
