<?php

declare(strict_types=1);

namespace App\Filament\My\Pages;

use App\Filament\My\Widgets\MyApplicationStatsWidget;
use App\Filament\My\Widgets\MyPriorityActionsWidget;
use App\Filament\My\Widgets\MyWelcomeWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function getWidgets(): array
    {
        return [
            MyWelcomeWidget::class,
            MyPriorityActionsWidget::class,
            MyApplicationStatsWidget::class,
        ];
    }

    public function getColumns(): int | array
    {
        return [
            'sm' => 1,
            'lg' => 2,
        ];
    }
}
