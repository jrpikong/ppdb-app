<?php

declare(strict_types=1);

namespace App\Filament\My\Widgets;

use App\Models\Application;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MyApplicationStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $userId = auth()->id();

        if (! $userId) {
            return [];
        }

        $baseQuery = Application::query()->where('user_id', $userId);

        $total = (clone $baseQuery)->count();
        $draft = (clone $baseQuery)->where('status', 'draft')->count();
        $active = (clone $baseQuery)->whereIn('status', [
            'submitted',
            'under_review',
            'documents_verified',
            'interview_scheduled',
            'interview_completed',
            'payment_pending',
            'payment_verified',
            'waitlisted',
        ])->count();
        $accepted = (clone $baseQuery)->where('status', 'accepted')->count();
        $enrolled = (clone $baseQuery)->where('status', 'enrolled')->count();

        return [
            Stat::make('Total Applications', $total)
                ->color('primary'),
            Stat::make('Draft', $draft)
                ->color('gray'),
            Stat::make('In Progress', $active)
                ->color('info'),
            Stat::make('Accepted', $accepted)
                ->color('success'),
            Stat::make('Enrolled', $enrolled)
                ->color('success'),
        ];
    }
}
