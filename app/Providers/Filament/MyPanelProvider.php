<?php

namespace App\Providers\Filament;

use App\Filament\My\Auth\Register;
use App\Filament\My\Pages\Dashboard;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class MyPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('my')
            ->path('my')
            ->colors([
                'primary' => Color::Blue,
            ])
            ->login()
            ->registration(Register::class)
            ->emailVerification()
            ->profile()
            ->passwordReset()
            ->emailVerification()
            ->discoverResources(in: app_path('Filament/My/Resources'), for: 'App\\Filament\\My\\Resources')
            ->discoverPages(in: app_path('Filament/My/Pages'), for: 'App\\Filament\\My\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/My/Widgets'), for: 'App\\Filament\\My\\Widgets')
            ->widgets([
                AccountWidget::class,
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
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->authGuard('web')
            ->brandName('VIS My Admissions')
            ->brandLogo(asset('logo/logo.webp'))
            ->brandLogoHeight('2rem')
            ->favicon(asset('images/favicon.png'))
            ->sidebarCollapsibleOnDesktop()
            ->navigationGroups([
                'Admissions',
                'Account',
            ]);
    }
}
