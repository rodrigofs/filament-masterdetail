<?php

declare(strict_types=1);

namespace Rodrigofs\FilamentMasterdetail\Tests\Resources\SharerResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Rodrigofs\FilamentMasterdetail\Tests\Resources\SharerResource;

final class ListSharers extends ListRecords
{
    protected static string $resource = SharerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
