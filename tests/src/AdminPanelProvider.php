<?php

declare(strict_types=1);

namespace Rodrigofs\FilamentMasterdetail\Tests;

use Filament\{Pages, Panel, PanelProvider};
use Filament\Http\Middleware\{Authenticate, DisableBladeIconComponents, DispatchServingFilamentEvent};
use Illuminate\Cookie\Middleware\{AddQueuedCookiesToResponse, EncryptCookies};
use Illuminate\Session\Middleware\{AuthenticateSession, StartSession};
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Rodrigofs\FilamentMasterdetail\Tests\Resources\{OrderResource, SharerResource};

final class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            //->login()
            //->registration()
            //->passwordReset()
            //->emailVerification()
            ->pages([
                Pages\Dashboard::class,
            ])
            ->resources([
                OrderResource::class,
                SharerResource::class
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ]);
        //            ->authMiddleware([
        //                Authenticate::class,
        //            ]);
    }
}
