<?php

declare(strict_types=1);

namespace App\Filament\School\Resources\AdmissionPeriods;

use App\Filament\School\Resources\AdmissionPeriods\Pages;
use App\Filament\School\Resources\AdmissionPeriods\Schemas\AdmissionPeriodForm;
use App\Filament\School\Resources\AdmissionPeriods\Tables\AdmissionPeriodsTable;
use App\Models\AdmissionPeriod;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AdmissionPeriodResource extends Resource
{
    protected static ?string $model = AdmissionPeriod::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static string|null|\UnitEnum $navigationGroup = 'Admissions';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'Admission Period';

    protected static ?string $pluralModelLabel = 'Admission Periods';

    protected static ?string $recordTitleAttribute = 'name';

    // ── Scope ke tenant aktif ─────────────────────────────────────────────
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class])
            ->where('admission_periods.school_id', Filament::getTenant()?->id)
            ->with(['academicYear', 'school']);
    }

    public static function form(Schema $schema): Schema
    {
        return AdmissionPeriodForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AdmissionPeriodsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListAdmissionPeriods::route('/'),
            'create' => Pages\CreateAdmissionPeriod::route('/create'),
            'edit'   => Pages\EditAdmissionPeriod::route('/{record}/edit'),
        ];
    }

    // ── Navigation badge: tampilkan jumlah period yang aktif ─────────────
    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('school_id', Filament::getTenant()?->id)
            ->where('is_active', true)
            ->count();

        return $count > 0 ? (string) $count . ' active' : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}
