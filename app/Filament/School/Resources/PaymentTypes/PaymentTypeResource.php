<?php

namespace App\Filament\School\Resources\PaymentTypes;

use App\Filament\School\Resources\PaymentTypes\Pages\CreatePaymentType;
use App\Filament\School\Resources\PaymentTypes\Pages\EditPaymentType;
use App\Filament\School\Resources\PaymentTypes\Pages\ListPaymentTypes;
use App\Filament\School\Resources\PaymentTypes\Schemas\PaymentTypeForm;
use App\Filament\School\Resources\PaymentTypes\Tables\PaymentTypesTable;
use App\Models\PaymentType;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PaymentTypeResource extends Resource
{
    protected static ?string $model = PaymentType::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;
    protected static string|null|\UnitEnum $navigationGroup = 'Finance';
    protected static ?int $navigationSort = 1;
    protected static ?string $modelLabel = 'Payment Type';
    protected static ?string $pluralModelLabel = 'Payment Types';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class])
            ->where('payment_types.school_id', Filament::getTenant()?->id);
    }

    public static function form(Schema $schema): Schema
    {
        return PaymentTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PaymentTypesTable::configure($table);
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
            'index' => ListPaymentTypes::route('/'),
            'create' => CreatePaymentType::route('/create'),
            'edit' => EditPaymentType::route('/{record}/edit'),
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
