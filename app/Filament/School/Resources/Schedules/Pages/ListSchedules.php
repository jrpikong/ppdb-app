<?php

declare(strict_types=1);

namespace App\Filament\School\Resources\Schedules\Pages;

use App\Filament\School\Resources\Schedules\ScheduleResource;
use App\Models\Schedule;
use Filament\Actions\CreateAction;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListSchedules extends ListRecords
{
    protected static string $resource = ScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('New Schedule')->icon('heroicon-o-plus'),
        ];
    }

    // Fix: override getAllTableSummaryQuery
    public function getAllTableSummaryQuery(): Builder
    {
        return ScheduleResource::getEloquentQuery();
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All'),

            'upcoming' => Tab::make('Upcoming')
                ->badge(fn (): int => Schedule::query()
                    ->whereHas('application', fn (Builder $q) =>
                    $q->withoutGlobalScopes()
                        ->where('school_id', Filament::getTenant()?->id)
                    )
                    ->whereIn('status', ['scheduled', 'confirmed'])
                    ->whereDate('scheduled_date', '>=', today())
                    ->count()
                )
                ->badgeColor('info')
                // ✅ pakai $query bukan $q — hindari naming conflict dengan closure badge
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->whereIn('schedules.status', ['scheduled', 'confirmed'])
                    ->whereDate('schedules.scheduled_date', '>=', today())
                ),

            'today' => Tab::make('Today')
                ->badge(fn (): int => Schedule::query()
                    ->whereHas('application', fn (Builder $q) =>
                    $q->withoutGlobalScopes()
                        ->where('school_id', Filament::getTenant()?->id)
                    )
                    ->whereDate('scheduled_date', today())
                    ->count()
                )
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->whereDate('schedules.scheduled_date', today())
                ),

            'completed' => Tab::make('Completed')
                ->badge(fn (): int => Schedule::query()
                    ->whereHas('application', fn (Builder $q) =>
                    $q->withoutGlobalScopes()
                        ->where('school_id', Filament::getTenant()?->id)
                    )
                    ->where('status', 'completed')
                    ->count()
                )
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->where('schedules.status', 'completed')
                ),

            'cancelled' => Tab::make('Cancelled / No Show')
                ->badge(fn (): int => Schedule::query()
                    ->whereHas('application', fn (Builder $q) =>
                    $q->withoutGlobalScopes()
                        ->where('school_id', Filament::getTenant()?->id)
                    )
                    ->whereIn('status', ['cancelled', 'no_show'])
                    ->count()
                )
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->whereIn('schedules.status', ['cancelled', 'no_show'])
                ),
        ];
    }
}
