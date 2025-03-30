<?php

declare(strict_types=1);

namespace Rodrigofs\FilamentMasterdetail\Tests\Resources\OrderResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Rodrigofs\FilamentMasterdetail\Tests\Resources\OrderResource;

final class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
