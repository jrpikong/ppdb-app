<?php

declare(strict_types=1);

namespace App\Filament\School\Widgets;

use App\Models\Application;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class ApplicationsPerMonthChart extends ChartWidget
{
    protected ?string $heading = 'Applications Trend (Last 12 Months)';

    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 2;

    protected ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $schoolId = Filament::getTenant()?->id;

        // Get last 12 months
        $months = collect();
        for ($i = 11; $i >= 0; $i--) {
            $months->push(now()->subMonths($i));
        }

        $submitted = $months->map(function (Carbon $month) use ($schoolId) {
            return Application::where('school_id', $schoolId)
                ->whereNotNull('submitted_at')
                ->whereYear('submitted_at', $month->year)
                ->whereMonth('submitted_at', $month->month)
                ->count();
        })->toArray();

        $accepted = $months->map(function (Carbon $month) use ($schoolId) {
            return Application::where('school_id', $schoolId)
                ->where('status', 'accepted')
                ->whereNotNull('decision_made_at')
                ->whereYear('decision_made_at', $month->year)
                ->whereMonth('decision_made_at', $month->month)
                ->count();
        })->toArray();

        $enrolled = $months->map(function (Carbon $month) use ($schoolId) {
            return Application::where('school_id', $schoolId)
                ->where('status', 'enrolled')
                ->whereNotNull('enrolled_at')
                ->whereYear('enrolled_at', $month->year)
                ->whereMonth('enrolled_at', $month->month)
                ->count();
        })->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Submitted',
                    'data' => $submitted,
                    'borderColor' => '#3B82F6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => true,
                ],
                [
                    'label' => 'Accepted',
                    'data' => $accepted,
                    'borderColor' => '#10B981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'fill' => true,
                ],
                [
                    'label' => 'Enrolled',
                    'data' => $enrolled,
                    'borderColor' => '#8B5CF6',
                    'backgroundColor' => 'rgba(139, 92, 246, 0.1)',
                    'fill' => true,
                ],
            ],
            'labels' => $months->map(fn (Carbon $month) => $month->format('M Y'))->toArray(),
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
                    'position' => 'top',
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
