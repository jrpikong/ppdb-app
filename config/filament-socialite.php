<?php

return [

    /*
    |--------------------------------------------------------------------------
    | OAuth callback middleware
    |--------------------------------------------------------------------------
    |
    | This option defines the middleware that is applied to the OAuth callback url.
    |
    */

    'middleware' => [
        \Illuminate\Cookie\Middleware\EncryptCookies::class,
        \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\Session\Middleware\AuthenticateSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | My panel social auth controls
    |--------------------------------------------------------------------------
    |
    | Keep these values env-driven so Google login can be disabled instantly
    | without code changes (rollback safety).
    |
    */
    'my_panel' => [
        'enabled' => (bool) env('FILAMENT_SOCIALITE_MY_PANEL_ENABLED', false),
        'registration_enabled' => (bool) env('FILAMENT_SOCIALITE_MY_PANEL_REGISTRATION_ENABLED', false),
        'domain_allow_list' => array_values(array_filter(array_map(
            static fn (string $domain): string => strtolower(trim($domain)),
            explode(',', (string) env('FILAMENT_SOCIALITE_MY_PANEL_DOMAIN_ALLOW_LIST', ''))
        ))),
    ],
];
