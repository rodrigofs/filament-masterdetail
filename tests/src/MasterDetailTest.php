<?php

declare(strict_types=1);

use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Illuminate\Contracts\View\View;
use Illuminate\Support\{Arr, Carbon, Str};
use Rodrigofs\FilamentMasterdetail\Components\{DataColumn, Masterdetail};
use Rodrigofs\FilamentMasterdetail\Tests\Fixtures\Livewire;

use function Pest\Livewire\livewire;

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

it('can set the data column is sortable', function () {
    expect(DataColumn::make(''))
        ->state('stateTest')
        ->formatStateUsing(fn($state) => Str::slug($state->getState()))->toBe('state-test');
});



//it('can fill and assert data in a MasterDetail', function (array $data) {
//    $undoRepeaterFake = Masterdetail::fake();
//
//    livewire(TestComponentWithMasterDetail::class)
//        ->fillForm($data)
//        ->assertFormSet($data);
//
//    $undoRepeaterFake();
//})->with([
//    'normal' => fn (): array => ['normal' => [
//        [
//            'title' => Str::random(),
//            'category' => Str::random(),
//        ],
//        [
//            'title' => Str::random(),
//            'category' => Str::random(),
//        ],
//        [
//            'title' => Str::random(),
//            'category' => Str::random(),
//        ],
//    ]],
//]);
//
//it('can remove items from a master MasterDetail', function () {
//    $undoRepeaterFake = Masterdetail::fake();
//
//    livewire(TestComponentWithMasterDetail::class)
//        ->fillForm($data = [
//            'normal' => [
//                [
//                    'title' => Str::random(),
//                    'category' => Str::random(),
//                ],
//                [
//                    'title' => Str::random(),
//                    'category' => Str::random(),
//                ],
//            ],
//        ])
//        ->assertFormSet($data)
//        ->fillForm([
//            'normal' => [
//                Arr::first($data['normal']),
//            ],
//        ])
//        ->assertFormSet(function (array $data) {
//            expect($data['normal'])->toHaveCount(1);
//
//            return [
//                'normal' => [
//                    Arr::first($data['normal']),
//                ],
//            ];
//        });
//
//    $undoRepeaterFake();
//});
//
//it('can render datacolumn in MasterDetail', function () {
//    $undoRepeaterFake = Masterdetail::fake();
//
//    livewire(TestComponentWithMasterDetail::class)
//        ->fillForm($data = [
//            'normal' => [
//                [
//                    'title' => Str::random(),
//                    'category' => Str::random(),
//                ],
//                [
//                    'title' => Str::random(),
//                    'category' => Str::random(),
//                ],
//            ],
//        ])
//        ->assertFormSet($data)
//        ->fillForm([
//            'normal' => [
//                Arr::first($data['normal']),
//            ],
//        ])
//        ->assertSeeText(['Category Column', 'Title Column']);
//
//    $undoRepeaterFake();
//});
//
final class TestComponentWithMasterDetail extends Livewire
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
