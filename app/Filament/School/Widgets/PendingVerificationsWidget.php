<?php

declare(strict_types=1);

namespace App\Filament\School\Widgets;

use App\Models\Application;
use App\Models\Document;
use App\Models\Payment;
use Filament\Facades\Filament;
use Filament\Widgets\Widget;

class PendingVerificationsWidget extends Widget
{
    protected string $view = 'filament.school.widgets.pending-verifications';

    protected static ?int $sort = 7;

    protected int | string | array $columnSpan = 1;

    protected static ?string $heading = 'Action Items';

    public function getActionItems(): array
    {
        $schoolId = Filament::getTenant()?->id;

        return [
            [
                'label'       => 'Applications Awaiting Review',
                'count'       => Application::where('school_id', $schoolId)
                    ->whereIn('status', ['submitted', 'under_review'])
                    ->count(),
                'icon'        => 'heroicon-o-document-text',
                'color'       => 'warning',
                'url'         => route('filament.school.resources.applications.index', [
                    'tenant' => Filament::getTenant(),
                    'tableFilters' => ['status' => ['values' => ['submitted', 'under_review']]],
                ]),
            ],
            [
                'label'       => 'Documents Pending Verification',
                'count'       => Document::query()
                    ->whereHas('application', function ($query) use ($schoolId) {})
                    ->where('status', 'pending')
                    ->count(),
                'icon'        => 'heroicon-o-document-check',
                'color'       => 'info',
//                'url'         => route('filament.school.resources.documents.index', [
//                    'tenant' => Filament::getTenant(),
//                    'tableFilters' => ['status' => ['value' => 'pending']],
//                ]),
            ],
            [
                'label'       => 'Payments Awaiting Verification',
                'count'       => Payment::query()
                    ->whereHas('application', function ($query) use ($schoolId) {})
                    ->where('status', 'submitted')
                    ->count(),
                'icon'        => 'heroicon-o-currency-dollar',
                'color'       => 'success',
                'url'         => route('filament.school.resources.payments.index', [
                    'tenant' => Filament::getTenant(),
                    'activeTab' => 'submitted',
                ]),
            ],
            [
                'label'       => 'Applications Missing Assignment',
                'count'       => Application::where('school_id', $schoolId)
                    ->whereIn('status', ['submitted', 'under_review'])
                    ->whereNull('assigned_to')
                    ->count(),
                'icon'        => 'heroicon-o-user-group',
                'color'       => 'danger',
                'url'         => route('filament.school.resources.applications.index', [
                    'tenant' => Filament::getTenant(),
                ]),
            ],
            [
                'label'       => 'Accepted - Awaiting Enrollment',
                'count'       => Application::where('school_id', $schoolId)
                    ->where('status', 'accepted')
                    ->whereDoesntHave('enrollment')
                    ->count(),
                'icon'        => 'heroicon-o-academic-cap',
                'color'       => 'warning',
                'url'         => route('filament.school.resources.enrollments.index', [
                    'tenant' => Filament::getTenant(),
                ]),
            ],
        ];
    }
}
