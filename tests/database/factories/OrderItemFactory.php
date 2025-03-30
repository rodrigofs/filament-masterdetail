<?php

declare(strict_types=1);

namespace Rodrigofs\FilamentMasterdetail\Tests\datababe\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Rodrigofs\FilamentMasterdetail\Tests\Models\{OrderItem, Product};

final class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'quantity' => fake()->numberBetween(1, 10),
            'price' => fake()->randomFloat(2, 1, 100)
        ];
    }
}
