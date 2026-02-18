<?php

namespace App\Filament\School\Resources\Enrollments;

use App\Filament\School\Resources\Enrollments\Pages\CreateEnrollment;
use App\Filament\School\Resources\Enrollments\Pages\EditEnrollment;
use App\Filament\School\Resources\Enrollments\Pages\ListEnrollments;
use App\Filament\School\Resources\Enrollments\Pages\ViewEnrollment;
use App\Filament\School\Resources\Enrollments\Schemas\EnrollmentForm;
use App\Filament\School\Resources\Enrollments\Schemas\EnrollmentInfolist;
use App\Filament\School\Resources\Enrollments\Tables\EnrollmentsTable;
use App\Models\Enrollment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EnrollmentResource extends Resource
{
    protected static ?string $model = Enrollment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'student_id';

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
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEnrollments::route('/'),
            'create' => CreateEnrollment::route('/create'),
            'view' => ViewEnrollment::route('/{record}'),
            'edit' => EditEnrollment::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
