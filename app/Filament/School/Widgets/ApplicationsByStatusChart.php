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

        if (! $schoolId) {
            return [
                'datasets' => [[
                    'label' => 'Applications',
                    'data' => [],
                    'backgroundColor' => [],
                ]],
                'labels' => [],
            ];
        }

        $statuses = Application::where('school_id', $schoolId)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $statusLabels = [
            'draft' => 'Draft',
            'submitted' => 'Submitted',
            'under_review' => 'Under Review',
            'documents_verified' => 'Documents Verified',
            'interview_scheduled' => 'Interview Scheduled',
            'interview_completed' => 'Interview Completed',
            'payment_pending' => 'Payment Pending',
            'payment_verified' => 'Payment Verified',
            'accepted' => 'Accepted',
            'rejected' => 'Rejected',
            'waitlisted' => 'Waitlisted',
            'enrolled' => 'Enrolled',
            'withdrawn' => 'Withdrawn',
        ];

        $statusColors = [
            'draft' => '#9CA3AF',
            'submitted' => '#3B82F6',
            'under_review' => '#F59E0B',
            'documents_verified' => '#F59E0B',
            'interview_scheduled' => '#8B5CF6',
            'interview_completed' => '#8B5CF6',
            'payment_pending' => '#6366F1',
            'payment_verified' => '#6366F1',
            'accepted' => '#10B981',
            'rejected' => '#EF4444',
            'waitlisted' => '#FB923C',
            'enrolled' => '#10B981',
            'withdrawn' => '#6B7280',
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
