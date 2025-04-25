<?php

declare(strict_types=1);

namespace Rodrigofs\FilamentMasterdetail\Tests\Fixtures;

use Filament\Forms\Form;
use Illuminate\Contracts\View\View;
use Rodrigofs\FilamentMasterdetail\Components\Masterdetail;
use Rodrigofs\FilamentMasterdetail\Tests\Fixtures\Livewire as LivewireFixtures;
final class TestComponentWithForm extends LivewireFixtures
{
    public function form(Form $form): Form
    {
        return $form->schema([
            Masterdetail::make('items'),
        ]);
    }

    public function render(): View
    {
        return view('fixtures.form');
    }
}
