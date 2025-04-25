<?php

declare(strict_types=1);

namespace Rodrigofs\FilamentMasterdetail\Tests\Resources;

use Rodrigofs\FilamentMasterdetail\Components\{DataColumn, Masterdetail};
use Filament\Forms\Components\{FileUpload, Textarea, TextInput};
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Rodrigofs\FilamentMasterdetail\Tests\Models\Sharer;

final class SharerResource extends Resource
{
    protected static ?string $model = Sharer::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->label('User'),

                TextInput::make('email')
                    ->unique(ignoreRecord: true)
                    ->email(),

                Masterdetail::make('shares')
                    ->relationship('shares')
                    ->label('Shares')
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->label('Title'),

                        Textarea::make('message')
                            ->label('Message'),

                        FileUpload::make('media')
                            ->required(),

                        TextInput::make('likes')
                            ->label('Likes')
                            ->numeric(),
                    ])->table([
                        DataColumn::make('title')
                            ->label('Title'),
                        DataColumn::make('message')
                            ->label('Message'),
                        DataColumn::make('media')
                            ->label('Media'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => \Rodrigofs\FilamentMasterdetail\Tests\Resources\SharerResource\Pages\ListSharers::route('/'),
            'create' => \Rodrigofs\FilamentMasterdetail\Tests\Resources\SharerResource\Pages\CreateSharer::route('/create'),
            'edit' => \Rodrigofs\FilamentMasterdetail\Tests\Resources\SharerResource\Pages\EditSharer::route('/{record}/edit'),
        ];
    }
}
