<?php

declare(strict_types=1);

namespace App\Filament\My\Resources\Applications;

use App\Filament\My\Resources\Applications\Pages;
use App\Filament\My\Resources\Applications\Schemas\ApplicationForm;
use App\Filament\My\Resources\Applications\Schemas\ApplicationInfolist;
use App\Filament\My\Resources\Applications\Tables\ApplicationsTable;
use App\Models\Application;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ApplicationResource extends Resource
{
    protected static ?string $model = Application::class;

    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-document-text';

    protected static string|null|\UnitEnum $navigationGroup = 'Admissions';

    protected static ?string $navigationLabel = 'My Applications';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'application_number';

    // Enable global search
    protected static int $globalSearchResultsLimit = 5;

    // ==================== SCHEMAS ====================

    public static function form(Schema $schema): Schema
    {
        return ApplicationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ApplicationsTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ApplicationInfolist::configure($schema);
    }

    // ==================== PAGES ====================

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListApplications::route('/'),
            'create' => Pages\CreateApplication::route('/create'),
            'view'   => Pages\ViewApplication::route('/{record}'),
            'edit'   => Pages\EditApplication::route('/{record}/edit'),
        ];
    }

    // ==================== QUERY SCOPING ====================

    /**
     * Only show applications belonging to the authenticated parent.
     */
    public static function getEloquentQuery(): Builder
    {
        $userId = auth()->id();

        if (! $userId) {
            return parent::getEloquentQuery()->whereRaw('1 = 0');
        }

        return parent::getEloquentQuery()
            ->where('user_id', $userId)
            ->with([
                'school',
                'admissionPeriod',
                'level',
                'parentGuardians',
                'documents',
                'payments',
                'medicalRecord',
                'enrollment',
            ]);
    }

    /**
     * Route binding also scoped to owner — prevents URL guessing.
     */
    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        $userId = auth()->id();

        if (! $userId) {
            return parent::getRecordRouteBindingEloquentQuery()->whereRaw('1 = 0');
        }

        return parent::getRecordRouteBindingEloquentQuery()
            ->where('user_id', $userId);
    }

    // ==================== AUTHORIZATION ====================

    public static function canCreate(): bool
    {
        // A parent may create unlimited draft applications
        return auth()->check();
    }

    public static function canView(Model $record): bool
    {
        return $record->user_id === auth()->id();
    }

    public static function canEdit(Model $record): bool
    {
        return $record->user_id === auth()->id()
            && $record->status === 'draft';
    }

    public static function canDelete(Model $record): bool
    {
        return $record->user_id === auth()->id()
            && $record->status === 'draft';
    }

    // ==================== GLOBAL SEARCH ====================

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'School'  => $record->school?->name ?? '-',
            'Status'  => ucwords(str_replace('_', ' ', $record->status)),
            'Student' => trim("{$record->student_first_name} {$record->student_last_name}"),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'application_number',
            'student_first_name',
            'student_last_name',
            'school.name',
        ];
    }

    // ==================== NAVIGATION BADGE ====================

    /**
     * Badge shows count of draft applications needing attention.
     */
    public static function getNavigationBadge(): ?string
    {
        $userId = auth()->id();

        if (! $userId) {
            return null;
        }

        $count = static::getModel()::query()
            ->where('user_id', $userId)
            ->where('status', 'draft')
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Incomplete draft applications';
    }
}
