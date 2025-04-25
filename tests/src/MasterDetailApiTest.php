<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsToMany, HasMany};
use Rodrigofs\FilamentMasterdetail\Components\Masterdetail;

describe('Masterdetail API', function () {
    it('applies default properties and mutators', function () {
        $component = Masterdetail::make('foo')
            ->label('Add')
            ->editActionLabel('Edit')
            ->heading('Heading')
            ->modalHeading('Modal')
            ->modalPersistent()
            ->id('foo')
            ->withoutHeader(false)
            ->breakPoint('sm')
            ->slideOver();

        expect($component->getAddActionLabel())->toBe('Add')
            ->and($component->getEditActionLabel())->toBe('Edit')
            ->and($component->getHeading())->toBe('Heading')
            ->and($component->isModalPersistent())->toBeTrue()
            ->and($component->getId())->toBe('foo')
            ->and($component->getBreakPoint())->toBe('sm')
            ->and($component->shouldHideHeader())->toBeFalse()
            ->and($component->isModalSlideOver())->toBeTrue();
    });

    it('computes relationship name when not explicitly set', function () {
        expect(
            Masterdetail::make('relation')
                ->relationship()
                ->getRelationshipName()
        )->toBe('relation');
    });

    it('accepts an override for relationship name', function () {
        expect(
            Masterdetail::make('x')
                ->relationship('y')
                ->getRelationshipName()
        )->toBe('y');
    });

    it('applies the deleteAction callback to the delete action', function () {
        $component = Masterdetail::make('items')
            ->deleteAction(fn ($action) => $action->label('Custom Delete'));

        $action = $component->getDeleteAction();

        expect($action->getLabel())->toBe('Custom Delete');
    });

    it('retains the original delete action when the callback returns null', function () {
        $component = Masterdetail::make('items')
            ->deleteAction(fn ($action) => null);

        $original = Masterdetail::make('items')->getDeleteAction();
        $modified = $component->getDeleteAction();

        expect($modified->getLabel())->toBe($original->getLabel());
    });

    it('removeNestedArrays filters out nested arrays except for cast fields', function () {
        $comp = new DummyComponent();
        $data = [
            'keep' => 'foo',
            'nested' => ['a' => 1],
        ];

        $filtered = $comp->removeNestedArrays($data, DummyModel::class);

        expect($filtered)->toBe(['keep' => 'foo']);
    });

    it('splitDataAndPivot returns only model data for HasMany relations', function () {
        $comp = new DummyComponent();
        $relation = Mockery::mock(HasMany::class);
        $input = ['column1' => 1, 'column2' => 2];

        $method = (new ReflectionClass($comp))
            ->getMethod('splitDataAndPivot');
        $method->setAccessible(true);

        [$data, $pivot] = $method->invoke($comp, $relation, $input);

        expect($data)->toBe($input);
        expect($pivot)->toBe([]);
    });

    it('splitDataAndPivot correctly separates data and pivot for BelongsToMany relations', function () {
        $comp = new DummyComponent();
        $relation = Mockery::mock(BelongsToMany::class);
        $relation->shouldReceive('getPivotColumns')->andReturn(['pivot_column']);
        $input = ['column1' => 1, 'pivot_column' => 9, 'column2' => 2];

        $method = (new ReflectionClass($comp))
            ->getMethod('splitDataAndPivot');
        $method->setAccessible(true);

        [$data, $pivot] = $method->invoke($comp, $relation, $input);

        expect($data)->toBe(['column1' => 1, 'column2' => 2]);
        expect($pivot)->toBe(['pivot_column' => 9]);
    });

    it('mutateRelationshipDataBeforeFillUsing applies the callback correctly', function () {
        $comp = new DummyComponent();
        $comp->mutateRelationshipDataBeforeFillUsing(fn (array $payload) => [['ok' => true]]);

        $result = $comp->mutateRelationshipDataBeforeFill([['x' => 1]]);

        expect($result)->toBe([['ok' => true]]);
    });

    it('mutateRelationshipDataBeforeCreateUsing applies the callback correctly', function () {
        $comp = new DummyComponent();
        $comp->mutateRelationshipDataBeforeCreateUsing(fn (array $d) => ['y' => 2]);

        $out = $comp->mutateRelationshipDataBeforeCreate(['foo' => 'bar']);

        expect($out)->toBe(['y' => 2]);
    });

    it('resolveModelInstance accepts a Model instance or classname', function () {
        $comp = new DummyComponent();
        $model = new DummyModel();

        $method = (new ReflectionClass($comp))->getMethod('resolveModelInstance');
        $method->setAccessible(true);

        $resolved = $method->invoke($comp, null, $model);
        expect($resolved)->toBe($model);

        $modelName = $model::class;
        $resolved = $method->invoke($comp, $modelName, null);
        expect($resolved)->toBeInstanceOf($modelName);
    });
});


final class DummyComponent
{
    use \Rodrigofs\FilamentMasterdetail\Concerns\HasRelationship;

    public array $stateData = [];
    public array $merged = [];

    public function getState(): array
    {
        return $this->stateData;
    }

    public function state(array $items): void
    {
        $this->merged = $items;
    }

    public function evaluate($callback, ...$args)
    {
        return $callback($args[0] ?? null);
    }
}

// --- Um Model fake só para testes de casts ---
final class DummyModel extends Model
{
    protected $casts = [
        'keep' => 'string',
    ];
}
