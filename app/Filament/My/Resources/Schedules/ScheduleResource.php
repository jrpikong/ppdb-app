<?php

declare(strict_types=1);

namespace App\Filament\My\Resources\Schedules;

use App\Filament\My\Resources\Schedules\Pages;
use App\Filament\My\Resources\Schedules\Schemas\ScheduleInfolist;
use App\Filament\My\Resources\Schedules\Tables\SchedulesTable;
use App\Models\Schedule;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ScheduleResource extends Resource
{
    protected static ?string $model = Schedule::class;

    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-calendar-days';

    protected static string|null|\UnitEnum $navigationGroup = 'Admissions';

    protected static ?string $navigationLabel = 'My Schedules';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'type';

    public static function table(Table $table): Table
    {
        return SchedulesTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ScheduleInfolist::configure($schema);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSchedules::route('/'),
            'view' => Pages\ViewSchedule::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $userId = auth()->id();

        if (! $userId) {
            return parent::getEloquentQuery()->whereRaw('1 = 0');
        }

        return parent::getEloquentQuery()
            ->whereHas('application', fn (Builder $query) => $query->where('user_id', $userId))
            ->with(['application.school', 'application.level', 'interviewer']);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        $userId = auth()->id();

        if (! $userId) {
            return null;
        }

        $count = static::getModel()::query()
            ->whereHas('application', fn (Builder $query) => $query->where('user_id', $userId))
            ->whereIn('status', ['scheduled', 'confirmed', 'rescheduled'])
            ->whereDate('scheduled_date', '>=', today())
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'info';
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        $userId = auth()->id();

        if (! $userId) {
            return parent::getRecordRouteBindingEloquentQuery()->whereRaw('1 = 0');
        }

        return parent::getRecordRouteBindingEloquentQuery()
            ->whereHas('application', fn (Builder $query) => $query->where('user_id', $userId));
    }
}

