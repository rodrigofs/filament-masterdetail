<?php

declare(strict_types=1);

namespace Rodrigofs\FilamentMasterdetail\Tests\Models;

use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Rodrigofs\FilamentMasterdetail\Tests\datababe\factories\SharerFactory;

#[UseFactory(SharerFactory::class)]
final class Sharer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
    ];

    public function shares(): BelongsToMany
    {
        return $this->belongsToMany(Share::class, 'share_sharer')
            ->withPivot('likes')
            ->withTimestamps();
    }
}
