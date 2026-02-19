<?php

declare(strict_types=1);

namespace App\Filament\My\Resources\Payments;

use App\Filament\My\Resources\Payments\Pages;
use App\Filament\My\Resources\Payments\Schemas\PaymentInfolist;
use App\Filament\My\Resources\Payments\Tables\PaymentsTable;
use App\Models\Payment;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-banknotes';

    protected static string|null|\UnitEnum $navigationGroup = 'Admissions';

    protected static ?string $navigationLabel = 'My Payments';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'transaction_code';

    public static function table(Table $table): Table
    {
        return PaymentsTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PaymentInfolist::configure($schema);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
            'view' => Pages\ViewPayment::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $userId = auth()->id();

        if (! $userId) {
            return parent::getEloquentQuery()->whereRaw('1 = 0');
        }

        return parent::getEloquentQuery()
            ->whereHas('application', fn (Builder $query) => $query->where('user_id', $userId))
            ->with(['application', 'paymentType', 'verifier']);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        $userId = auth()->id();

        if (! $userId) {
            return null;
        }

        $count = static::getModel()::query()
            ->whereHas('application', fn (Builder $query) => $query->where('user_id', $userId))
            ->whereIn('status', ['pending', 'rejected'])
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        $userId = auth()->id();

        if (! $userId) {
            return parent::getRecordRouteBindingEloquentQuery()->whereRaw('1 = 0');
        }

        return parent::getRecordRouteBindingEloquentQuery()
            ->whereHas('application', fn (Builder $query) => $query->where('user_id', $userId));
    }
}
