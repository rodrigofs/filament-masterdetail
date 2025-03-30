<?php

declare(strict_types=1);

namespace Rodrigofs\FilamentMasterdetail\Tests\Models;

use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Rodrigofs\FilamentMasterdetail\Tests\datababe\factories\ProductFactory;

#[UseFactory(ProductFactory::class)]
final class Product extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $fillable = [
        'name',
        'description',
        'price',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
        ];
    }
}
