<?php

declare(strict_types=1);

namespace App\Filament\My\Resources\Profiles;

use App\Filament\My\Resources\Profiles\Pages;
use App\Filament\My\Resources\Profiles\Schemas\ProfileForm;
use App\Models\User;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class ProfileResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-user-circle';

    protected static string|null|\UnitEnum $navigationGroup = 'Account';

    protected static ?string $navigationLabel = 'My Profile';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return ProfileForm::configure($schema);
    }

    public static function getPages(): array
    {
        return [
            'edit' => Pages\EditProfile::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $userId = auth()->id();

        if (! $userId) {
            return parent::getEloquentQuery()->whereRaw('1 = 0');
        }

        return parent::getEloquentQuery()->whereKey($userId);
    }

    public static function getNavigationUrl(): string
    {
        $userId = auth()->id();

        if (! $userId) {
            return '#';
        }

        return static::getUrl('edit', ['record' => $userId]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canViewAny(): bool
    {
        return auth()->check();
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        $userId = auth()->id();

        if (! $userId) {
            return parent::getRecordRouteBindingEloquentQuery()->whereRaw('1 = 0');
        }

        return parent::getRecordRouteBindingEloquentQuery()->whereKey($userId);
    }
}

