<?php

declare(strict_types=1);

namespace Rodrigofs\FilamentMasterdetail\Concerns;

use Closure;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Database\Eloquent\{Builder, Collection, Model};
use Illuminate\Database\Eloquent\Relations\{BelongsToMany, HasMany, HasOneOrMany, Pivot};
use Rodrigofs\FilamentMasterdetail\Components\{DataColumn, Masterdetail};
use Filament\Support\Contracts\TranslatableContentDriver;
use Illuminate\Support\Arr;
use InvalidArgumentException;

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

        $this->saveRelationshipsUsing(static function (
            Masterdetail $component,
            HasForms     $livewire,
            ?array       $state,
        ) {
            $items = collect($state ?? []);
            $relationship = $component->getRelationship();
            $existing = $component->getCachedExistingRecords();
            $keyName       = $relationship->getRelated()->getKeyName();
            $incomingIds   = $items->pluck($keyName)->filter()->all();

            $component->deleteRemovedRecords($relationship, $existing->keys()->all(), $incomingIds);
            $translatable = $livewire->makeFilamentTranslatableContentDriver();

            $items->each(function (array $itemData) use ($component, $relationship, $existing, $keyName, $translatable) {
                [$data, $pivot] = $component->splitDataAndPivot($relationship, $itemData);

                if ($id = $itemData[$keyName] ?? null) {
                    $component->updateExistingRecord($component, $existing["record-{$id}"], $data, $pivot, $translatable);
                } else {
                    $component->createNewRecord($component, $relationship, $data, $pivot, $translatable);
                }
            });
        });

        $this->dehydrated(false);

        return $this;
    }


    /**
     * @param Masterdetail $component
     * @param BelongsToMany<Model, Model, Pivot, string>|HasMany<Model, Model> $relation
     * @param array<string, mixed> $data
     * @param list<array> $pivot
     * @param TranslatableContentDriver|null $translatable
     * @return void
     */
    protected function createNewRecord(
        Masterdetail           $component,
        BelongsToMany | HasMany $relation,
        array                  $data,
        array                  $pivot,
        ?TranslatableContentDriver                $translatable,
    ): void {
        $data = $component->mutateRelationshipDataBeforeCreate($data);

        if ($data === null) {
            return;
        }

        $data = $component->removeNestedArrays($data, $component->getRelatedModel(), $relation->getRelated());

        $record = $translatable
            ? $translatable->makeRecord($component->getRelatedModel(), $data)
            : $relation->getRelated()->newInstance()->fill($data);

        $component->clearRelationAttributes($record);
        $relation->save($record);

        if ($pivot !== []) {
            $relation->updateExistingPivot($record->getKey(), $pivot);
        }
    }

    /**
     * @param Masterdetail $component
     * @param Model $record
     * @param array<string, mixed> $data
     * @param list<array> $pivot
     * @param TranslatableContentDriver|null $translatable
     * @return void
     */
    protected function updateExistingRecord(
        Masterdetail $component,
        Model $record,
        array $data,
        array $pivot,
        ?TranslatableContentDriver $translatable,
    ): void {
        $data = $component->mutateRelationshipDataBeforeSave($data, record: $record);

        if ($data === null) {
            return;
        }

        $component->clearRelationAttributes($record);
        $data = $component->removeNestedArrays($data, null, $record);

        if ($translatable) {
            $translatable->updateRecord($record, $data);
        } else {
            $record->fill($data)->save();
        }

        if ($pivot !== []) {
            $component
                ->getRelationship()
                ->updateExistingPivot($record->getKey(), $pivot);
        }
    }

    /**
     * @param BelongsToMany<Model, Model, Pivot, string>|HasMany<Model, Model> $relation
     * @param array<mixed, BelongsToMany<Model, Model, Pivot, string>|HasMany<Model, Model>> $item
     * @return list<array>
     */
    protected function splitDataAndPivot(BelongsToMany | HasMany $relation, array $item): array
    {
        if (! $relation instanceof BelongsToMany) {
            return [$item, []];
        }

        $pivotCols = $relation->getPivotColumns();
        $pivotData = Arr::only($item, $pivotCols);
        $modelData = Arr::except($item, $pivotCols);

        return [$modelData, $pivotData];
    }

    /**
     * @param BelongsToMany<Model, Model, Pivot, string>|HasMany<Model, Model> $relation
     * @param array<int, mixed> $existingHashes
     * @param array<int, string> $incomingIds
     * @return void
     */
    protected function deleteRemovedRecords(
        BelongsToMany | HasMany $relation,
        array $existingHashes,
        array $incomingIds,
    ): void {
        $toDeleteHashes = array_diff($existingHashes, array_map(fn ($id) => "record-{$id}", $incomingIds));

        if (empty($toDeleteHashes)) {
            return;
        }

        $ids = array_map(fn (string $hash) => (int) str_replace('record-', '', $hash), $toDeleteHashes);

        if ($this->isBelongsToMany()) {
            $relation->detach($ids);

            return;
        }

        $relation
            ->whereKey($ids)
            ->each(fn (Model $model) => $model->delete());
    }


    /**
     *
     * @param  array<string, mixed>      $data        Input data to filter
     * @param  string|null   $modelClass  Fully‑qualified model class name
     * @param  Model|null    $model       Existing model instance
     * @return array<string, mixed>                     Filtered data
     *
     * @throws InvalidArgumentException  If neither $modelClass nor $model is valid
     */
    public function removeNestedArrays(
        array  $data,
        ?string $modelClass = null,
        ?Model  $model      = null,
    ): array {
        $modelInstance = $this->resolveModelInstance($modelClass, $model);

        $casts = $modelInstance->getCasts();

        return array_filter(
            $data,
            function ($value, string $key) use ($casts): bool {
                if (! is_array($value)) {
                    return true;
                }

                return array_key_exists($key, $casts);
            },
            ARRAY_FILTER_USE_BOTH
        );
    }

    /**
     * @param  string|null $modelClass
     * @param  Model|null  $model
     * @return Model
     *
     * @throws InvalidArgumentException
     */
    private function resolveModelInstance(
        ?string $modelClass,
        ?Model  $model,
    ): Model {
        if ($model instanceof Model) {
            return $model;
        }

        if (empty($modelClass)) {
            throw new InvalidArgumentException('Either $model or $modelClass must be provided.');
        }

        if (! class_exists($modelClass)) {
            throw new InvalidArgumentException("Model class '{$modelClass}' does not exist.");
        }

        if (! is_subclass_of($modelClass, Model::class)) {
            throw new InvalidArgumentException("Provided class '{$modelClass}' is not an Eloquent model.");
        }

        return new $modelClass();
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
     * @param Collection<string ,Model> $records
     * @return array<array<string, mixed>>
     */
    protected function getStateFromRelatedRecords(Collection $records): array
    {
        if (!$records->count()) {
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
     * @param array<array<string, mixed>> $data
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
     * @param array<string, mixed> $data
     * @return array<string, mixed> | null
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
     * @param array<array<string, mixed>> $data
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

    public function mutateRelationshipDataBeforeSaveUsing(?Closure $callback): static
    {
        $this->mutateRelationshipDataBeforeSaveUsing = $callback;

        return $this;
    }

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
        $keyName = $relationship->getRelated()->getKeyName();
        $relationshipName = $this->getRelationshipName();

        if (
            $this->getModelInstance()->relationLoaded($relationshipName) && (!$this->modifyRelationshipQueryUsing)

        ) {
            return $this->cachedExistingRecords = $this
                ->getRecord()
                ->getRelationValue($relationshipName)
                ->mapWithKeys(
                    fn (Model $item): array => ["record-{$item[$keyName]}" => $item],
                );
        }

        /** @var Builder<Model> $query */
        $query = $relationship->getQuery();

        $fields = $this->getTableFields();

        // TODO: check implementation feasibility to optimize relationship loading, bringing only the key field and the label field.
        $eagerLoads = collect($fields)
            ->map(function (DataColumn $f) {
                if (!$f->getRelationship() && !$f->getRelationshipName()) {
                    return null;
                }

                return $f->getRelationship() ?: $f->getRelationshipName();
            })
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (!empty($eagerLoads)) {
            $query->with($eagerLoads);
        }

        if ($relationship instanceof BelongsToMany) {
            $query->select([
                "{$relationship->getTable()}.*",
                "{$query->getModel()->getTable()}.*",
            ]);
        }

        if ($this->modifyRelationshipQueryUsing) {
            $query = $this->evaluate($this->modifyRelationshipQueryUsing, [
                'query' => $query,
            ]) ?? $query;
        }

        return $this->cachedExistingRecords = $query
            ->get()
            ->mapWithKeys(
                function (Model $item) use ($keyName): array {
                    foreach ($item->getRelations() as $relationName => $relation) {
                        $item->setAttribute($relationName, $relation->toArray());
                    }

                    return ["record-$item[$keyName]" => $item];
                },
            );
    }

    protected function clearRelationAttributes(Model $model): void
    {
        foreach ($model->getRelations() as $relationName => $_) {
            $model->unsetRelation($relationName);
            unset($model[$relationName]);
        }
    }

    /** @phpstan-ignore-next-line */
    public function getRelationship(): HasOneOrMany | BelongsToMany | null
    {
        if (!$this->hasRelationship()) {
            return null;
        }

        return $this->getModelInstance()->{$this->getRelationshipName()}();
    }

    public function isBelongsToMany(): bool
    {
        return $this->getRelationship() instanceof BelongsToMany;
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
