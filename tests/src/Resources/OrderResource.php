<?php

declare(strict_types=1);

namespace Rodrigofs\FilamentMasterdetail\Tests\Resources;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\{BulkActionGroup, CreateAction, DeleteBulkAction, EditAction};
use Rodrigofs\FilamentMasterdetail\Tests\Resources\OrderResource\Pages\{CreateOrder, EditOrder, ListOrders};
use Filament\Tables\Table;
use Rodrigofs\FilamentMasterdetail\Components\{DataColumn, Masterdetail};
use Rodrigofs\FilamentMasterdetail\Tests\Models\{Order};

final class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('customer_name')
                    ->label('Name')
                    ->required(),

                Masterdetail::make('items')
                    ->relationship()
                    ->addActionLabel('Add Item')
                    ->modalHeading('Add Item')
                    ->modalDescription('Add a new item to the order.')
                    ->modalIcon('heroicon-o-plus')
                    ->modalWidth('lg')
                    ->schema([
                        TextInput::make('product_id')
                            ->label('Product ID'),
                        TextInput::make('quantity')
                            ->label('Quantity'),
                        TextInput::make('price')
                            ->label('Price'),
                    ])
                    ->table([
                        DataColumn::make('product.name')
                            ->label('Product')
                            ->columnWidth('w-1/3'),

                        DataColumn::make('quantity')
                            ->label('Quantity')
                            ->columnWidth('w-1/3'),

                        DataColumn::make('price')
                            ->label('Price')
                            ->columnWidth('w-1/3'),
                    ]),
            ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrders::route('/'),
            'create' => CreateOrder::route('/create'),
            'edit' => EditOrder::route('/{record}/edit'),
        ];
    }
}
