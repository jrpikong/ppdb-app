<?php

namespace App\Filament\School\Resources\Payments\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('application_id')
                    ->relationship('application', 'id')
                    ->required(),
                Select::make('payment_type_id')
                    ->relationship('paymentType', 'name')
                    ->required(),
                TextInput::make('transaction_code')
                    ->required(),
                TextInput::make('amount')
                    ->required()
                    ->numeric(),
                TextInput::make('currency')
                    ->required()
                    ->default('IDR'),
                DatePicker::make('payment_date')
                    ->required(),
                TextInput::make('payment_method'),
                TextInput::make('bank_name'),
                TextInput::make('account_number'),
                TextInput::make('reference_number'),
                TextInput::make('proof_file'),
                Select::make('status')
                    ->options([
            'pending' => 'Pending',
            'submitted' => 'Submitted',
            'verified' => 'Verified',
            'rejected' => 'Rejected',
            'refunded' => 'Refunded',
        ])
                    ->default('pending')
                    ->required(),
                Textarea::make('rejection_reason')
                    ->columnSpanFull(),
                Textarea::make('notes')
                    ->columnSpanFull(),
                TextInput::make('verified_by')
                    ->numeric(),
                DateTimePicker::make('verified_at'),
                TextInput::make('refund_amount')
                    ->numeric(),
                DatePicker::make('refund_date'),
                Textarea::make('refund_reason')
                    ->columnSpanFull(),
            ]);
    }
}
