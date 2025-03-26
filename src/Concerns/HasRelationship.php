<?php

declare(strict_types=1);

namespace Rodrigofs\FilamentMasterdetail\Concerns;

use Closure;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Rodrigofs\FilamentMasterdetail\Components\Masterdetail;

trait HasRelationship
{
    protected string | Closure | null $relationship = null;

    /**
     * @var Collection<string ,Model> | null
     */
    protected ?Collection $cachedExistingRecords = null;

    /**
     * @var array<array<string, mixed>> | null
     */
    protected ?array $hydratedDefaultState = null;

    protected bool $hasHydratedState = false;

    /**
     * @var array<int|mixed>
     */
    protected array $childRelated = [];

    protected ?Closure $mutateRelationshipDataBeforeCreateUsing = null;

    protected ?Closure $mutateRelationshipDataBeforeFillUsing = null;

    protected ?Closure $mutateRelationshipDataBeforeSaveUsing = null;

    private ?Closure $modifyRelationshipQueryUsing = null;

    private bool $shouldMergeHydratedDefaultStateWithChildComponentContainerStateAfterStateHydrated = true;

    public function relationship(string | Closure | null $name = null, ?Closure $modifyQueryUsing = null): static
    {
        $this->relationship = $name ?? $this->getName();

        $this->modifyRelationshipQueryUsing = $modifyQueryUsing;

        $this->loadStateFromRelationshipsUsing(static function (Masterdetail $component) {
            $component->clearCachedExistingRecords();

            $component->fillFromRelationship();
        });

        $this->saveRelationshipsUsing(static function (Masterdetail $component, HasForms $livewire, ?array $state) {
            if (! is_array($state)) {
                $state = [];
            }

            $relationship = $component->getRelationship();

            $existingRecords = $component->getCachedExistingRecords();

            $recordsToDelete = [];

            foreach ($existingRecords->pluck($relationship->getRelated()->getKeyName()) as $keyToCheckForDeletion) {
                if (array_key_exists("record-{$keyToCheckForDeletion}", $state)) {
                    continue;
                }

                $recordsToDelete[] = $keyToCheckForDeletion;
            }

            $relationship
                ->whereKey($recordsToDelete)
                ->get()
                ->each(static fn (Model $record) => $record->delete());

            $translatableContentDriver = $livewire->makeFilamentTranslatableContentDriver();

            foreach ($state as $itemKey => $item) {
                $itemData = $item;

                if ($record = ($existingRecords[$itemKey] ?? null)) {
                    $itemData = $component->mutateRelationshipDataBeforeSave($itemData, record: $record);

                    if ($itemData === null) {
                        continue;
                    }

                    $translatableContentDriver ?
                        $translatableContentDriver->updateRecord($record, $itemData) :
                        $record->fill($itemData)->save();

                    continue;
                }

                $relatedModel = $component->getRelatedModel();

                $itemData = $component->mutateRelationshipDataBeforeCreate($itemData);

                if ($itemData === null) {
                    continue;
                }

                if ($translatableContentDriver) {
                    $record = $translatableContentDriver->makeRecord($relatedModel, $itemData);
                } else {
                    $record = new $relatedModel;
                    $record->fill($itemData);
                }

                $record = $relationship->save($record);
                // $item->model($record)->saveRelationships();
            }

        });

        $this->dehydrated(false);

        return $this;
    }

    public function clearCachedExistingRecords(): void
    {
        $this->cachedExistingRecords = null;
    }

    public function fillFromRelationship(): void
    {
        $this->state(
            $this->getStateFromRelatedRecords($this->getCachedExistingRecords()),
        );
    }

    /**
     * @param  Collection<string ,Model>  $records
     * @return array<array<string, mixed>>
     */
    protected function getStateFromRelatedRecords(Collection $records): array
    {
        if (! $records->count()) {
            return [];
        }

        $translatableContentDriver = $this->getLivewire()->makeFilamentTranslatableContentDriver();

        return $records
            ->map(function (Model $record) use ($translatableContentDriver): array {
                $data = $translatableContentDriver ?
                    $translatableContentDriver->getRecordAttributesToArray($record) :
                    $record->attributesToArray();

                return $this->mutateRelationshipDataBeforeFill($data);
            })
            ->toArray();
    }

    /**
     * @param  array<array<string, mixed>>  $data
     * @return array<array<string, mixed>>
     */
    public function mutateRelationshipDataBeforeFill(array $data): array
    {
        if ($this->mutateRelationshipDataBeforeFillUsing instanceof Closure) {
            $data = $this->evaluate($this->mutateRelationshipDataBeforeFillUsing, [
                'data' => $data,
            ]);
        }

        return $data;
    }

    public function mutateRelationshipDataBeforeFillUsing(?Closure $callback): static
    {
        $this->mutateRelationshipDataBeforeFillUsing = $callback;

        return $this;
    }

    /**
     * @param  array<array<string, mixed>>  $data
     * @return array<array<string, mixed>> | null
     */
    public function mutateRelationshipDataBeforeCreate(array $data): ?array
    {
        if ($this->mutateRelationshipDataBeforeCreateUsing instanceof Closure) {
            $data = $this->evaluate($this->mutateRelationshipDataBeforeCreateUsing, [
                'data' => $data,
            ]);
        }

        return $data;
    }

    public function mutateRelationshipDataBeforeCreateUsing(?Closure $callback): static
    {
        $this->mutateRelationshipDataBeforeCreateUsing = $callback;

        return $this;
    }

    /**
     * @param  array<array<string, mixed>>  $data
     * @return array<array<string, mixed>> | null
     */
    public function mutateRelationshipDataBeforeSave(array $data, Model $record): ?array
    {
        if ($this->mutateRelationshipDataBeforeSaveUsing instanceof Closure) {
            $data = $this->evaluate(
                $this->mutateRelationshipDataBeforeSaveUsing,
                namedInjections: [
                    'data' => $data,
                    'record' => $record,
                ],
                typedInjections: [
                    Model::class => $record,
                    $record::class => $record,
                ],
            );
        }

        return $data;
    }

    //    public function mutateRelationshipDataBeforeSaveUsing(?Closure $callback): static
    //    {
    //        $this->mutateRelationshipDataBeforeSaveUsing = $callback;
    //
    //        return $this;
    //    }

    protected function mergeHydratedDefaultStateWithChildComponentContainerState(): void
    {
        $state = $this->getState();
        $items = $this->hydratedDefaultState;

        foreach ($items as $itemKey => $itemData) {
            $items[$itemKey] = [
                ...$state[$itemKey] ?? [],
                ...$itemData,
            ];
        }

        $this->state($items);
    }

    /**
     * @return Collection<string ,Model>
     */
    public function getCachedExistingRecords(): Collection
    {
        if ($this->cachedExistingRecords) {
            return $this->cachedExistingRecords;
        }

        $relationship = $this->getRelationship();
        $relatedKeyName = $relationship->getRelated()->getKeyName();

        $relationshipName = $this->getRelationshipName();

        if (
            $this->getModelInstance()->relationLoaded($relationshipName) &&
            (! $this->modifyRelationshipQueryUsing)

        ) {
            return $this->cachedExistingRecords = $this->getRecord()->getRelationValue($relationshipName)
                ->mapWithKeys(
                    fn (Model $item): array => ["record-{$item[$relatedKeyName]}" => $item],
                );
        }

        /** @var Builder<Model> $relationshipQuery */
        $relationshipQuery = $relationship->getQuery();

        foreach ($this->getTableFields() as $field) {
            if (is_null($field->getRelationship())) {
                $this->childRelated[] = $field->getRelationship();
            }
        }

        if (count($this->childRelated) > 0) {
            $relationshipQuery->with($this->childRelated);
        }

        if ($relationship instanceof BelongsToMany) {
            $relationshipQuery->select([
                $relationship->getTable() . '.*',
                $relationshipQuery->getModel()->getTable() . '.*',
            ]);
        }

        if ($this->modifyRelationshipQueryUsing) {
            $relationshipQuery = $this->evaluate($this->modifyRelationshipQueryUsing, [
                'query' => $relationshipQuery,
            ]) ?? $relationshipQuery;
        }

        return $this->cachedExistingRecords = $relationshipQuery->get()->mapWithKeys(
            function (Model $item) use ($relatedKeyName): array {
                foreach ($item->getRelations() as $relationName => $relation) {
                    $item->setAttribute($relationName, $relation->toArray());
                }

                return ["record-{$item[$relatedKeyName]}" => $item];
            },
        );
    }

    /** @phpstan-ignore-next-line  */
    public function getRelationship(): HasOneOrMany | BelongsToMany | null
    {
        if (! $this->hasRelationship()) {
            return null;
        }

        return $this->getModelInstance()->{$this->getRelationshipName()}();
    }

    public function hasRelationship(): bool
    {
        return filled($this->getRelationshipName());
    }

    public function getRelationshipName(): ?string
    {
        return $this->evaluate($this->relationship);
    }

    public function getRelatedModel(): string
    {
        return $this->getRelationship()->getModel()::class;
    }
}
