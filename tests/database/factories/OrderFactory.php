<?php

declare(strict_types=1);

namespace Rodrigofs\FilamentMasterdetail\Tests\datababe\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Rodrigofs\FilamentMasterdetail\Tests\Models\Order;

final class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'customer_name' => fake()->name
        ];
    }
}
