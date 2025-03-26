<?php

declare(strict_types=1);

namespace Rodrigofs\FilamentMasterdetail\Components;

use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Concerns\CanBeAutofocused;
use Filament\Forms\Components\Concerns\CanGenerateUuids;
use Filament\Forms\Components\Concerns\CanLimitItemsLength;
use Filament\Tables\Columns\Concerns\HasName;
use Rodrigofs\FilamentMasterdetail\Concerns\CanDeleteAction;
use Rodrigofs\FilamentMasterdetail\Concerns\HasFormModal;
use Rodrigofs\FilamentMasterdetail\Concerns\HasRelationship;
use Rodrigofs\FilamentMasterdetail\Concerns\HasTable;

final class Masterdetail extends Component
{
    use CanBeAutofocused;
    use CanDeleteAction;
    use CanGenerateUuids;
    use CanLimitItemsLength;
    use HasFormModal;
    use HasName;
    use HasRelationship;
    use HasTable;

    protected string $viewIdentifier = 'field';

    final public function __construct(string $name)
    {
        $this->defaultView(fn () => 'filament-masterdetail::components.index');
        $this->name($name);
        $this->statePath($name);
    }

    public static function make(string $name): Masterdetail
    {
        $static = app(Masterdetail::class, ['name' => $name]);
        $static->configure();

        return $static;
    }

    public function getId(): string
    {
        return parent::getId() ?? $this->getStatePath();
    }

    public function getKey(): string
    {
        return parent::getKey() ?? $this->getStatePath();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->afterStateHydrated(static function (self $component, ?array $state): void {

            if (is_array($component->hydratedDefaultState) && $component->shouldMergeHydratedDefaultStateWithChildComponentContainerStateAfterStateHydrated) {
                $component->mergeHydratedDefaultStateWithChildComponentContainerState();
            }

            if (is_array($component->hydratedDefaultState)) {
                return;
            }

            if ($component->hasHydratedState) {
                return;
            }

            $items = [];

            foreach ($state ?? [] as $itemData) {
                $items[$component->generateUuid()] = $itemData;
            }

            $component->state($items);

            $component->hasHydratedState = true;
        });

        $this->registerActions([
            fn (self $component): Action => $component->getAddAction(),
            fn (self $component): Action => $component->getDeleteAction(),
        ]);
    }
}
