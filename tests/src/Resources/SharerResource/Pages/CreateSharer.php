<?php

declare(strict_types=1);

namespace Rodrigofs\FilamentMasterdetail\Tests\Resources\SharerResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Rodrigofs\FilamentMasterdetail\Tests\Resources\SharerResource;

final class CreateSharer extends CreateRecord
{
    protected static string $resource = SharerResource::class;
}
