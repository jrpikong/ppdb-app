<?php

declare(strict_types=1);

namespace App\Filament\School\Resources\Applications\Widgets;

use App\Models\Application;
use Filament\Widgets\ChartWidget;
use Filament\Facades\Filament;
use Carbon\Carbon;

class ApplicationsChartWidget extends ChartWidget
{
    protected ?string $heading = 'Applications Trend (Last 6 Months)';

    protected static ?int $sort = 3;

    protected ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $schoolId = Filament::getTenant()?->id;

        if (! $schoolId) {
            return [
                'datasets' => [
                    [
                        'label' => 'Submitted',
                        'data' => [0, 0, 0, 0, 0, 0],
                        'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                        'borderColor' => 'rgb(59, 130, 246)',
                        'borderWidth' => 2,
                        'fill' => true,
                    ],
                    [
                        'label' => 'Accepted',
                        'data' => [0, 0, 0, 0, 0, 0],
                        'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                        'borderColor' => 'rgb(34, 197, 94)',
                        'borderWidth' => 2,
                        'fill' => true,
                    ],
                    [
                        'label' => 'Rejected',
                        'data' => [0, 0, 0, 0, 0, 0],
                        'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                        'borderColor' => 'rgb(239, 68, 68)',
                        'borderWidth' => 2,
                        'fill' => true,
                    ],
                ],
                'labels' => collect(range(5, 0))
                    ->map(fn (int $i) => Carbon::now()->subMonths($i)->format('M Y'))
                    ->all(),
            ];
        }

        $months = [];
        $submitted = [];
        $accepted = [];
        $rejected = [];

        // Get last 6 months of data
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $months[] = $date->format('M Y');

            $submitted[] = Application::where('school_id', $schoolId)
                ->whereYear('submitted_at', $date->year)
                ->whereMonth('submitted_at', $date->month)
                ->count();

            $accepted[] = Application::where('school_id', $schoolId)
                ->where('status', 'accepted')
                ->whereYear('decision_made_at', $date->year)
                ->whereMonth('decision_made_at', $date->month)
                ->count();

            $rejected[] = Application::where('school_id', $schoolId)
                ->where('status', 'rejected')
                ->whereYear('decision_made_at', $date->year)
                ->whereMonth('decision_made_at', $date->month)
                ->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Submitted',
                    'data' => $submitted,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'borderWidth' => 2,
                    'fill' => true,
                ],
                [
                    'label' => 'Accepted',
                    'data' => $accepted,
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                    'borderColor' => 'rgb(34, 197, 94)',
                    'borderWidth' => 2,
                    'fill' => true,
                ],
                [
                    'label' => 'Rejected',
                    'data' => $rejected,
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                    'borderColor' => 'rgb(239, 68, 68)',
                    'borderWidth' => 2,
                    'fill' => true,
                ],
            ],
            'labels' => $months,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 1,
                    ],
                ],
            ],
            'maintainAspectRatio' => false,
        ];
    }
}
