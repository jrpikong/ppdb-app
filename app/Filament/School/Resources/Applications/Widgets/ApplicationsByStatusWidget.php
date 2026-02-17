<?php

declare(strict_types=1);

namespace App\Filament\School\Resources\Applications\Widgets;

use App\Models\Application;
use Filament\Widgets\ChartWidget;
use Filament\Facades\Filament;

class ApplicationsByStatusWidget extends ChartWidget
{
    protected  ?string $heading = 'Applications by Status';

    protected static ?int $sort = 2;

    protected ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $schoolId = Filament::getTenant()->id;

        $statuses = [
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

        $data = [];
        $labels = [];
        $colors = [];

        $colorMap = [
            'draft' => 'rgb(107, 114, 128)',
            'submitted' => 'rgb(59, 130, 246)',
            'under_review' => 'rgb(251, 191, 36)',
            'documents_verified' => 'rgb(251, 191, 36)',
            'interview_scheduled' => 'rgb(168, 85, 247)',
            'interview_completed' => 'rgb(168, 85, 247)',
            'payment_pending' => 'rgb(99, 102, 241)',
            'payment_verified' => 'rgb(99, 102, 241)',
            'accepted' => 'rgb(34, 197, 94)',
            'rejected' => 'rgb(239, 68, 68)',
            'waitlisted' => 'rgb(249, 115, 22)',
            'enrolled' => 'rgb(34, 197, 94)',
            'withdrawn' => 'rgb(107, 114, 128)',
        ];

        foreach ($statuses as $key => $label) {
            $count = Application::where('school_id', $schoolId)
                ->where('status', $key)
                ->count();

            if ($count > 0) {
                $data[] = $count;
                $labels[] = $label;
                $colors[] = $colorMap[$key] ?? 'rgb(107, 114, 128)';
            }
        }

        return [
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => $colors,
                    'borderColor' => 'rgb(255, 255, 255)',
                    'borderWidth' => 2,
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
                'tooltip' => [
                    'enabled' => true,
                ],
            ],
            'maintainAspectRatio' => false,
        ];
    }
}
