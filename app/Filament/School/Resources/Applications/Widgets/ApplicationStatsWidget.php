<?php

declare(strict_types=1);

namespace App\Filament\School\Resources\Applications\Widgets;

use App\Models\Application;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Facades\Filament;

class ApplicationStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $schoolId = Filament::getTenant()?->id;

        if (! $schoolId) {
            return [];
        }

        // Get counts by status
        $total = Application::where('school_id', $schoolId)->count();
        $submitted = Application::where('school_id', $schoolId)
            ->whereIn('status', ['submitted', 'under_review'])
            ->count();
        $accepted = Application::where('school_id', $schoolId)
            ->where('status', 'accepted')
            ->count();
        $enrolled = Application::where('school_id', $schoolId)
            ->where('status', 'enrolled')
            ->count();
        $rejected = Application::where('school_id', $schoolId)
            ->where('status', 'rejected')
            ->count();
        $pending = Application::where('school_id', $schoolId)
            ->where('status', 'draft')
            ->count();

        return [
            Stat::make('Total Applications', $total)
                ->description('All applications')
                ->descriptionIcon('heroicon-o-document-text')
                ->color('primary')
                ->chart($this->getWeeklyTrend($schoolId)),

            Stat::make('Pending Review', $submitted)
                ->description($submitted > 0 ? 'Requires attention' : 'All caught up!')
                ->descriptionIcon($submitted > 0 ? 'heroicon-o-exclamation-circle' : 'heroicon-o-check-circle')
                ->color($submitted > 10 ? 'warning' : 'info')
                ->chart($this->getWeeklyTrendByStatus($schoolId, ['submitted', 'under_review'])),

            Stat::make('Accepted', $accepted)
                ->description('Awaiting enrollment')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success')
                ->chart($this->getWeeklyTrendByStatus($schoolId, ['accepted'])),

            Stat::make('Enrolled', $enrolled)
                ->description('Successfully enrolled')
                ->descriptionIcon('heroicon-o-academic-cap')
                ->color('success')
                ->chart($this->getWeeklyTrendByStatus($schoolId, ['enrolled'])),

            Stat::make('Rejected', $rejected)
                ->description('Not accepted')
                ->descriptionIcon('heroicon-o-x-circle')
                ->color('danger')
                ->chart($this->getWeeklyTrendByStatus($schoolId, ['rejected'])),

            Stat::make('Draft', $pending)
                ->description('Incomplete applications')
                ->descriptionIcon('heroicon-o-pencil')
                ->color('gray')
                ->chart($this->getWeeklyTrendByStatus($schoolId, ['draft'])),
        ];
    }

    protected function getWeeklyTrend(int $schoolId): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $count = Application::where('school_id', $schoolId)
                ->whereDate('created_at', $date)
                ->count();
            $data[] = $count;
        }
        return $data;
    }

    protected function getWeeklyTrendByStatus(int $schoolId, array $statuses): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $count = Application::where('school_id', $schoolId)
                ->whereIn('status', $statuses)
                ->whereDate('created_at', $date)
                ->count();
            $data[] = $count;
        }
        return $data;
    }
}
