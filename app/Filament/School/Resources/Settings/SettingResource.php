<?php

declare(strict_types=1);

namespace App\Filament\School\Resources\Settings;

use App\Filament\School\Resources\Settings\Schemas\SettingForm;
use App\Filament\School\Resources\Settings\Tables\SettingsTable;
use App\Models\Setting;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static ?string $tenantOwnershipRelationshipName = 'defaultSchool';

    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Settings';

    protected static string|null|\UnitEnum $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 99;

    public static function form(Schema $schema): Schema
    {
        return SettingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SettingsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSettings::route('/'),
            'create' => Pages\CreateSetting::route('/create'),
            'edit' => Pages\EditSetting::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery();
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->can('view_any_setting');
    }

    public static function canCreate(): bool
    {
        return auth()->user()->can('create_setting') && static::getModel()::query()->count() === 0;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()->can('update_setting');
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()->can('delete_setting');
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::query()->count();
    }
}
