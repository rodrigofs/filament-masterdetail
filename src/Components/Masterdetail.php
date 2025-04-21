<?php

declare(strict_types=1);

namespace Rodrigofs\FilamentMasterdetail\Components;

use Closure;
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
use Rodrigofs\FilamentMasterdetail\Concerns\{CanDeleteAction, CanEditAction, CanAddAction, HasRelationship, HasTable};

final class Masterdetail extends Component implements HasHeaderActions
{
    use CanBeAutofocused;
    use CanOpenModal;
    use CanDeleteAction;
    use CanEditAction;
    use CanGenerateUuids;
    use CanLimitItemsLength;
    use HasDescription;
    use CanAddAction;
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

    public function label(Htmlable | Closure | string | null $label): static
    {
        return $this->addActionLabel($label);
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
            fn (self $component): Action => $component->getEditAction(),
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

    /**
     * @param array<string, mixed> $data
     * @param mixed $state
     * @param string $itemKey
     * @return array|mixed
     */
    public function refreshRelationship(array $data, mixed $state, string $itemKey): mixed
    {
        foreach ($this->tableFields as $tableField) {
            if ($tableField->getRelationship() || $tableField->getRelationshipName()) {

                $relatedName = $tableField->getRelationship() ?? $tableField->getRelationshipName();
                $related = $this->getRelationship()->getRelated()->fill($data)->{$relatedName};

                if (is_null($related)) {
                    continue;
                }

                $state[$itemKey][$relatedName] = [
                    $related->getKeyName() => $related->getKey(),
                    $tableField->getRelationshipAttribute() => $related->{$tableField->getRelationshipAttribute()},
                ];

            }
        }

        return $state;
    }
}
