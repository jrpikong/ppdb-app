<?php

declare(strict_types=1);

namespace App\Filament\School\Widgets;

use App\Filament\School\Resources\Applications\ApplicationResource;
use App\Filament\School\Resources\Enrollments\EnrollmentResource;
use App\Filament\School\Resources\Levels\LevelResource;
use App\Filament\School\Resources\Payments\PaymentResource;
use App\Filament\School\Resources\Schedules\ScheduleResource;
use App\Models\Application;
use App\Models\Document;
use App\Models\Enrollment;
use App\Models\Level;
use App\Models\Payment;
use App\Models\Schedule;
use Filament\Facades\Filament;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Builder;

class DashboardOverviewWidget extends Widget
{
    protected string $view = 'filament.school.widgets.dashboard-overview';

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 8;

    protected static bool $isLazy = false;

    /**
     * @return array<int, array{name: string, icon: string, color: string, features: array<int, array{name: string, description: string, url: string, resource: string}>}>
     */
    public function getCategories(): array
    {
        return array_filter([
            $this->enrollmentProgressCategory(),
            $this->upcomingSchedulesCategory(),
            $this->actionItemsCategory(),
        ]);
    }

    /**
     * @return array{name: string, icon: string, color: string, features: list<array{name: string, description: string, url: string, resource: string}>}
     */
    protected function enrollmentProgressCategory(): array
    {
        $schoolId = Filament::getTenant()?->id;

        $levels = Level::where('school_id', $schoolId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(function (Level $level) use ($schoolId) {
                $enrolled = Enrollment::whereHas('application', function (Builder $q) use ($schoolId, $level) {
                    $q->where('school_id', $schoolId)
                        ->where('level_id', $level->id);
                })
                    ->whereIn('status', ['enrolled', 'active'])
                    ->count();

                $quota = $level->quota;
                $percentage = $quota > 0 ? round(($enrolled / $quota) * 100, 1) : 0;

                $statusText = match (true) {
                    $percentage >= 100  => '⚠️ Full - No slots available',
                    $percentage >= 90   => max(0, $quota - $enrolled) . ' slots remaining (almost full)',
                    default             => max(0, $quota - $enrolled) . ' slots available',
                };

                return [
                    'name'        => $level->code . ' — ' . $level->name,
                    'description' => $statusText . ' • ' . $enrolled . '/' . $quota . ' enrolled (' . $percentage . '%)',
                    'url'         => EnrollmentResource::getUrl('index', ['tenant' => Filament::getTenant()]),
                    'resource'    => $percentage >= 90 ? '⚠️ Almost Full' : '✓ Available',
                    'percentage'  => $percentage,
                ];
            })
            ->toArray();

        return [
            'name' => 'Enrollment Progress',
            'icon' => 'heroicon-o-academic-cap',
            'color' => 'blue',
            'features' => $levels,
        ];
    }

    /**
     * @return array{name: string, icon: string, color: string, features: list<array{name: string, description: string, url: string, resource: string}>}
     */
    protected function upcomingSchedulesCategory(): array
    {
        $schedules = Schedule::query()
            ->whereHas('application', fn (Builder $q) =>
            $q->where('school_id', Filament::getTenant()?->id)
            )
            ->whereIn('status', ['scheduled', 'confirmed'])
            ->where('scheduled_date', '>=', today())
            ->orderBy('scheduled_date')
            ->orderBy('scheduled_time')
            ->limit(8)
            ->with(['application', 'interviewer'])
            ->get()
            ->map(function (Schedule $schedule) {
                $studentName = trim(
                    ($schedule->application?->student_first_name ?? '') . ' ' .
                    ($schedule->application?->student_last_name ?? '')
                );

                $typeLabel = match ($schedule->type) {
                    'observation' => '👀 Observation',
                    'test'        => '📝 Assessment',
                    'interview'   => '🗣️ Interview',
                    default       => ucfirst($schedule->type),
                };

                $dateTime = $schedule->scheduled_date->format('d M Y') . ' at ' .
                    \Carbon\Carbon::parse($schedule->scheduled_time)->format('H:i');

                $todayBadge = $schedule->scheduled_date->isToday() ? ' • 🔥 Today' : '';

                return [
                    'name'        => $typeLabel . ' — ' . $studentName,
                    'description' => $dateTime . $todayBadge,
                    'url'         => ScheduleResource::getUrl('view', [
                        'tenant' => Filament::getTenant(),
                        'record' => $schedule,
                    ]),
                    'resource'    => $schedule->interviewer?->name ?? 'Unassigned',
                ];
            })
            ->toArray();

        return [
            'name' => 'Upcoming Schedules',
            'icon' => 'heroicon-o-calendar',
            'color' => 'amber',
            'features' => $schedules,
        ];
    }

    /**
     * @return array{name: string, icon: string, color: string, features: list<array{name: string, description: string, url: string, resource: string}>}
     */
    protected function actionItemsCategory(): array
    {
        $schoolId = Filament::getTenant()?->id;

        // Applications awaiting review
        $pendingApps = Application::where('school_id', $schoolId)
            ->whereIn('status', ['submitted', 'under_review'])
            ->count();

        // Documents pending verification
        $pendingDocs = Document::whereHas('application', function ($query) use ($schoolId) {
            $query->where('school_id', $schoolId);
        })
            ->where('status', 'pending')
            ->count();

        // Payments awaiting verification
        $pendingPayments = Payment::whereHas('application', function ($query) use ($schoolId) {
            $query->where('school_id', $schoolId);
        })
            ->where('status', 'submitted')
            ->count();

        // Applications missing assignment
        $unassigned = Application::where('school_id', $schoolId)
            ->whereIn('status', ['submitted', 'under_review'])
            ->whereNull('assigned_to')
            ->count();

        // Accepted awaiting enrollment
        $awaitingEnrollment = Application::where('school_id', $schoolId)
            ->where('status', 'accepted')
            ->whereDoesntHave('enrollment')
            ->count();

        return [
            'name' => 'Action Items',
            'icon' => 'heroicon-o-clipboard-document-check',
            'color' => 'rose',
            'features' => array_values(array_filter([
                $pendingApps > 0 ? [
                    'name'        => 'Applications Awaiting Review',
                    'description' => $pendingApps . ' applications need review and decision',
                    'url'         => ApplicationResource::getUrl('index', ['tenant' => Filament::getTenant()]),
                    'resource'    => '⚠️ ' . $pendingApps . ' pending',
                ] : null,
                $pendingDocs > 0 ? [
                    'name'        => 'Documents Pending Verification',
                    'description' => $pendingDocs . ' documents waiting for verification',
                    'url'         => '',//DocumentResource::getUrl('index', ['tenant' => Filament::getTenant()]),
                    'resource'    => '⚠️ ' . $pendingDocs . ' pending',
                ] : null,
                $pendingPayments > 0 ? [
                    'name'        => 'Payments Awaiting Verification',
                    'description' => $pendingPayments . ' payment proofs need verification',
                    'url'         => PaymentResource::getUrl('index', ['tenant' => Filament::getTenant()]),
                    'resource'    => '⚠️ ' . $pendingPayments . ' pending',
                ] : null,
                $unassigned > 0 ? [
                    'name'        => 'Unassigned Applications',
                    'description' => $unassigned . ' applications need staff assignment',
                    'url'         => ApplicationResource::getUrl('index', ['tenant' => Filament::getTenant()]),
                    'resource'    => '⚠️ ' . $unassigned . ' pending',
                ] : null,
                $awaitingEnrollment > 0 ? [
                    'name'        => 'Awaiting Enrollment',
                    'description' => $awaitingEnrollment . ' accepted students not yet enrolled',
                    'url'         => EnrollmentResource::getUrl('index', ['tenant' => Filament::getTenant()]),
                    'resource'    => '⚠️ ' . $awaitingEnrollment . ' pending',
                ] : null,
            ])),
        ];
    }
}
