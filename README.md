# Filament Master Detail

<div class="filament-hidden">

![Filament Master Detail](https://raw.githubusercontent.com/rodrigofs/filament-masterdetail/main/art/rodrigofs-filament-masterdetail.png)

</div>

[![Latest Version on Packagist](https://img.shields.io/packagist/v/rodrigofs/filament-masterdetail.svg?style=flat-square)](https://packagist.org/packages/rodrigofs/filament-masterdetail)
[![PHP Run Tests](https://github.com/rodrigofs/filament-masterdetail/actions/workflows/run-tests.yml/badge.svg)](https://github.com/rodrigofs/filament-masterdetail/actions/workflows/run-tests.yml)
[![fix-code-style](https://github.com/rodrigofs/filament-masterdetail/actions/workflows/fix-php-code-styling.yml/badge.svg)](https://github.com/rodrigofs/filament-masterdetail/actions/workflows/fix-php-code-styling.yml)
[![phpstan](https://github.com/rodrigofs/filament-masterdetail/actions/workflows/phpstan.yml/badge.svg)](https://github.com/rodrigofs/filament-masterdetail/actions/workflows/phpstan.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/rodrigofs/filament-masterdetail.svg?style=flat-square)](https://packagist.org/packages/rodrigofs/filament-masterdetail)

---

## Overview

**Filament Master Detail** is a dynamic management ~~plugin~~ **Form Component** for HasMany (1,n) and Many to Many(n,n) relationships in FilamentPHP. It allows you to add and remove related records directly within the parent form, without the need to save the parent record first. Ideal for fast and fluid data entry scenarios.

---

## Table of Contents

- [Installation](#installation)
- [Basic Usage](#basic-usage)
- [Common Use Cases](#common-use-cases)
- [Additional Features](#additional-features)
    - [Behavior & Customization](#behavior--customization)
        - [Disable Actions](#disable-actions)
        - [Slideover Mode](#slideover-mode)
        - [Set Custom Labels](#set-custom-labels)
        - [Set Modal Icon and Width](#set-modal-icon-and-width)
        - [Keep Modal Open After Adding](#keep-modal-open-after-adding)
        - [Customize Table Heading](#customize-table-heading)
        - [Preserve Field Values](#preserve-field-values)
        - [Manipulate Data Before Adding](#manipulate-data-before-adding)
        - [Add Header Actions](#add-header-actions)
- [Full Example](#full-example)
- [Editing Behavior](#editing-behavior)
- [FAQ](#faq)
- [Screenshots](#screenshots)
    - [Table View](#table-view)
    - [Add New Item](#add-new-item-in-modal)
    - [Edit Item](#edit-item-in-modal)
    - [Remove Item](#remove-item-with-confirmation)
    - [Slideover Mode](#show-slideover-mode)
    - [Video Demo](#video-demo)
- [Changelog](#changelog)
- [Contributing](#contributing)
- [Security](#security-vulnerabilities)
- [License](#license)

---

## Installation

### Requirements

- PHP >= 8.1
- Laravel >= 10
- Filament >= 3.x

### Installation Steps

```bash
  composer require rodrigofs/filament-masterdetail
```

---

## Basic Usage

>   Important: When using the table(...) method, it is not compatible with Filament's TextColumn or other default columns. You must exclusively use the DataColumn provided by this package.

### Example

```php
use Rodrigofs\FilamentMasterDetail\Forms\Components\MasterDetail;
use Rodrigofs\FilamentMasterDetail\Tables\Columns\DataColumn;

MasterDetail::make('items')
    ->relationship('items')
    ->schema([
        TextInput::make('name')->required(),
        TextInput::make('description'),
    ])
    ->table([
        DataColumn::make('name'),
        DataColumn::make('description'),
    ]);
```
Define the HasMany relationship in the parent model:

```php
public function items(): HasMany
{
    return $this->hasMany(Item::class);
}
```

---

## Common Use Cases

### Order Creation with Items 

```php

use Rodrigofs\FilamentMasterDetail\Forms\Components\MasterDetail;
use Rodrigofs\FilamentMasterDetail\Tables\Columns\DataColumn;

MasterDetail::make('items')
    ->relationship()
    ->schema([
        Select::make('shop_product_id')
            ->label('Product')
            ->options(Product::query()->pluck('name', 'id'))
            ->required()
            ->reactive()
            ->afterStateUpdated(fn ($state, Set $set) => $set('price', Product::find($state)?->price ?? 0))
            ->distinct()
            ->disableOptionsWhenSelectedInSiblingRepeaterItems()
            ->columnSpan(['md' => 5])
            ->searchable(),

        TextInput::make('quantity')
            ->label('Quantity')
            ->numeric()
            ->default(1)
            ->required()
            ->columnSpan(['md' => 2]),

        TextInput::make('price')
            ->label('Unit Price')
            ->numeric()
            ->disabled()
            ->dehydrated()
            ->required()
            ->columnSpan(['md' => 3]),
    ])
    ->unique('shop_product_id')
    ->table([
        DataColumn::make('product.name')
            ->relationship()
            ->label('Product'),

        DataColumn::make('quantity')
            ->label('Quantity'),

        DataColumn::make('price')
            ->label('Unit Price'),

        DataColumn::make('total')
            ->formatStateUsing(fn ($rowLoop) => $rowLoop->price * $rowLoop->quantity)
            ->label('Total'),
    ]);
```

---

## Additional Features

### Behavior & Customization

You can customize the behavior of the component and the appearance and behavior of the modal used to add, delete, and edit related records:

#### Disable Actions

Disable the default Add, Delete, and Edit actions on the component:

```php
use Rodrigofs\FilamentMasterDetail\Components\MasterDetail;

// Disable actions statically
MasterDetail::make('items')
    ->addable(false)
    ->editable(false)
    ->removable(false)
    ->schema([
        // Form fields
    ]);

// Disable actions conditionally using closures
MasterDetail::make('items')
    ->addable(fn (): bool => /* condition based on $record, $get, $state, $operation and more... */ false)
    ->editable(fn (...): bool => /* condition based on $record, $get, $state, $operation and more... */ false)
    ->removable(fn (...): bool => /* condition based on $record, $get, $state, $operation and more... */ false)
    ->schema([
        // Form fields
    ]);
```

#### Slideover Mode

Display the form inside a Slideover instead of a traditional modal:

```php
use Rodrigofs\FilamentMasterDetail\Components\MasterDetail;

MasterDetail::make('items')
    ->slideover()
    ->schema([
        // Form fields
    ]);
```

#### Set Custom Labels

Define the labels for modal actions and headings:

```php

use Rodrigofs\FilamentMasterDetail\Forms\Components\MasterDetail;

MasterDetail::make('items')
    ->addActionLabel('Add Product')
    ->modalHeading('Add Product')
    ->modalDescription('Include a new product in this order.')
    ->modalSubmitActionLabel('Add')
    ->modalSubmitEditActionLabel('Edit')
    ->modalCancelActionLabel('Cancel');

```

#### Set Modal Icon and Width

Customize the modal icon and size:

```php

use Rodrigofs\FilamentMasterDetail\Forms\Components\MasterDetail;

MasterDetail::make('items')
    ->modalIcon('heroicon-o-plus')
    ->modalWidth('lg');

```

#### Keep Modal Open After Adding

Prevent the modal from closing automatically after adding a record:

```php

use Rodrigofs\FilamentMasterDetail\Forms\Components\MasterDetail;

MasterDetail::make('items')
    ->modalPersistent();

```

#### Customize Table Heading

Set a custom heading for the related records table:

```php

use Rodrigofs\FilamentMasterDetail\Forms\Components\MasterDetail;

MasterDetail::make('items')
    ->heading('Order Items');

```

#### Preserve Field Values

Prevent specific fields from being cleared after adding a record:

```php

use Rodrigofs\FilamentMasterDetail\Forms\Components\MasterDetail;

MasterDetail::make('items')
    ->formExceptClear(['product_id']);

```

#### Manipulate Data Before Adding

Allow data manipulation before the record is added to the table:

```php

use Rodrigofs\FilamentMasterDetail\Forms\Components\MasterDetail;

MasterDetail::make('items')
    ->beforeAddActionExecute(fn ($state, $set) => $set('product_id', $state));

```

#### Add Header Actions

Define custom actions in the header of the MasterDetail component:

```php

use Rodrigofs\FilamentMasterDetail\Forms\Components\MasterDetail;

MasterDetail::make('items')
    ->headerActions([
       Action::make('reset')
            ->modalHeading('Are you sure?')
            ->modalDescription('All existing items will be removed from the order.')
            ->requiresConfirmation()
            ->color('danger')
            ->action(fn (Forms\Set $set) => $set('items', [])),
    ]);

```

> _There are many additional features available—more than can be covered at once. While I will continue to document them, I encourage you to freely explore all possibilities. Don’t hesitate to open an issue if you encounter any problems or have suggestions._

---

## Full Example

```php

use Rodrigofs\FilamentMasterDetail\Forms\Components\MasterDetail;

MasterDetail::make('items')
    ->relationship()
    ->schema([
        Select::make('product_id')
            ->label('Product')
            ->options(Product::query()->pluck('name', 'id'))
            ->required(),
        TextInput::make('quantity')
            ->numeric()
            ->required(),
    ])
    ->table([
        DataColumn::make('product.name')
            ->relationship()
            ->label('Product'),
        DataColumn::make('quantity')
            ->label('Quantity'),
 
    ])
    ->addActionLabel('Add Product')
    ->modalHeading('Add Product')
    ->modalDescription('Include a new product in this order.')
    ->modalIcon('heroicon-o-plus')
    ->modalWidth('lg')
    ->modalSubmitActionLabel('Add')
    ->modalCancelActionLabel('Cancel')
    ->heading('Order Items')
    ->formExceptClear(['product_id'])
    ->beforeAddActionExecute(fn ($state, $set) => $set('product_id', $state))
    ->headerActions([
        Action::make('reset')
            ->modalHeading('Are you sure?')
            ->modalDescription('All existing items will be removed from the order.')
            ->requiresConfirmation()
            ->color('danger')
            ->action(fn (Forms\Set $set) => $set('items', [])),
    ])
    ->slideOver();

```

---

## Editing Behavior

The editing feature works for related records defined via the relationship() method on the component.

>**Important**:
>This feature does not support custom statePath-based implementations that do not use the relationship() method. Relationship-based binding is required for the edit action to resolve the model instance correctly.


## FAQ

1. **Do I need to save the parent record before adding related records?**
   *No. MasterDetail allows adding and removing related records before persisting the parent model.*

2. **Does it support other relationship types besides HasMany?**
  ~~*Currently, only HasMany relationships are supported.~~*
   *Yes. In addition to HasMany, it is now possible to create records using BelongsToMany (many-to-many) relationships. However, attaching existing related records is not yet supported.*

4. **Is there support for editing related records?**
   ~~*No. Only adding and removing records is supported at the moment.*~~
     *Yes. You can edit related records through an edit action.*

---

## Screenshots

### Table View

![Table View](https://raw.githubusercontent.com/rodrigofs/filament-masterdetail/main/.github/resources/table.png)

### Add New Item in Modal

![Add New Item](https://raw.githubusercontent.com/rodrigofs/filament-masterdetail/main/.github/resources/add.png)

### Edit Item in Modal

![Edit Item](https://raw.githubusercontent.com/rodrigofs/filament-masterdetail/main/.github/resources/edit.png)

### Remove Item with Confirmation

![Remove Item](https://raw.githubusercontent.com/rodrigofs/filament-masterdetail/main/.github/resources/remove.png)

### Show Slideover Mode

![Slideover Mode](https://raw.githubusercontent.com/rodrigofs/filament-masterdetail/main/.github/resources/slideover.png)

### Video Demo

[![Video Demo](https://raw.githubusercontent.com/rodrigofs/filament-masterdetail/main/.github/resources/demo.png)](https://youtu.be/ONHLSC0Znew)

---

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

---

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

---

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

---

## Credits

- [Rodrigo Fernandes](https://github.com/rodrigofs)
- [All Contributors](../../contributors)

---

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
