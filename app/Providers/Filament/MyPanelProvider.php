<?php

namespace App\Providers\Filament;

use App\Filament\My\Auth\Login;
use App\Filament\My\Auth\Register;
use App\Filament\My\Pages\Dashboard;
use App\Models\Role;
use App\Models\User;
use DutchCodingCompany\FilamentSocialite\FilamentSocialitePlugin;
use DutchCodingCompany\FilamentSocialite\Provider as SocialiteProvider;
use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Schemas\Components\Icon;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\AccountWidget;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Laravel\Socialite\Contracts\User as SocialiteUserContract;
use Spatie\Permission\PermissionRegistrar;

class MyPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $panel = $panel
            ->id('my')
            ->path('my')
            ->colors([
                'primary' => Color::Blue,
            ])
            ->login(Login::class)
            ->registration(Register::class)
            ->emailVerification()
            ->profile()
            ->globalSearch(false)
            ->passwordReset()
            ->emailVerification()
            ->databaseNotifications()
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
            ->brandLogo(asset('logo/main-logo'))
            ->brandLogoHeight('2rem')
            ->favicon(asset('site-logo-vis-150x150.png'))
            ->sidebarCollapsibleOnDesktop()
            ->navigationGroups([
                'Admissions',
                'Account',
            ]);

        if ($this->isSocialiteEnabled()) {
            $panel->plugins([
                $this->makeSocialitePlugin(),
            ]);
        }

        return $panel;
    }

    private function isSocialiteEnabled(): bool
    {
        return (bool) config('filament-socialite.my_panel.enabled', false)
            && filled(config('services.google.client_id'))
            && filled(config('services.google.client_secret'));
    }

    private function makeSocialitePlugin(): FilamentSocialitePlugin
    {
        $registrationEnabled = (bool) config('filament-socialite.my_panel.registration_enabled', false);

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
            ->domainAllowList(config('filament-socialite.my_panel.domain_allow_list', []))
            ->registration(function (string $provider, SocialiteUserContract $oauthUser, ?Authenticatable $user) use ($registrationEnabled): bool {
                if ($user instanceof User) {
                    return $user->canAccessPanel(Filament::getPanel('my'));
                }

                return $registrationEnabled;
            })
            ->authorizeUserUsing(function (FilamentSocialitePlugin $plugin, SocialiteUserContract $oauthUser): bool {
                $email = mb_strtolower(trim((string) $oauthUser->getEmail()));

                if ($email === '') {
                    return false;
                }

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

                return User::query()
                    ->whereRaw('LOWER(email) = ?', [$email])
                    ->first();
            })
            ->createUserUsing(function (string $provider, SocialiteUserContract $oauthUser, FilamentSocialitePlugin $plugin): Authenticatable {
                $email = mb_strtolower(trim((string) $oauthUser->getEmail()));

                if ($email === '') {
                    throw new \RuntimeException('Social login requires an email address.');
                }

                $name = trim((string) ($oauthUser->getName() ?: $oauthUser->getNickname() ?: Str::before($email, '@')));

                /** @var User $user */
                $user = User::query()->create([
                    'school_id' => 0,
                    'name' => $name !== '' ? $name : 'Parent User',
                    'email' => $email,
                    'password' => Hash::make(Str::random(64)),
                    'is_active' => true,
                ]);

                $user->forceFill([
                    'email_verified_at' => Carbon::now(),
                ])->save();

                $this->assignParentRole($user);

                return $user;
            });
    }

    private function assignParentRole(User $user): void
    {
        app(PermissionRegistrar::class)->setPermissionsTeamId(0);

        $parentRole = Role::query()
            ->where('name', 'parent')
            ->where('school_id', 0)
            ->where('guard_name', 'web')
            ->first();

        if (! $parentRole) {
            $parentRole = Role::create([
                'name' => 'parent',
                'school_id' => 0,
                'guard_name' => 'web',
            ]);

            report(new \RuntimeException(
                'Parent role was missing. Please run RolePermissionSeeder to assign correct permissions.'
            ));
        }

        $user->assignRole($parentRole);
    }
}
