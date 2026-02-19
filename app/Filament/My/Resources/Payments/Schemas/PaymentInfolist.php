<?php

declare(strict_types=1);

namespace App\Filament\My\Resources\Payments\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PaymentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Payment Summary')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('transaction_code')
                            ->badge()
                            ->color('gray'),
                        TextEntry::make('application.application_number')
                            ->label('Application #'),
                        TextEntry::make('paymentType.name')
                            ->label('Payment Type'),
                        TextEntry::make('amount')
                            ->formatStateUsing(fn ($state, $record): string => $record->currency . ' ' . number_format((float) $state, 0, ',', '.'))
                            ->weight('bold')
                            ->color('success'),
                        TextEntry::make('status')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'pending' => 'Pending',
                                'submitted' => 'Submitted',
                                'verified' => 'Verified',
                                'rejected' => 'Rejected',
                                'refunded' => 'Refunded',
                                default => ucfirst($state),
                            }),
                        TextEntry::make('payment_date')
                            ->date('d M Y')
                            ->placeholder('-'),
                    ]),

                Section::make('Bank Transfer Information')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('paymentType.bank_name')
                            ->label('Bank Name')
                            ->placeholder('-'),
                        TextEntry::make('paymentType.account_number')
                            ->label('Account Number')
                            ->placeholder('-')
                            ->copyable(),
                        TextEntry::make('paymentType.account_holder')
                            ->label('Account Holder')
                            ->placeholder('-'),
                        TextEntry::make('paymentType.payment_instructions')
                            ->label('Instructions')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ]),

                Section::make('Submission & Verification')
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('payment_method')
                                ->placeholder('-')
                                ->formatStateUsing(fn (?string $state): string => $state ? (string) str($state)->replace('_', ' ')->title() : '-'),
                            TextEntry::make('reference_number')
                                ->placeholder('-'),
                            TextEntry::make('proof_file')
                                ->label('Proof File')
                                ->formatStateUsing(fn (?string $state): string => filled($state) ? 'Uploaded' : 'Not uploaded'),
                            TextEntry::make('verified_at')
                                ->dateTime('d M Y H:i')
                                ->placeholder('-'),
                            TextEntry::make('rejection_reason')
                                ->label('Rejection Reason')
                                ->placeholder('-')
                                ->columnSpanFull(),
                            TextEntry::make('notes')
                                ->placeholder('-')
                                ->columnSpanFull(),
                        ]),
                    ]),
            ]);
    }
}
