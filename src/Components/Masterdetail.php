<?php

declare(strict_types=1);

namespace Rodrigofs\FilamentMasterdetail\Components;

use Filament\Actions\Concerns\CanOpenModal;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Concerns\{CanBeAutofocused,
    CanGenerateUuids,
    CanLimitItemsLength,
    HasHeaderActions as InteractsHeaderActions};
use Filament\Forms\Components\Contracts\HasHeaderActions;
use Filament\Support\Concerns\{HasDescription, HasHeading, HasIcon, HasIconColor};
use Filament\Tables\Columns\Concerns\HasName;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\{HtmlString, Str};
use Rodrigofs\FilamentMasterdetail\Concerns\{CanDeleteAction, HasFormModal, HasRelationship, HasTable};

final class Masterdetail extends Component implements HasHeaderActions
{
    use CanBeAutofocused;
    use CanOpenModal;
    use CanDeleteAction;
    use CanGenerateUuids;
    use CanLimitItemsLength;
    use HasDescription;
    use HasFormModal;
    use HasHeading;
    use HasIcon;
    use HasIconColor;
    use HasName;
    use HasRelationship;
    use HasTable;
    use InteractsHeaderActions;

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
        $this->modalHeading($this->getLabel() ?? Str::of($this->getName())->title()->toString());
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
            fn (self $component): Action => $component->getDeleteAction(),
        ]);

        $this->headerActions = [
            $this->getAddAction(),
        ];
    }

    public function getHeading(): string | Htmlable | null
    {
        if (is_null($this->heading)) {
            $this->heading = new HtmlString('&nbsp;'); // workaround: force right alignment when title will be set to null
        }

        return $this->evaluate($this->heading);
    }
}
