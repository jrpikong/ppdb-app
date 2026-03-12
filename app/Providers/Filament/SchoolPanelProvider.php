<?php

namespace App\Providers\Filament;

use App\Filament\School\Auth\Login;
use App\Filament\SuperAdmin\Widgets\AccountWidget;
use App\Models\School;
use App\Models\User;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use DutchCodingCompany\FilamentSocialite\FilamentSocialitePlugin;
use DutchCodingCompany\FilamentSocialite\Provider as SocialiteProvider;
use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Laravel\Socialite\Contracts\User as SocialiteUserContract;

class SchoolPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $panel = $panel
            ->id('school')
            ->path('school')
            ->colors([
                'primary' => Color::Blue,
            ])
            ->login(Login::class)
            ->tenant(School::class,'code')
            ->tenantRegistration(null)
            ->tenantRoutePrefix('s')
            ->discoverResources(in: app_path('Filament/School/Resources'), for: 'App\\Filament\\School\\Resources')
            ->discoverPages(in: app_path('Filament/School/Pages'), for: 'App\\Filament\\School\\Pages')
            ->pages([
                \App\Filament\School\Pages\Dashboard::class, // ✅ Use Filament's built-in Dashboard
            ])
            ->discoverWidgets(in: app_path('Filament/School/Widgets'), for: 'App\\Filament\\School\\Widgets')
            ->widgets([
                AccountWidget::class,
            ])
            ->plugins([
                FilamentShieldPlugin::make()
                    // ✅ CRITICAL: Enable tenant scoping
                    ->scopeToTenant(true)

                    // ✅ Relationship name dari Role ke School
                    ->tenantOwnershipRelationshipName('school')

                    // Layout customization (optional)
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
                    ]),
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
            ->brandName('VIS School Portal')
            ->brandLogo(asset('logo/main-logo'))
            ->brandLogoHeight('2rem')
            ->favicon(asset('site-logo-vis-150x150.png'))
            ->sidebarCollapsibleOnDesktop()
            ->navigationGroups([
                'Admissions',
                'Students',
                'Finance',
                'Settings',
                'Reports',
            ]);

        // Add Socialite plugin if enabled
        if ($this->isSocialiteEnabled()) {
            $panel->plugins([
                $this->makeSocialitePlugin(),
            ]);
        }

        return $panel;
    }

    private function isSocialiteEnabled(): bool
    {
        return (bool) config('filament-socialite.school_panel.enabled', false)
            && filled(config('services.google.client_id'))
            && filled(config('services.google.client_secret'));
    }

    private function makeSocialitePlugin(): FilamentSocialitePlugin
    {
        return FilamentSocialitePlugin::make()
            ->providers([
                SocialiteProvider::make('google')
                    ->label('')
                    ->icon('fab-google')
                    ->outlined(false)
                    ->color(Color::Red)
                    ->stateless((bool) config('services.google.stateless', false))
                    ->scopes(['openid', 'profile', 'email']),
            ])
            ->rememberLogin(true)
            ->domainAllowList(config('filament-socialite.school_panel.domain_allow_list', []))
            // NO REGISTRATION - only existing users can login
            ->registration(fn() => false)
            ->authorizeUserUsing(function (FilamentSocialitePlugin $plugin, SocialiteUserContract $oauthUser): bool {
                $email = mb_strtolower(trim((string) $oauthUser->getEmail()));

                if ($email === '') {
                    return false;
                }

                // Verify email is verified in Google
                $isEmailVerified = (bool) data_get($oauthUser->getRaw(), 'verified_email', true);

                if (! $isEmailVerified) {
                    return false;
                }

                return FilamentSocialitePlugin::checkDomainAllowList($plugin, $oauthUser);
            })
            ->resolveUserUsing(function (string $provider, SocialiteUserContract $oauthUser, FilamentSocialitePlugin $plugin): ?Authenticatable {
                $email = mb_strtolower(trim((string) $oauthUser->getEmail()));

                if ($email === '') {
                    return null;
                }

                $user = User::query()
                    ->whereRaw('LOWER(email) = ?', [$email])
                    ->first();

                if (! $user) {
                    return null;
                }

                // Verify user has staff role for school panel
                if (! $user->hasAnyRole(['super_admin', 'school_admin', 'admin', 'admission_admin', 'finance_admin'])) {
                    return null;
                }

                // Verify user is active
                if (! $user->is_active) {
                    return null;
                }

                // For non-super_admin, verify tenant access
                if (! $user->hasRole('super_admin')) {
                    $tenant = Filament::getTenant();

                    if (! $tenant || $user->school_id !== $tenant->id) {
                        return null;
                    }
                }

                return $user;
            })
            ->createUserUsing(function (): Authenticatable {
                throw new \RuntimeException('Registration is disabled for staff panel. Please contact your administrator.');
            });
    }
}
