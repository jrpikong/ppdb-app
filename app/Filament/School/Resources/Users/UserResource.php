<?php

declare(strict_types=1);

namespace App\Filament\School\Resources\Users;

use App\Filament\School\Resources\Users\Pages\CreateUser;
use App\Filament\School\Resources\Users\Pages\EditUser;
use App\Filament\School\Resources\Users\Pages\ListUsers;
use App\Filament\School\Resources\Users\Pages\ViewUser;
use App\Filament\School\Resources\Users\Schemas\UserForm;
use App\Filament\School\Resources\Users\Schemas\UserInfolist;
use App\Filament\School\Resources\Users\Tables\UsersTable;
use App\Models\User;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static string|null|\UnitEnum $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 10;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Staff';

    protected static ?string $pluralModelLabel = 'Staff Management';

    // ================================================================
// PATCH: UserResource.php — method getEloquentQuery()
// ================================================================

    public static function getEloquentQuery(): Builder
    {
        $tenant = Filament::getTenant();

        return parent::getEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class])
            ->where('users.school_id', $tenant?->id)             // ✅ prefix tabel users
            ->whereHas('roles', fn (Builder $q) =>
            $q->whereNotIn('roles.name', ['parent'])          // ✅ prefix tabel roles
            ->where('roles.school_id', $tenant?->id)        // ✅ prefix tabel roles
            );
    }

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return UserInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'view'   => ViewUser::route('/{record}'),
            'edit'   => EditUser::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $tenant = Filament::getTenant();

        $count = static::getModel()::where('school_id', $tenant?->id)
            ->where('is_active', true)
            ->count();

        return $count > 0 ? (string) $count : null;
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
