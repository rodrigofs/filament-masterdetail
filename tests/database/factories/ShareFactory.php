<?php

declare(strict_types=1);

namespace Rodrigofs\FilamentMasterdetail\Tests\datababe\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Rodrigofs\FilamentMasterdetail\Tests\Models\Share;

final class ShareFactory extends Factory
{
    protected $model = Share::class;

    public function definition(): array
    {
        return [
            'title' => fake()->text(30),
            'media' => fake()->imageUrl(),
            'message' => fake()->text(100),
        ];
    }
}
