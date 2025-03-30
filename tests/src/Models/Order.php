<?php

declare(strict_types=1);

namespace Rodrigofs\FilamentMasterdetail\Tests\Models;

use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Rodrigofs\FilamentMasterdetail\Tests\datababe\factories\OrderFactory;

#[UseFactory(OrderFactory::class)]
final class Order extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $fillable = [
        'customer_name',
    ];

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
