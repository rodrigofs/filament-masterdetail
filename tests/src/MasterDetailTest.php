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

it('has masterdetail field', function () {
    livewire(TestComponentWithForm::class)
        ->assertFormComponentExists('items');
});

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

it('can delete record detail', function () {

    $order = Order::factory()
        ->has(OrderItem::factory()->count(3), 'items')
        ->create();

    $component = livewire(EditOrder::class, [
        'record' => $order->getKey(),
    ]);

    $component->assertSuccessful();

    $state = $component->get('data.items');

    $component->callFormComponentAction(component: 'items', name: 'delete', arguments: [
        'item' => array_key_first($state),
    ])
        ->call('save')
        ->assertHasNoFormErrors();


    $this->assertDatabaseCount('order_items', 2);
});


it('can set up a relationship without providing the name ', function () {
    expect(Masterdetail::make('test'))
        ->relationship()
        ->getRelationshipName()->toBe('test');
});

it('can set a relationship providing the name ', function () {
    expect(Masterdetail::make('test'))
        ->relationship('relactionship')
        ->getRelationshipName()->toBe('relactionship');
});

it('can set the name', function () {
    expect(Masterdetail::make('test'))
        ->name('test')
        ->getName()->toBe('test');
});

it('can set the label', function () {
    expect(Masterdetail::make(''))
        ->label('Test Label1')
        ->getLabel()->toBe('Test Label1')
        ->addActionLabel('Test Label2')
        ->getAddActionLabel()->toBe('Test Label2');
});

it('can set the heading', function () {
    expect(Masterdetail::make(''))
        ->heading('Test Title')
        ->getHeading()->toBe('Test Title');
});

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

it('can set add label action', function () {
    expect(Masterdetail::make(''))
        ->addActionLabel('Test Add Label')
        ->getAddActionLabel()->toBe('Test Add Label');
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

it('can set the modal submit action label', function () {
    expect(Masterdetail::make(''))
        ->modalSubmitActionLabel('Test Submit Label')
        ->getModalSubmitActionLabel()->toBe('Test Submit Label');
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
