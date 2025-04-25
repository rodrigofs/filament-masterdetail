<?php

declare(strict_types=1);

namespace Rodrigofs\FilamentMasterdetail\Tests\datababe\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Rodrigofs\FilamentMasterdetail\Tests\Models\Sharer;

final class SharerFactory extends Factory
{
    protected $model = Sharer::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name,
            'email' => fake()->unique()->safeEmail()
        ];
    }
}
