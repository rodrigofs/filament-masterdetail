<?php

declare(strict_types=1);

namespace Rodrigofs\FilamentMasterdetail\Tests\Resources\OrderResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Rodrigofs\FilamentMasterdetail\Tests\Resources\OrderResource;

final class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;
}
