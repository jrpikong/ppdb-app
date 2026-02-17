<?php

namespace App\Filament\School\Resources\AdmissionPeriods;

use App\Filament\School\Resources\AdmissionPeriods\Tables\AdmissionPeriodsTable;
use App\Filament\School\Resources\AdmissionPeriods\Schemas\AdmissionPeriodForm;
use App\Models\AdmissionPeriod;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class AdmissionPeriodResource extends Resource
{
    protected static ?string $model = AdmissionPeriod::class;

    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-calendar';
    protected static string|null|\UnitEnum $navigationGroup = 'Admissions';

    protected static ?int $navigationSort =2;

    public static function form(Schema $schema): Schema
    {
        return AdmissionPeriodForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AdmissionPeriodsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAdmissionPeriods::route('/'),
            'create' => Pages\CreateAdmissionPeriod::route('/create'),
            'edit' => Pages\EditAdmissionPeriod::route('/{record}/edit'),
        ];
    }
}
