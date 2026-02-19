<?php

declare(strict_types=1);

namespace App\Filament\School\Widgets;

use App\Models\Application;
use App\Models\Document;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\Schedule;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $schoolId = Filament::getTenant()?->id;

        // Total applications this academic year
        $totalApplications = Application::where('school_id', $schoolId)->count();

        // Pending applications (submitted, under review)
        $pendingApplications = Application::where('school_id', $schoolId)
            ->whereIn('status', ['submitted', 'under_review'])
            ->count();

        // Accepted applications this month
        $acceptedThisMonth = Application::where('school_id', $schoolId)
            ->where('status', 'accepted')
            ->whereMonth('decision_made_at', now()->month)
            ->count();

        // Active enrollments
        $activeEnrollments = Enrollment::whereHas('application', fn (Builder $q) =>
            $q->where('school_id', $schoolId)
        )->where('status', 'active')->count();

        // Pending document verifications
        $pendingDocuments = Document::query()
            ->whereHas('application',fn (Builder $q) => $q->where('school_id', $schoolId))
            ->where('status', 'pending')
            ->count();

        // Pending payment verifications
        $pendingPayments = Payment::query()
            ->whereHas('application',fn (Builder $q) => $q->where('school_id', $schoolId))
            ->where('status', 'submitted')
            ->count();

        // Upcoming schedules today
        $schedulesToday = Schedule::whereHas('application', fn (Builder $q) =>
            $q->where('school_id', $schoolId)
        )
        ->whereDate('scheduled_date', today())
        ->whereIn('status', ['scheduled', 'confirmed'])
        ->count();

        // Application conversion rate (accepted / total submitted)
        $submittedCount = Application::where('school_id', $schoolId)
            ->whereIn('status', ['submitted', 'under_review', 'accepted', 'rejected', 'waitlisted'])
            ->count();
        $acceptedCount = Application::where('school_id', $schoolId)
            ->where('status', 'accepted')
            ->count();
        $conversionRate = $submittedCount > 0
            ? round(($acceptedCount / $submittedCount) * 100, 1)
            : 0;

        return [
            // Total Applications
            Stat::make('Total Applications', $totalApplications)
                ->description('All time applications')
                ->descriptionIcon('heroicon-o-document-text')
                ->color('primary')
                ->chart([7, 12, 18, 15, 22, 28, $totalApplications])
                ->url(route('filament.school.resources.applications.index', [
                    'tenant' => Filament::getTenant(),
                ])),

            // Pending Review
            Stat::make('Pending Review', $pendingApplications)
                ->description('Awaiting review & decision')
                ->descriptionIcon('heroicon-o-clock')
                ->color($pendingApplications > 10 ? 'warning' : 'success')
                ->url(route('filament.school.resources.applications.index', [
                    'tenant' => Filament::getTenant(),
                    'tableFilters' => [
                        'status' => ['values' => ['submitted', 'under_review']],
                    ],
                ])),

            // Accepted This Month
            Stat::make('Accepted This Month', $acceptedThisMonth)
                ->description('New acceptances')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success'),

            // Active Students
            Stat::make('Active Students', $activeEnrollments)
                ->description('Currently enrolled')
                ->descriptionIcon('heroicon-o-user-group')
                ->color('info')
                ->url(route('filament.school.resources.enrollments.index', [
                    'tenant' => Filament::getTenant(),
                ])),

            // Pending Verifications
            Stat::make('Pending Verifications', $pendingDocuments + $pendingPayments)
                ->description("{$pendingDocuments} docs, {$pendingPayments} payments")
                ->descriptionIcon('heroicon-o-exclamation-triangle')
                ->color($pendingDocuments + $pendingPayments > 5 ? 'danger' : 'warning'),

            // Schedules Today
            Stat::make('Schedules Today', $schedulesToday)
                ->description('Interviews & tests today')
                ->descriptionIcon('heroicon-o-calendar')
                ->color($schedulesToday > 0 ? 'warning' : 'gray')
                ->url(route('filament.school.resources.schedules.index', [
                    'tenant' => Filament::getTenant(),
                ])),

            // Acceptance Rate
            Stat::make('Acceptance Rate', $conversionRate . '%')
                ->description('Of submitted applications')
                ->descriptionIcon('heroicon-o-chart-bar')
                ->color($conversionRate >= 50 ? 'success' : 'warning'),

            // Payment Collection Rate
            Stat::make('Payment Collection', function () use ($schoolId) {
                $totalDue = Enrollment::whereHas('application', fn (Builder $q) =>
                    $q->where('school_id', $schoolId)
                )->sum('total_amount_due');

                $totalPaid = Enrollment::whereHas('application', fn (Builder $q) =>
                    $q->where('school_id', $schoolId)
                )->sum('total_amount_paid');

                if ($totalDue <= 0) return '0%';

                return round(($totalPaid / $totalDue) * 100, 1) . '%';
            })
                ->description('Tuition collected')
                ->descriptionIcon('heroicon-o-currency-dollar')
                ->color('success'),
        ];
    }
}
