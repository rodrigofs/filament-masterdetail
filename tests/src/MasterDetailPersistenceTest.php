<?php

declare(strict_types=1);

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Rodrigofs\FilamentMasterdetail\Tests\Models\{Order, OrderItem, Product, Share, Sharer};
use Rodrigofs\FilamentMasterdetail\Tests\Resources\OrderResource\Pages\{CreateOrder, EditOrder};
use Rodrigofs\FilamentMasterdetail\Tests\Resources\SharerResource\Pages\{CreateSharer, EditSharer};
use Rodrigofs\FilamentMasterdetail\Tests\Fixtures\TestComponentWithForm;

use function Pest\Laravel\{assertDatabaseCount, assertDatabaseMissing};
use function Pest\Livewire\livewire;

beforeEach(function () {
    Storage::fake('public');
    $this->orderData = Order::factory()->make();
    $this->products = Product::factory()->count(2)->create();
    $this->sharerData = Sharer::factory()->make();
});

it('renders the masterdetail field', function () {
    livewire(TestComponentWithForm::class)
        ->assertFormComponentExists('items');
});

it('creates two items when adding via form actions', function () {
    $component = livewire(CreateOrder::class)
        ->fillForm(['customer_name' => $this->orderData->customer_name]);

    foreach ($this->products as $index => $product) {
        $component->callFormComponentAction('items', 'add', [
            'product_id' => $product->getKey(),
            'quantity' => $index + 1,
            'price' => $product->price,
        ]);
    }

    $component->call('create')->assertHasNoFormErrors();

    $order = Order::firstWhere('customer_name', $this->orderData->customer_name);
    expect($order->items)->toHaveCount(2);
    expect($order->items->pluck('quantity')->all())->toEqual([1, 2]);
});

it('removes an item on delete action', function () {
    $order = Order::factory()
        ->has(OrderItem::factory()->count(3), 'items')
        ->create();

    $component = livewire(EditOrder::class, ['record' => $order->getKey()])
        ->assertSuccessful();

    $initialCount = $order->items->count();
    $itemKeys = $component->get('data.items');

    $component
        ->callFormComponentAction('items', 'delete', arguments: ['item' => array_key_first($itemKeys)])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(Order::find($order->getKey())->items)->toHaveCount($initialCount - 1);
});

it('edits quantity of an existing item', function () {
    $order = Order::factory()
        ->has(OrderItem::factory()->count(1), 'items')
        ->create();

    $component = livewire(EditOrder::class, ['record' => $order->getKey()]);
    $state = $component->get('data.items');
    $itemKey = array_key_first($state);
    $newQty = 19;

    $component
        ->callFormComponentAction(
            'items',
            'edit',
            ['quantity' => $newQty] + $state[$itemKey],
            ['item' => $itemKey],
        )
        ->call('save')
        ->assertHasNoFormErrors();

    expect(OrderItem::find($state[$itemKey]['id'])->quantity)->toBe($newQty);
});

it('persists array-type characteristics correctly', function () {
    $component = livewire(CreateOrder::class)
        ->fillForm(['customer_name' => $this->orderData->customer_name]);

    $characteristics = [
        ['color' => 'red', 'size' => 'large'],
        ['color' => 'blue', 'size' => 'small'],
    ];

    foreach ($this->products as $i => $product) {
        $component->callFormComponentAction('items', 'add', [
            'product_id' => $product->getKey(),
            'quantity' => $i + 1,
            'price' => $product->price,
            'characteristics' => $characteristics[$i],
        ]);
    }

    $component->call('create')->assertHasNoFormErrors();

    $order = Order::firstWhere('customer_name', $this->orderData->customer_name);
    foreach ($order->items as $i => $item) {
        expect($item->characteristics)->toBe($characteristics[$i]);
    }
});

it('attaches shares with uploaded files', function () {
    $shares = Share::factory()->count(2)->make(['likes' => 5]);
    $files = [
        UploadedFile::fake()->create('a.pdf', 100, 'application/pdf'),
        UploadedFile::fake()->create('b.pdf', 200, 'application/pdf'),
    ];

    $component = livewire(CreateSharer::class)
        ->fillForm([
            'name' => $this->sharerData->name,
            'email' => $this->sharerData->email,
        ]);

    foreach ($shares as $i => $share) {
        $component->callFormComponentAction('shares', 'add', [
            'title' => $share->title,
            'message' => $share->message,
            'media' => $files[$i],
            'likes' => $share->likes,
        ]);
    }

    $component->call('create')->assertHasNoFormErrors();

    $sharer = Sharer::firstWhere('email', $this->sharerData->email);
    expect($sharer->shares)->toHaveCount(2);
    $sharer->shares->each(fn ($share) => Storage::disk('public')->assertExists($share->media));
});

it('updates pivot likes and file on edit', function () {
    $sharer = Sharer::factory()->create();
    $shares = Share::factory()->count(2)->create();
    $sharer->shares()->attach($shares->pluck('id')->toArray(), ['likes' => 5]);

    $newFile = UploadedFile::fake()->create('new.pdf', 300, 'application/pdf');
    $component = livewire(EditSharer::class, ['record' => $sharer->getKey()])
        ->fillForm(['name' => 'Updated Name', 'email' => $sharer->email]);

    $itemKey = array_key_first($component->get('data.shares'));

    $component
        ->callFormComponentAction('shares', 'edit', [
            'title' => 'New Title',
            'message' => 'New Message',
            'media' => $newFile,
            'likes' => 99,
        ], ['item' => $itemKey])
        ->call('save')
        ->assertHasNoFormErrors();

    $sharer->refresh();
    expect($sharer->shares->first()->pivot->likes)->toBe(99);
    Storage::disk('public')->assertExists($sharer->shares->first()->media);
});

it('removes a share item on delete action', function () {
    $sharer = Sharer::factory()->has(Share::factory()->count(2))->create();

    assertDatabaseCount('share_sharer', 2);

    $component = livewire(EditSharer::class, ['record' => $sharer->getKey()]);
    $state = $component->get('data.shares');
    $itemKey = array_key_first($state);
    $removing = $state[$itemKey]['share_id'];

    $component
        ->callFormComponentAction('shares', 'delete', arguments: ['item' => $itemKey])
        ->call('save')
        ->assertHasNoFormErrors();

    assertDatabaseCount('shares', 2);
    assertDatabaseCount('share_sharer', 1);
    assertDatabaseMissing('share_sharer', [
        'sharer_id' => $sharer->getKey(),
        'share_id' => $removing,
    ]);
});
