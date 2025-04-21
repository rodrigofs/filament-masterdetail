<?php

declare(strict_types=1);

namespace Rodrigofs\FilamentMasterdetail\Tests;

use Filament\Forms\Form;
use Illuminate\Contracts\View\View;
use Rodrigofs\FilamentMasterdetail\Components\{DataColumn, Masterdetail};
use Rodrigofs\FilamentMasterdetail\Tests\Fixtures\Livewire as LivewireFixtures;
use Rodrigofs\FilamentMasterdetail\Tests\Models\{Order, OrderItem, Product};
use Rodrigofs\FilamentMasterdetail\Tests\Resources\OrderResource\Pages\{CreateOrder, EditOrder};

use function Pest\Livewire\livewire;

describe('render component test', function () {

    it('has masterdetail field', function () {
        livewire(TestComponentWithForm::class)
            ->assertFormComponentExists('items');
    });
});

describe('persist component test', function () {

    it('can create record master detail', function () {

        $order = Order::factory()->make();

        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        livewire(CreateOrder::class)
            ->fillForm([
                'customer_name' => $order->customer_name,
            ])
            ->callFormComponentAction(component: 'items', name: 'add', data: [
                'product_id' => $product1->getKey(),
                'quantity' => 2,
                'price' => $product1->price,
            ])
            ->callFormComponentAction(component: 'items', name: 'add', data: [
                'product_id' => $product2->getKey(),
                'quantity' => 3,
                'price' => $product2->price,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $storedPage = Order::query()->where('customer_name', $order->customer_name)->first();

        $this->assertDatabaseHas(Order::class, [
            'id' => $storedPage->getKey(),
            'customer_name' => $order->customer_name,
        ]);

        $this->assertDatabaseCount('order_items', 2);

        $this->assertDatabaseHas('order_items', [
            'order_id' => $storedPage->getKey(),
            'product_id' => $product1->getKey(),
            'quantity' => 2,
            'price' => $product1->price,
        ]);

        $this->assertDatabaseHas('order_items', [
            'order_id' => $storedPage->getKey(),
            'product_id' => $product2->getKey(),
            'quantity' => 3,
            'price' => $product2->price,
        ]);
    });

    it('can delete record master detail', function () {

        $order = Order::factory()
            ->has(OrderItem::factory()->count(3), 'items')
            ->create();

        $component = livewire(EditOrder::class, [
            'record' => $order->getKey(),
        ])->assertSuccessful();

        $this->assertDatabaseCount('order_items', 3);

        $state = $component->get('data.items');

        $component->callFormComponentAction(component: 'items', name: 'delete', arguments: [
            'item' => array_key_first($state),
        ])
            ->call('save')
            ->assertSeeText(data_get($order->items, '0.product.name'))
            ->assertSeeText(data_get($order->items, '1.product.name'))
            ->assertHasNoFormErrors();


        $this->assertDatabaseCount('order_items', 2);
    });

    it('can edit record master detail', function () {
        $order = Order::factory()
            ->has(OrderItem::factory()->count(1), 'items')
            ->create();

        $component = livewire(EditOrder::class, [
            'record' => $order->getKey(),
        ])->assertSuccessful();

        $state = $component->get('data.items');

        $itemKey = array_key_first($state);

        $component->callFormComponentAction(
            component: 'items',
            name: 'edit',
            data: [
                ...$state[$itemKey],
                'quantity' => 19,
            ],
            arguments: ['item' => $itemKey],
        )->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseCount('order_items', 1);
        $this->assertDatabaseHas('order_items', [
            'id' => $state[$itemKey]['id'],
            'quantity' => 19,
        ]);
    });
});

describe('relationship component test', function () {

    it('can set up a relationship without providing the name ', function () {
        expect(Masterdetail::make('test'))
            ->relationship()
            ->getRelationshipName()->toBe('test');
    });

    it('can set a relationship providing the name ', function () {
        expect(Masterdetail::make('test'))
            ->relationship('relationship')
            ->getRelationshipName()->toBe('relationship');
    });
});

describe('properties component test', function () {
    it('can set the name', function () {
        expect(Masterdetail::make('test'))
            ->name('test')
            ->getName()->toBe('test');
    });

    it('can set the add action label', function () {
        expect(Masterdetail::make(''))
            ->label('Add action label 1')
            ->getAddActionLabel()->toBe('Add action label 1')
            ->addActionLabel('Add action label 2')
            ->getAddActionLabel()->toBe('Add action label 2');
    });

    it('can set the edit action label', function () {
        expect(Masterdetail::make(''))
            ->editActionLabel('Edit action label 1')
            ->getEditActionLabel()->toBe('Edit action label 1');
    });

    it('can set the container heading', function () {
        expect(Masterdetail::make(''))
            ->heading('Container Title')
            ->getHeading()->toBe('Container Title');
    });

    it('can not set the container heading', function () {
        expect(Masterdetail::make(''))
            ->getHeading()->toEqual('&nbsp;');
    });

    describe('properties modal component test', function () {

        it('can set the modal heading', function () {
            expect(Masterdetail::make(''))
                ->modalHeading('Test Title Modal')
                ->getModalHeading()->toBe('Test Title Modal');
        });

        it('can modal persistent', function () {
            expect(Masterdetail::make(''))
                ->modalPersistent()
                ->isModalPersistent()->toBeTrue();
        });

        it('can set the modal width', function () {
            expect(Masterdetail::make(''))
                ->modalWidth('lg')
                ->getModalWidth()->toBe('lg');
        });

        it('can set the modal description', function () {
            expect(Masterdetail::make(''))
                ->modalDescription('Test Description')
                ->getModalDescription()->toBe('Test Description');
        });

        it('can set the modal icon', function () {
            expect(Masterdetail::make(''))
                ->modalIcon('heroicon-o-archive')
                ->getModalIcon()->toBe('heroicon-o-archive');
        });

        it('can set the modal alignment', function () {
            expect(Masterdetail::make(''))
                ->modalAlignment('left')
                ->getModalAlignment()->toBe('left');
        });

        it('can set the modal header sticky', function () {
            expect(Masterdetail::make(''))
                ->stickyModalHeader()
                ->isModalHeaderSticky()->toBeTrue();
        });

        it('can set the modal footer sticky', function () {
            expect(Masterdetail::make(''))
                ->stickyModalFooter()
                ->isModalFooterSticky()->toBeTrue();
        });

        it('can set the modal autofocus', function () {
            expect(Masterdetail::make(''))
                ->modalAutofocus()
                ->isModalAutofocused()->toBeTrue();
        });

        it('can set the modal cancel action label', function () {
            expect(Masterdetail::make(''))
                ->modalCancelActionLabel('Test Cancel Label')
                ->getModalCancelActionLabel()->toBe('Test Cancel Label');
        });

        it('can set the modal submit add action label', function () {
            expect(Masterdetail::make(''))
                ->modalSubmitActionLabel('Add action submit Label')
                ->getModalSubmitActionLabel()->toBe('Add action submit Label');
        });

        it('can set the modal submit edit action label', function () {
            expect(Masterdetail::make(''))
                ->modalSubmitEditActionLabel('Edit action submit Label')
                ->getModalSubmitEditActionLabel()->toBe('Edit action submit Label');
        });

        it('can set the modal close on click away', function () {
            expect(Masterdetail::make(''))
                ->closeModalByClickingAway()
                ->isModalClosedByClickingAway()->toBeTrue();
        });

        it('can set the modal slide over', function () {
            expect(Masterdetail::make(''))
                ->slideOver()
                ->isModalSlideOver()->toBeTrue();
        });
    });
});

describe('datacolumn component test', function () {
    it('can set label on data column', function () {
        expect(DataColumn::make(''))
            ->label('Test Label Column')
            ->getLabel()->toBe('Test Label Column');
    });

    it('can set the data column alignment', function () {
        expect(DataColumn::make(''))
            ->alignment('left')
            ->getAlignment()->toBe('left');
    });

    it('can set the data column width', function () {
        expect(DataColumn::make(''))
            ->columnWidth('lg')
            ->getColumnWidth()->toBe('lg');
    });

    it('can get relationship without set relation name', function () {
        expect(DataColumn::make('product.name'))
            ->relationship()
            ->getRelationshipName()->toBe('product');
    });

    it('can set relationship', function () {
        expect(DataColumn::make('product.name'))
            ->relationship('product')
            ->getRelationshipName()->toBe('product');
    });

    it('can format money', function () {
        $column = DataColumn::make('price');
        expect($column)
            ->money(currency: 'USD', locale: 'en-US')->formatState(2500)->toEqual('$2,500.00')
            ->and($column)
            ->money(currency: 'BRL', locale: 'pt_BR')->formatState(100)->toContain('R$')->toContain('100,00');

    });

    it('can format date', function () {
        $column = DataColumn::make('date');
        expect($column)
            ->date('d M Y')->formatState('2025-01-20')->toEqual('20 Jan 2025')
            ->and($column)
            ->date('d/m/Y')->formatState('2025-01-20')->toContain('20/01/2025');

    });

    it('can format datetime', function () {
        $column = DataColumn::make('datetime');
        expect($column)
            ->date('d M Y H:i:s')->formatState('2025-01-20 23:59')->toEqual('20 Jan 2025 23:59:00')
            ->and($column)
            ->date('d/m/Y H:i')->formatState('2025-01-20 14:30')->toContain('20/01/2025 14:30');

    });
});


final class TestComponentWithForm extends LivewireFixtures
{
    public function form(Form $form): Form
    {
        return $form->schema([
            Masterdetail::make('items')
        ]);
    }

    public function render(): View
    {
        return view('fixtures.form');
    }
}
