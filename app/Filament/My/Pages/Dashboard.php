<?php

namespace App\Filament\My\Pages;

use App\Filament\My\Widgets\MyApplicationStatsWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function getWidgets(): array
    {
        return [
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
