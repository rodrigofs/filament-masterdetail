<?php

use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Rodrigofs\FilamentMasterdetail\Components\DataColumn;
use Rodrigofs\FilamentMasterdetail\Components\Masterdetail;
use Rodrigofs\FilamentMasterdetail\Tests\Fixtures\Livewire;

use function Pest\Livewire\livewire;

it('can fill and assert data in a MasterDetail', function (array $data) {
    $undoRepeaterFake = Masterdetail::fake();

    livewire(TestComponentWithMasterDetail::class)
        ->fillForm($data)
        ->assertFormSet($data);

    $undoRepeaterFake();
})->with([
    'normal' => fn (): array => ['normal' => [
        [
            'title' => Str::random(),
            'category' => Str::random(),
        ],
        [
            'title' => Str::random(),
            'category' => Str::random(),
        ],
        [
            'title' => Str::random(),
            'category' => Str::random(),
        ],
    ]],
]);

it('can remove items from a master MasterDetail', function () {
    $undoRepeaterFake = Masterdetail::fake();

    livewire(TestComponentWithMasterDetail::class)
        ->fillForm($data = [
            'normal' => [
                [
                    'title' => Str::random(),
                    'category' => Str::random(),
                ],
                [
                    'title' => Str::random(),
                    'category' => Str::random(),
                ],
            ],
        ])
        ->assertFormSet($data)
        ->fillForm([
            'normal' => [
                Arr::first($data['normal']),
            ],
        ])
        ->assertFormSet(function (array $data) {
            expect($data['normal'])->toHaveCount(1);

            return [
                'normal' => [
                    Arr::first($data['normal']),
                ],
            ];
        });

    $undoRepeaterFake();
});

it('can render datacolumn in MasterDetail', function () {
    $undoRepeaterFake = Masterdetail::fake();

    livewire(TestComponentWithMasterDetail::class)
        ->fillForm($data = [
            'normal' => [
                [
                    'title' => Str::random(),
                    'category' => Str::random(),
                ],
                [
                    'title' => Str::random(),
                    'category' => Str::random(),
                ],
            ],
        ])
        ->assertFormSet($data)
        ->fillForm([
            'normal' => [
                Arr::first($data['normal']),
            ],
        ])
        ->assertSeeText(['Category Column', 'Title Column']);

    $undoRepeaterFake();
});

class TestComponentWithMasterDetail extends Livewire
{
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Masterdetail::make('normal')
                    ->modalHeading('Test Title Modal')
                    ->table([
                        DataColumn::make('title')
                            ->label('Title Column'),
                        DataColumn::make('category')
                            ->label('Category Column'),
                    ])
                    ->schema([
                        TextInput::make('title'),
                        TextInput::make('category'),
                    ]),
            ])
            ->statePath('data');
    }

    public function render(): View
    {
        return view('forms.fixtures.form');
    }
}
