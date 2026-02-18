<?php

declare(strict_types=1);

namespace App\Filament\School\Resources\Enrollments;

use App\Filament\School\Resources\Enrollments\Pages;
use App\Filament\School\Resources\Enrollments\Schemas\EnrollmentForm;
use App\Filament\School\Resources\Enrollments\Schemas\EnrollmentInfolist;
use App\Filament\School\Resources\Enrollments\Tables\EnrollmentsTable;
use App\Models\Enrollment;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EnrollmentResource extends Resource
{
    protected static ?string $model = Enrollment::class;

    protected static bool $isScopedToTenant = false;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string|null|\UnitEnum $navigationGroup = 'Students';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Enrollment';

    protected static ?string $pluralModelLabel = 'Student Enrollments';

    protected static ?string $recordTitleAttribute = 'student_id';

    // ✅ Tenant scoping via application relationship
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class])
            ->whereHas('application', fn (Builder $q) =>
            $q->where('school_id', Filament::getTenant()?->id)
            )
            ->with(['application', 'application.user', 'enrolledBy'])
            ->orderByDesc('enrollment_date');
    }

    public static function form(Schema $schema): Schema
    {
        return EnrollmentForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return EnrollmentInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EnrollmentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListEnrollments::route('/'),
            'create' => Pages\CreateEnrollment::route('/create'),
            'view'   => Pages\ViewEnrollment::route('/{record}'),
            'edit'   => Pages\EditEnrollment::route('/{record}/edit'),
        ];
    }

    // Badge: jumlah active enrollments
    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::whereHas('application', fn (Builder $q) =>
        $q->where('school_id', Filament::getTenant()?->id)
        )->where('status', 'active')->count();

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
