<?php

declare(strict_types=1);

namespace App\Filament\My\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Illuminate\Contracts\Support\Htmlable;

class Login extends BaseLogin
{
    protected static string $layout = 'filament-panels::components.layout.base';

    protected string $view = 'filament.my.auth.login';

    public function getTitle(): string | Htmlable
    {
        return 'Parent Portal Login';
    }
}

