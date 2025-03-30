<?php

namespace Rodrigofs\FilamentMasterdetail\Tests\Models;

use Illuminate\Database\Eloquent\Model;

class Products extends Model
{
    protected $fillable = [
        'name',
        'description',
        'price',
    ];
}
