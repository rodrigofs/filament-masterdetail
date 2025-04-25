<?php

declare(strict_types=1);

namespace Rodrigofs\FilamentMasterdetail\Tests\Resources\SharerResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Rodrigofs\FilamentMasterdetail\Tests\Resources\SharerResource;

final class EditSharer extends EditRecord
{
    protected static string $resource = SharerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
