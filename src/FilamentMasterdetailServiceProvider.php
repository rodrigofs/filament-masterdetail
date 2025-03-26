<?php

declare(strict_types=1);

namespace Rodrigofs\FilamentMasterdetail;

use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class FilamentMasterdetailServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package->name('filament-masterdetail')
            ->hasAssets()
            ->hasTranslations()
            ->hasViews();
    }

    public function packageBooted(): void
    {
        FilamentAsset::register([
            Css::make('filament-masterdetail', __DIR__ . '/../dist/filament-masterdetail.css'),

        ], 'rodrigofs/filament-masterdetail');
    }
}
