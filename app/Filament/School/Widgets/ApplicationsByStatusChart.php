<?php

declare(strict_types=1);

namespace App\Filament\School\Widgets;

use App\Models\Application;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;

class ApplicationsByStatusChart extends ChartWidget
{
    protected ?string $heading = 'Applications by Status';

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 1;

    protected ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $schoolId = Filament::getTenant()?->id;

        $statuses = Application::where('school_id', $schoolId)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $statusLabels = [
            'draft'          => 'Draft',
            'submitted'      => 'Submitted',
            'under_review'   => 'Under Review',
            'accepted'       => 'Accepted',
            'rejected'       => 'Rejected',
            'waitlist'       => 'Waitlist',
            'enrolled'       => 'Enrolled',
            'withdrawn'      => 'Withdrawn',
        ];

        $statusColors = [
            'draft'          => '#9CA3AF', // gray
            'submitted'      => '#3B82F6', // blue
            'under_review'   => '#F59E0B', // amber
            'accepted'       => '#10B981', // green
            'rejected'       => '#EF4444', // red
            'waitlist'       => '#F59E0B', // amber
            'enrolled'       => '#8B5CF6', // purple
            'withdrawn'      => '#6B7280', // gray
        ];

        $labels = [];
        $data = [];
        $colors = [];

        foreach ($statuses as $status => $count) {
            if ($count > 0) {
                $labels[] = $statusLabels[$status] ?? ucfirst($status);
                $data[] = $count;
                $colors[] = $statusColors[$status] ?? '#9CA3AF';
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Applications',
                    'data' => $data,
                    'backgroundColor' => $colors,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
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
            'maintainAspectRatio' => false,
        ];
    }
}
