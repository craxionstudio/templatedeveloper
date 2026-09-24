<?php

namespace Database\Factories;

use App\Models\Cluster;
use App\Models\HouseType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<HouseType>
 */
class HouseTypeFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->firstName();

        return [
            'cluster_id' => Cluster::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
            'lot_size' => '6×12',
            'land_area' => 72,
            'building_area' => 90,
            'bedrooms' => 3,
            'bathrooms' => 2,
            'floors' => 2,
            'carports' => 1,
            'price_from' => 1_000_000_000,
            'installment_from' => 6_200_000,
            'is_published' => true,
        ];
    }
}
