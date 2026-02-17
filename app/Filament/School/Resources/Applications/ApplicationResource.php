<?php

declare(strict_types=1);

namespace App\Filament\School\Resources\Applications;

use App\Filament\School\Resources\Applications\Pages;
use App\Filament\School\Resources\Applications\RelationManagers;
use App\Filament\School\Resources\Applications\Schemas\ApplicationForm;
use App\Filament\School\Resources\Applications\Schemas\ApplicationInfolist;
use App\Filament\School\Resources\Applications\Tables\ApplicationsTable;
use App\Models\Application;
use App\Models\User;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Filament\Support\Enums\FontWeight;

class ApplicationResource extends Resource
{
    protected static ?string $model = Application::class;

    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Applications';

    protected static string|null|\UnitEnum $navigationGroup = 'Admissions';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'application_number';

    /**
     * Scope queries to current tenant (school)
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('school_id', Filament::getTenant()->id)
            ->with([
                'user',
                'admissionPeriod',
                'level',
                'parentGuardians',
                'documents',
                'payments',
                'schedules',
            ]);
    }

    /**
     * Form schema for creating/editing applications
     */
    public static function form(Schema $schema): Schema
    {
        return ApplicationForm::configure($schema);
    }

    /**
     * Table schema for listing applications
     */
    public static function table(Table $table): Table
    {
        return ApplicationsTable::configure($table);

    }

    public static function infolist(Schema $schema): Schema
    {
        return ApplicationInfolist::configure($schema);
    }

    /**
     * Get the relation managers
     */
    public static function getRelations(): array
    {
        return [
            RelationManagers\ParentGuardiansRelationManager::class,
            RelationManagers\DocumentsRelationManager::class,
            RelationManagers\PaymentsRelationManager::class,
            RelationManagers\SchedulesRelationManager::class,
        ];
    }

    /**
     * Get the pages
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListApplications::route('/'),
            'create' => Pages\CreateApplication::route('/create'),
            'view' => Pages\ViewApplication::route('/{record}'),
            'edit' => Pages\EditApplication::route('/{record}/edit'),
        ];
    }

    /**
     * Get navigation badge (count of pending applications)
     */
    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('school_id', Filament::getTenant()->id)
            ->whereIn('status', ['submitted', 'under_review'])
            ->count();

        return $count > 0 ? (string) $count : null;
    }
    /**
     * Get navigation badge color
     */
    public static function getNavigationBadgeColor(): ?string
    {
        $count = static::getModel()::where('school_id', Filament::getTenant()->id)
            ->whereIn('status', ['submitted', 'under_review'])
            ->count();

        return $count > 10 ? 'warning' : 'info';
    }
}
