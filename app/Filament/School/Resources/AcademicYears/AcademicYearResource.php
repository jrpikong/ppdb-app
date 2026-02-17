<?php

namespace App\Filament\School\Resources\AcademicYears;

use App\Filament\School\Resources\AcademicYears\Pages\CreateAcademicYear;
use App\Filament\School\Resources\AcademicYears\Pages\EditAcademicYear;
use App\Filament\School\Resources\AcademicYears\Pages\ListAcademicYears;
use App\Filament\School\Resources\AcademicYears\Schemas\AcademicYearForm;
use App\Filament\School\Resources\AcademicYears\Tables\AcademicYearsTable;
use App\Models\AcademicYear;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AcademicYearResource extends Resource
{
    protected static ?string $model = AcademicYear::class;

    protected static string|null|BackedEnum $navigationIcon = 'heroicon-o-calendar';

    protected static string|null|\UnitEnum $navigationGroup = 'Admissions';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Academic Years';

    protected static ?string $modelLabel = 'Academic Year';

    protected static ?string $pluralModelLabel = 'Academic Years';

    protected static ?string $slug = 'academic-years';

    public static function form(Schema $schema): Schema
    {
        return AcademicYearForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AcademicYearsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAcademicYears::route('/'),
            'create' => CreateAcademicYear::route('/create'),
            'edit' => EditAcademicYear::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('is_active', true)->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}
