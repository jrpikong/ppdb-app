<?php

declare(strict_types=1);

namespace App\Filament\School\Resources\MedicalRecords;

use App\Filament\School\Resources\MedicalRecords\Schemas\MedicalRecordForm;
use App\Filament\School\Resources\MedicalRecords\Schemas\MedicalRecordInfolist;
use App\Filament\School\Resources\MedicalRecords\Tables\MedicalRecordsTable;
use App\Models\MedicalRecord;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MedicalRecordResource extends Resource
{
    protected static ?string $model = MedicalRecord::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHeart;

    protected static bool $isScopedToTenant = false;
    protected static string|null|\UnitEnum $navigationGroup = 'Students';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'Medical Record';

    protected static ?string $pluralModelLabel = 'Medical Records';

    protected static ?string $recordTitleAttribute = 'application.student_first_name';

    // ✅ Tenant scoping via application relationship
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class])
            ->whereHas('application', fn(Builder $q) => $q->where('school_id', Filament::getTenant()?->id)
            )
            ->with(['application', 'application.user'])
            ->latest();
    }

    public static function form(Schema $schema): Schema
    {
        return MedicalRecordForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MedicalRecordInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MedicalRecordsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMedicalRecords::route('/'),
            'create' => Pages\CreateMedicalRecord::route('/create'),
            'view' => Pages\ViewMedicalRecord::route('/{record}'),
            'edit' => Pages\EditMedicalRecord::route('/{record}/edit'),
        ];
    }

    // Badge: jumlah records with special needs/allergies
    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::whereHas('application', fn(Builder $q) => $q->where('school_id', Filament::getTenant()?->id)
        )
            ->where(function ($query) {
                $query->where('has_food_allergies', true)
                    ->orWhere('has_medical_conditions', true)
                    ->orWhere('has_special_needs', true);
            })
            ->count();

        return $count > 0 ? (string)$count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Students with allergies, medical conditions, or special needs';
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}
