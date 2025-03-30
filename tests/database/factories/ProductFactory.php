<?php

declare(strict_types=1);

namespace Rodrigofs\FilamentMasterdetail\Tests\datababe\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Rodrigofs\FilamentMasterdetail\Tests\Models\Product;

final class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name' => fake()->word,
            'description' => fake()->sentence,
            'price' => fake()->randomFloat(2, 1, 100)
        ];
    }
}
