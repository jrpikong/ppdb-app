<?php

namespace App\Filament\School\Resources\Payments;

use App\Filament\School\Resources\Payments\Pages\CreatePayment;
use App\Filament\School\Resources\Payments\Pages\EditPayment;
use App\Filament\School\Resources\Payments\Pages\ListPayments;
use App\Filament\School\Resources\Payments\Pages\ViewPayment;
use App\Filament\School\Resources\Payments\Schemas\PaymentForm;
use App\Filament\School\Resources\Payments\Schemas\PaymentInfolist;
use App\Filament\School\Resources\Payments\Tables\PaymentsTable;
use App\Models\Payment;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;
    protected static string|null|\UnitEnum $navigationGroup = 'Finance';
    protected static ?int $navigationSort = 2;
    protected static ?string $modelLabel = 'Payment';
    protected static ?string $pluralModelLabel = 'Payment Verification';
    protected static ?string $recordTitleAttribute = 'transaction_code';

    protected static bool $isScopedToTenant = false;
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class])
            ->whereHas('application', fn (Builder $q) =>
            $q->where('school_id', Filament::getTenant()?->id)
            )
            ->with(['application', 'application.user', 'paymentType', 'verifier']);
    }


    public static function form(Schema $schema): Schema
    {
        return PaymentForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PaymentInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PaymentsTable::configure($table);
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
            'index' => ListPayments::route('/'),
            'view' => ViewPayment::route('/{record}'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::whereHas('application', fn (Builder $q) =>
        $q->where('school_id', Filament::getTenant()?->id)
        )->where('status', 'submitted')->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Payments awaiting verification';
    }
}
