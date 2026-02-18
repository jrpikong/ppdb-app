<?php

namespace App\Filament\School\Resources\Payments\Schemas;

use App\Models\Payment;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PaymentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('application.id')
                    ->label('Application'),
                TextEntry::make('paymentType.name')
                    ->label('Payment type'),
                TextEntry::make('transaction_code'),
                TextEntry::make('amount')
                    ->numeric(),
                TextEntry::make('currency'),
                TextEntry::make('payment_date')
                    ->date(),
                TextEntry::make('payment_method')
                    ->placeholder('-'),
                TextEntry::make('bank_name')
                    ->placeholder('-'),
                TextEntry::make('account_number')
                    ->placeholder('-'),
                TextEntry::make('reference_number')
                    ->placeholder('-'),
                TextEntry::make('proof_file')
                    ->placeholder('-'),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('rejection_reason')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('notes')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('verified_by')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('verified_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('refund_amount')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('refund_date')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('refund_reason')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (Payment $record): bool => $record->trashed()),
            ]);
    }
}
