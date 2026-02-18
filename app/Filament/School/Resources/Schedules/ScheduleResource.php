<?php

declare(strict_types=1);

namespace App\Filament\School\Resources\Schedules;

use App\Filament\School\Resources\Schedules\Pages;
use App\Filament\School\Resources\Schedules\Schemas\ScheduleForm;
use App\Filament\School\Resources\Schedules\Schemas\ScheduleInfolist;
use App\Filament\School\Resources\Schedules\Tables\SchedulesTable;
use App\Models\Schedule;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ScheduleResource extends Resource
{
    protected static ?string $model = Schedule::class;

    protected static bool $isScopedToTenant = false;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendar;

    protected static string|null|\UnitEnum $navigationGroup = 'Admissions';

    protected static ?int $navigationSort = 4;

    protected static ?string $modelLabel = 'Schedule';

    protected static ?string $pluralModelLabel = 'Interview & Test Schedules';

    protected static ?string $recordTitleAttribute = 'type';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->join('applications', 'schedules.application_id', '=', 'applications.id')
            ->where('applications.school_id', Filament::getTenant()?->id)
            ->select('schedules.*'); // penting: hindari ambiguous column
    }

    public static function form(Schema $schema): Schema
    {
        return ScheduleForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ScheduleInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SchedulesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSchedules::route('/'),
            'create' => Pages\CreateSchedule::route('/create'),
            'view' => Pages\ViewSchedule::route('/{record}'),
            'edit' => Pages\EditSchedule::route('/{record}/edit'),
        ];
    }

    // Badge: jadwal hari ini
    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::whereHas('application', fn(Builder $q) => $q->where('school_id', Filament::getTenant()?->id)
        )
            ->whereDate('scheduled_date', today())
            ->whereIn('status', ['scheduled', 'confirmed'])
            ->count();

        return $count > 0 ? (string)$count . ' today' : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'info';
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}
