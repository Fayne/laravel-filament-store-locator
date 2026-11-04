<?php

namespace Fayne\StoreLocator\Database\Factories;

use Fayne\StoreLocator\Enums\Publishable;
use Fayne\StoreLocator\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

class StoreFactory extends Factory
{
    protected $model = Store::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->company(),
            'status' => Publishable::Published->value,
            'weight' => 0,
            'brands' => [],
            'subway_lines' => [],
            'opening_hours' => [],
        ];
    }
}
