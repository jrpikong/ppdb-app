<?php
declare(strict_types=1);
namespace App\Filament\School\Resources\Enrollments\Pages;

use App\Filament\School\Resources\Enrollments\EnrollmentResource;
use App\Models\Enrollment;
use Filament\Actions\CreateAction;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListEnrollments extends ListRecords
{
    protected static string $resource = EnrollmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('New Enrollment')
                ->icon('heroicon-o-plus'),
        ];
    }

    // ✅ Override getAllTableSummaryQuery (pattern yang proven)
    public function getAllTableSummaryQuery(): Builder
    {
        return EnrollmentResource::getEloquentQuery();
    }

    public function getTabs(): array
    {
        return [
            'active' => Tab::make('Active Students')
                ->badge(fn (): int => Enrollment::query()
                    ->whereHas('application', fn (Builder $q) =>
                    $q->withoutGlobalScopes()
                        ->where('school_id', Filament::getTenant()?->id)
                    )
                    ->where('status', 'active')
                    ->count()
                )
                ->badgeColor('success')
                // ✅ $query bukan $q (hindari naming conflict)
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->where('enrollments.status', 'active')
                ),

            'enrolled' => Tab::make('Enrolled')
                ->badge(fn (): int => Enrollment::query()
                    ->whereHas('application', fn (Builder $q) =>
                    $q->withoutGlobalScopes()
                        ->where('school_id', Filament::getTenant()?->id)
                    )
                    ->where('status', 'enrolled')
                    ->count()
                )
                ->badgeColor('info')
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->where('enrollments.status', 'enrolled')
                ),

            'completed' => Tab::make('Completed')
                ->badge(fn (): int => Enrollment::query()
                    ->whereHas('application', fn (Builder $q) =>
                    $q->withoutGlobalScopes()
                        ->where('school_id', Filament::getTenant()?->id)
                    )
                    ->where('status', 'completed')
                    ->count()
                )
                ->badgeColor('primary')
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->where('enrollments.status', 'completed')
                ),

            'withdrawn' => Tab::make('Withdrawn')
                ->badge(fn (): int => Enrollment::query()
                    ->whereHas('application', fn (Builder $q) =>
                    $q->withoutGlobalScopes()
                        ->where('school_id', Filament::getTenant()?->id)
                    )
                    ->whereIn('status', ['withdrawn', 'expelled', 'transferred'])
                    ->count()
                )
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->whereIn('enrollments.status', ['withdrawn', 'expelled', 'transferred'])
                ),

            'graduated' => Tab::make('Graduated')
                ->badge(fn (): int => Enrollment::query()
                    ->whereHas('application', fn (Builder $q) =>
                    $q->withoutGlobalScopes()
                        ->where('school_id', Filament::getTenant()?->id)
                    )
                    ->where('status', 'graduated')
                    ->count()
                )
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->where('enrollments.status', 'graduated')
                ),

            'all' => Tab::make('All')
                ->badge(fn (): int => Enrollment::query()
                    ->whereHas('application', fn (Builder $q) =>
                    $q->withoutGlobalScopes()
                        ->where('school_id', Filament::getTenant()?->id)
                    )
                    ->count()
                ),
        ];
    }
}
