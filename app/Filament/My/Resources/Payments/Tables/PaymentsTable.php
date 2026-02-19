<?php

declare(strict_types=1);

namespace App\Filament\My\Resources\Payments\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('transaction_code')
                    ->label('Transaction')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                TextColumn::make('application.application_number')
                    ->label('Application #')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('paymentType.name')
                    ->label('Payment Type')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('amount')
                    ->label('Amount')
                    ->formatStateUsing(fn ($state, $record): string => $record->currency . ' ' . number_format((float) $state, 0, ',', '.'))
                    ->sortable()
                    ->weight('semibold')
                    ->color('success'),

                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Pending',
                        'submitted' => 'Submitted',
                        'verified' => 'Verified',
                        'rejected' => 'Rejected',
                        'refunded' => 'Refunded',
                        default => ucfirst($state),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'submitted' => 'warning',
                        'verified' => 'success',
                        'rejected' => 'danger',
                        'refunded' => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('payment_date')
                    ->label('Payment Date')
                    ->date('d M Y')
                    ->placeholder('-')
                    ->sortable(),

                TextColumn::make('verified_at')
                    ->label('Verified At')
                    ->dateTime('d M Y H:i')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'submitted' => 'Submitted',
                        'verified' => 'Verified',
                        'rejected' => 'Rejected',
                        'refunded' => 'Refunded',
                    ])
                    ->native(false),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No payment records')
            ->emptyStateDescription('Payments related to your applications will appear here.');
    }
}
