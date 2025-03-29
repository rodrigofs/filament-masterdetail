<div class="filament-hidden">

![Filament Master Detail](https://raw.githubusercontent.com/rodrigofs/filament-masterdetail/main/art/rodrigofs-filament-masterdetail.png)

</div>

# Filament Master Detail

This package is a Filament plugin.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/rodrigofs/filament-masterdetail.svg?style=flat-square)](https://packagist.org/packages/rodrigofs/filament-masterdetail)
[![PHP Run Tests](https://github.com/rodrigofs/filament-masterdetail/actions/workflows/run-tests.yml/badge.svg)](https://github.com/rodrigofs/filament-masterdetail/actions/workflows/run-tests.yml)
[![fix-code-style](https://github.com/rodrigofs/filament-masterdetail/actions/workflows/fix-php-code-styling.yml/badge.svg)](https://github.com/rodrigofs/filament-masterdetail/actions/workflows/fix-php-code-styling.yml)
[![phpstan](https://github.com/rodrigofs/filament-masterdetail/actions/workflows/phpstan.yml/badge.svg)](https://github.com/rodrigofs/filament-masterdetail/actions/workflows/phpstan.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/rodrigofs/filament-masterdetail.svg?style=flat-square)](https://packagist.org/packages/rodrigofs/filament-masterdetail)

# Filament Master Details

A custom component for the [Filament Admin Panel](https://filamentphp.com), designed to facilitate the addition and removal of related records via a **HasMany (1,n)** relationship, even when the parent record has not yet been persisted in the database.

## Overview

**Filament Master Details** offers a practical and smooth solution for managing related data, especially useful in creation processes where the immediate persistence of the parent record has not occurred.

This component acts as an internal mini-form for child records, allowing them to be added or removed in real-time, storing the data in memory until the parent form is fully persisted.

## About This Documentation

This section provides an overview of the **main features** and configuration steps required to use the `Filament Master Detail` component effectively.

The documentation is **still under development**, and more detailed examples, advanced use cases, and customization guides will be added progressively.

We recommend checking this section frequently to stay updated with the latest improvements, usage patterns, and best practices related to this component.

If you have suggestions or questions, feel free to open an issue or contribute to the project.

## Features

- **Add related records** without requiring prior persistence of the main record.
- **Dynamically remove related records**.
- Integration with the Eloquent `hasMany` relationship.
- Real-time storage via Livewire for state management.

> **Important:** The component **does not support pagination nor editing of child records**, and is recommended for relationships with a moderate amount of data.

## Usage Context

Ideal for forms with:
- Quick registrations involving multiple sub-items (e.g., orders with products, classes with students).
- Processes where user experience requires **agility and low friction** during data entry.
- Situations where the parent model has not yet been saved.

## Requirements

- Laravel >= 10.x
- Filament >= 3.x
- Livewire >= 3.x

## Demo

[![dark.png](https://raw.githubusercontent.com/rodrigofs/filament-masterdetail/refs/heads/main/.github/resources/dark.png)](https://youtu.be/ONHLSC0Znew)

[![light.png](https://raw.githubusercontent.com/rodrigofs/filament-masterdetail/refs/heads/main/.github/resources/light.png)](https://youtu.be/ONHLSC0Znew)

## Installation

You can install the package via composer:

```bash
    composer require rodrigofs/filament-masterdetail
```

## Usage

To use the **Filament Master Detail** component, it is essential to configure the following three elements correctly. These are critical for the component to function as expected:

1. **Relationship**  
   Define a `hasMany` Eloquent relationship on the parent model. This relationship is used to associate and persist the detail records when the parent form is saved.

2. **Form Schema**  
   Set up the `schema` with the fields that will be used to input the detail records. These fields are rendered inside a modal and allow dynamic data entry for each child record.

3. **Table**  
   Configure a `table` to display the list of inserted detail records. Each entry shown in this table reflects the data submitted through the form.
   > ⚠️ You **must** use the `DataColumn` helper component when defining the table. It is specially designed for use within this Master Detail context and is not interchangeable with Filament’s standard `Tables\Columns\Column`.

---

## Example

```php
MasterDetail::make('items')
    ->relationship()
    ->modalPersistent()
    ->heading('Order items')
    ->modalHeading('Adding Product')
    ->headerActions([
        Action::make('reset')
            ->modalHeading('Are you sure?')
            ->modalDescription('All existing items will be removed from the order.')
            ->requiresConfirmation()
            ->color('danger')
            ->action(fn (Forms\Set $set) => $set('items', [])),
    ])
    ->schema([
        Forms\Components\Select::make('shop_product_id')
            ->label('Product')
            ->options(Product::query()->pluck('name', 'id'))
            ->required()
            ->reactive()
            ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('unit_price', Product::find($state)?->price ?? 0))
            ->distinct()
            ->disableOptionsWhenSelectedInSiblingRepeaterItems()
            ->columnSpan(['md' => 5])
            ->searchable(),

        Forms\Components\TextInput::make('qty')
            ->label('Quantity')
            ->numeric()
            ->default(1)
            ->required()
            ->columnSpan(['md' => 2]),

        Forms\Components\TextInput::make('unit_price')
            ->label('Unit Price')
            ->disabled()
            ->dehydrated()
            ->numeric()
            ->required()
            ->columnSpan(['md' => 3]),
    ])
    ->table([
        DataColumn::make('shop_product_id')
            ->label('Product')
            ->relationship('product')
            ->formatStateUsing(fn($state) => Product::find($state)?->name)
            ->columnWidth('1/3'),

        DataColumn::make('qty')
            ->label('Quantity'),

        DataColumn::make('unit_price')
            ->label('Unit Price'),
    ])
    ->columns([
        'md' => '10',
    ]);
```

### Explanation of Each Section

#### `MasterDetail::make('items')`
Binds the component to the `items` attribute, which must be a `hasMany` relationship on the parent model.

#### `->relationship()`
Activates the Eloquent relationship. Required for syncing the data between the form and the related models.

#### `->modalPersistent()`
Keeps the modal state between open/close cycles, improving UX for repeated data entry.

#### `->heading()` / `->modalHeading()`
Sets the visible title for the section and modal window, respectively.

#### `->headerActions([...])`
Adds actions to the component’s header.  
In the example, a **reset** action clears all inserted items, with confirmation.

#### `->schema([...])`
Defines the fields shown in the modal for each item:

- **Product selector** (`shop_product_id`) with dynamic `unit_price` update
- **Quantity input**
- **Unit price field** (readonly but submitted)

#### `->table([...])`
Displays the list of added items using the `DataColumn` component:

- Resolves **product name** using the `product` relationship
- Displays **quantity** and **unit price**
- > **Important:** Only `DataColumn` works inside the Master Detail component table.

#### `->columns([...])`
Sets Tailwind grid span for medium screens and above.

---

### ⚠️ Note about `DataColumn`

You **must** use the provided `DataColumn` class to define columns inside the `table()` method.  
This helper is designed to work specifically within the Master Detail structure, handling internal data binding and formatting.

> **Standard** `Filament\Tables\Columns\Column` components are **not compatible** with this context.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Rodrigo Fernandes](https://github.com/rodrigofs)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
