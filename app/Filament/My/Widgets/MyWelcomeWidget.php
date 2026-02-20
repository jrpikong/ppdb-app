<?php

declare(strict_types=1);

namespace App\Filament\My\Widgets;

use App\Filament\My\Resources\Applications\ApplicationResource;
use App\Models\Application;
use Filament\Widgets\Widget;

class MyWelcomeWidget extends Widget
{
    protected string $view = 'filament.my.widgets.my-welcome-widget';
    protected static bool   $isLazy = false;
    protected int | string | array $columnSpan = 'full';

    public string $userName   = '';
    public string $greeting   = '';
    public int    $draftCount = 0;
    public bool   $hasAnyApp  = false;
    public string $createUrl  = '';
    public string $listUrl    = '';

    public function mount(): void
    {
        $user = auth()->user();

        $this->userName  = $user?->name ?? 'Parent';
        $this->greeting  = $this->buildGreeting();
        $this->createUrl = ApplicationResource::getUrl('create');
        $this->listUrl   = ApplicationResource::getUrl('index');

        if ($user) {
            $this->draftCount = Application::query()
                ->where('user_id', $user->id)
                ->where('status', 'draft')
                ->count();
            $this->hasAnyApp  = Application::query()
                ->where('user_id', $user->id)
                ->exists();
        }
    }

    private function buildGreeting(): string
    {
        $hour = (int) now()->format('H');

        return match (true) {
            $hour >= 5  && $hour < 12 => 'Good morning',
            $hour >= 12 && $hour < 17 => 'Good afternoon',
            $hour >= 17 && $hour < 21 => 'Good evening',
            default                   => 'Welcome back',
        };
    }
}
