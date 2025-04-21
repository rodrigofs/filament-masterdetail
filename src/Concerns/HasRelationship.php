<?php

declare(strict_types=1);

namespace Rodrigofs\FilamentMasterdetail\Concerns;

use Closure;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Database\Eloquent\{Builder, Collection, Model};
use Illuminate\Database\Eloquent\Relations\{BelongsToMany, HasOneOrMany};
use Rodrigofs\FilamentMasterdetail\Components\{DataColumn, Masterdetail};

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

    /**
     * @var array<int, mixed>
     */
    protected array $showFields = [];

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
            $items = is_array($state) ? $state : [];
            $relationship = $component->getRelationship();
            $relatedModel = $relationship->getRelated();
            $primaryKey = $relatedModel->getKeyName();
            $existing = $component->getCachedExistingRecords();

            $incomingIds = collect($items)
                ->pluck($primaryKey)
                ->filter()
                ->all();

            $toDelete = array_diff(
                $existing->keys()->all(),
                array_map(fn ($id) => "record-{$id}", $incomingIds),
            );

            $toDeleteIds = array_map(
                fn (string $hash) => (int)str_replace('record-', '', $hash),
                $toDelete,
            );

            if (!empty($toDeleteIds)) {
                $relationship
                    ->whereKey($toDeleteIds)
                    ->each(fn (Model $r) => $r->delete());
            }

            $translatableContentDriver = $livewire->makeFilamentTranslatableContentDriver();

            foreach ($items as $itemData) {
                $id = $itemData[$primaryKey] ?? null;
                $recordKey = $id !== null ? "record-{$id}" : null;

                if ($recordKey !== null && isset($existing[$recordKey])) {
                    $record = $existing[$recordKey];

                    $data = $component->mutateRelationshipDataBeforeSave($itemData, record: $record);
                    if ($data !== null) {
                        $component->clearRelationAttributes($record);
                        $data = $component->removeNestedArrays($data);

                        $translatableContentDriver ?
                            $translatableContentDriver->updateRecord($record, $data) :
                            $record->fill($data)->save();
                    }

                    continue;
                }

                $data = $component->mutateRelationshipDataBeforeCreate($itemData);
                if ($data === null) {
                    continue;
                }

                $data = $component->removeNestedArrays($data);
                if ($translatableContentDriver) {
                    $record = $translatableContentDriver->makeRecord($component->getRelatedModel(), $data);
                } else {
                    $record = new $relatedModel();
                    $record->fill($data);
                }

                $component->clearRelationAttributes($record);
                $relationship->save($record);
            }
        });

        $this->dehydrated(false);

        return $this;
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function removeNestedArrays(array $data): array
    {
        return array_filter($data, fn ($value) => !is_array($value));
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
     * @param array<array<string, mixed>> $data
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
        $relatedName = $relationship->getRelated()->getKeyName();
        $relationName = $this->getRelationshipName();

        if (
            $this->getModelInstance()->relationLoaded($relationName) && (!$this->modifyRelationshipQueryUsing)

        ) {
            return $this->cachedExistingRecords = $this
                ->getRecord()
                ->getRelationValue($relationName)
                ->mapWithKeys(
                    fn (Model $item): array => ["record-{$item[$relatedName]}" => $item],
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
                function (Model $item) use ($relatedName): array {
                    foreach ($item->getRelations() as $relationName => $relation) {
                        $item->setAttribute($relationName, $relation->toArray());
                    }

                    return ["record-$item[$relatedName]" => $item];
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
