<?php

declare(strict_types=1);

namespace Rodrigofs\FilamentMasterdetail\Tests\Models;

use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Rodrigofs\FilamentMasterdetail\Tests\datababe\factories\ShareFactory;

#[UseFactory(ShareFactory::class)]
final class Share extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'title',
        'media',
        'message',
    ];

    public function sharers(): BelongsToMany
    {
        return $this->belongsToMany(Sharer::class, 'share_sharer')
            ->withPivot('likes')
            ->withTimestamps();
    }
}
