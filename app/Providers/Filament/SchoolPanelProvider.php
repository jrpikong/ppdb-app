<?php

namespace App\Providers\Filament;

use App\Http\Middleware\ApplyTenantScope;
use App\Models\School;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class SchoolPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('school')
            ->path('school')
            ->login()

            // ✅ TENANCY CONFIGURATION
            ->tenant(School::class)
//            ->tenantProfile(Pages\EditProfile::class)
            ->tenantRegistration(null) // Disable self-registration
            ->tenantRoutePrefix('s') // URL: /school/s/{tenant}

            ->colors([
                'primary' => Color::Blue,
            ])
            ->discoverResources(in: app_path('Filament/School/Resources'), for: 'App\\Filament\\School\\Resources')
            ->discoverPages(in: app_path('Filament/School/Pages'), for: 'App\\Filament\\School\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/School/Widgets'), for: 'App\\Filament\\School\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
            ])
            ->authGuard('web')
            ->tenantDomain(null)
            ->middleware([
                ApplyTenantScope::class,
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
            ->plugins([
                FilamentShieldPlugin::make()
                    ->gridColumns([
                        'default' => 1,
                        'sm' => 2,
                        'lg' => 3,
                    ])
                    ->sectionColumnSpan(1)
                    ->checkboxListColumns([
                        'default' => 1,
                        'sm' => 2,
                        'lg' => 4,
                    ])
                    ->resourceCheckboxListColumns([
                        'default' => 1,
                        'sm' => 2,
                    ])
                    // ⚠️ IMPORTANT: Configure for tenant awareness
                    ->scopeToTenant(true)
                    ->tenantRelationshipName('school')    // string|Closure|null
                ,
            ])
            ->brandName('VIS School Portal')
            ->favicon(asset('images/favicon.png'))
            ->brandLogo(asset('logo/logo.webp')) // Add your logo
            ->brandLogoHeight('2rem')
            ->sidebarCollapsibleOnDesktop()
            ->navigationGroups([
                'Admissions',
                'Students',
                'Finance',
                'Settings',
                'Reports',
            ]);
    }
}
