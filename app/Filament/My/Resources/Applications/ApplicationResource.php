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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListApplications::route('/'),
            'create' => Pages\CreateApplication::route('/create'),
            'view' => Pages\ViewApplication::route('/{record}'),
            'edit' => Pages\EditApplication::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $userId = auth()->id();

        if (! $userId) {
            return parent::getEloquentQuery()->whereRaw('1 = 0');
        }

        return parent::getEloquentQuery()
            ->where('user_id', $userId)
            ->with(['school', 'admissionPeriod', 'level']);
    }

    public static function canEdit(Model $record): bool
    {
        return $record->user_id === auth()->id()
            && in_array($record->status, ['draft'], true);
    }

    public static function canDelete(Model $record): bool
    {
        return $record->user_id === auth()->id()
            && in_array($record->status, ['draft'], true);
    }

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

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        $userId = auth()->id();

        if (! $userId) {
            return parent::getRecordRouteBindingEloquentQuery()->whereRaw('1 = 0');
        }

        return parent::getRecordRouteBindingEloquentQuery()
            ->where('user_id', $userId);
    }
}
