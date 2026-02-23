<?php

declare(strict_types=1);

namespace App\Filament\SuperAdmin\Resources\Schools;

use App\Filament\SuperAdmin\Resources\Schools\Pages\CreateSchool;
use App\Filament\SuperAdmin\Resources\Schools\Pages\EditSchool;
use App\Filament\SuperAdmin\Resources\Schools\Pages\ListSchools;
use App\Filament\SuperAdmin\Resources\Schools\Pages\ViewSchool;
use App\Filament\SuperAdmin\Resources\Schools\Schemas\SchoolForm;
use App\Filament\SuperAdmin\Resources\Schools\Schemas\SchoolInfolist;
use App\Filament\SuperAdmin\Resources\Schools\Tables\SchoolsTable;
use App\Models\School;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SchoolResource extends Resource
{
    protected static ?string $model = School::class;

    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-building-office-2';

    protected static string|null|\UnitEnum $navigationGroup = 'School Management';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'School';

    protected static ?string $pluralModelLabel = 'Schools';

    public static function form(Schema $schema): Schema
    {
        return SchoolForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SchoolInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SchoolsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withCount('users');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSchools::route('/'),
            'create' => CreateSchool::route('/create'),
            'view' => ViewSchool::route('/{record}'),
            'edit' => EditSchool::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view_any_school') ?? false;
    }

    public static function canView(Model $record): bool
    {
        return auth()->user()?->can('view_school') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create_school') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->can('update_school') ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->can('delete_school') ?? false;
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::query()->count();
    }
}

