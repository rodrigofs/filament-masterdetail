<?php

declare(strict_types=1);

use Illuminate\Support\HtmlString;
use Rodrigofs\FilamentMasterdetail\Components\DataColumn;

describe('DataColumn API', function () {
    it('applies default properties and mutators', function () {
        $component = DataColumn::make('relation.field')
            ->relationship()
            ->label('Add')
            ->columnWidth('sm')
            ->visible()
            ->state('foo');

        expect($component->getLabel())->toBe('Add')
            ->and($component->getColumnWidth())->toBe('sm')
            ->and($component->isHidden())->toBeFalse()
            ->and($component->getRelationshipName())->toBe('relation')
            ->and($component->isVisible())->toBeTrue();

        $component = DataColumn::make('foo')
            ->visible()->hidden();

        expect($component->isHidden())->toBeTrue()
            ->and($component->isVisible())->toBeFalse();
    });

    it('injects state into evaluated closures', function () {
        $component = DataColumn::make('name')->state('Rodrigo');
        $result = $component->evaluate(fn (string $state) => "Olá, $state");
        expect($result)->toBe('Olá, Rodrigo');
    });

    it('injects rowLoop into evaluated closures', function () {
        $component = DataColumn::make('name');
        $rowLoop = new stdClass();
        $rowLoop->teste = 'bar';
        $component->rowLoop($rowLoop);

        $result = $component->evaluate(fn ($rowLoop) => $rowLoop->teste);
        expect($result)->toBe('bar');
    });

    it('formats various states correctly', function () {
        // Money
        expect(
            DataColumn::make('amount')
                ->money(currency: 'USD', locale: 'en-US')
                ->formatState(123456)
        )->toBe('$123,456.00')
            ->and(
                DataColumn::make('date')
                    ->date('d/m/Y')
                    ->formatState('2025-04-23')
            )->toBe('23/04/2025')
            ->and(
                DataColumn::make('ts')
                    ->date('d M Y H:i:s')
                    ->formatState('2025-04-23 15:30')
            )->toBe('23 Apr 2025 15:30:00')
            ->and(
                DataColumn::make('amount')
                    ->numeric()
                    ->formatState(123456)
            )->toBe('123,456')
            ->and(
                DataColumn::make('time')
                    ->time('H:i')
                    ->formatState('15:30:00')
            )->toBe('15:30')
            ->and(
                DataColumn::make('time')
                    ->timezone('UTC')
                    ->formatState('2025-04-23 15:30:00')
            )->toBe('2025-04-23 15:30:00')
            ->and(
                DataColumn::make('description')
                    ->limit(10)
                    ->formatState('Lorem ipsum dolor sit amet')
            )->toBe('Lorem ipsu...')
            ->and(
                DataColumn::make('description')
                    ->words(3)
                    ->formatState('Lorem ipsum dolor sit amet')
            )->toBe('Lorem ipsum dolor...')
            ->and(
                DataColumn::make('description')
                    ->html()
                    ->formatState('Lorem ipsum dolor sit amet')
            )->toBeInstanceOf(HtmlString::class)
            ->and(
                DataColumn::make('description')
                    ->markdown()
                    ->formatState('Lorem ipsum dolor sit amet')
            )->toBeInstanceOf(HtmlString::class);
    });
});
