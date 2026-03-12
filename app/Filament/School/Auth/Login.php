<?php

declare(strict_types=1);

namespace App\Filament\School\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Illuminate\Contracts\Support\Htmlable;

class Login extends BaseLogin
{
    protected static string $layout = 'filament-panels::components.layout.base';

    protected string $view = 'filament.school.auth.login';

    public function getTitle(): string | Htmlable
    {
        return 'Staff Portal Login';
    }
}
