<?php

namespace App\Filament\School\Pages;

use App\Filament\School\Widgets;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function getWidgets(): array
    {
        return [
            Widgets\StatsOverviewWidget::class,
            Widgets\ApplicationsByStatusChart::class,
            Widgets\ApplicationsPerMonthChart::class,
            Widgets\RecentApplicationsWidget::class,
            Widgets\EnrollmentProgressWidget::class,
            Widgets\PendingVerificationsWidget::class,
            Widgets\UpcomingSchedulesWidget::class,
        ];
    }

    public function getColumns(): int | array
    {
        return [
            'sm' => 1,
            'md' => 2,
            'lg' => 3,
        ];
    }

}
