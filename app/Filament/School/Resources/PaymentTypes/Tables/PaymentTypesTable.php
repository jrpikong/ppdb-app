<?php
declare(strict_types=1);

namespace App\Filament\School\Resources\PaymentTypes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class PaymentTypesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('code')
                    ->label('Code')
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Payment Type')
                    ->description(fn($record): string => $record->description
                        ? \Illuminate\Support\Str::limit($record->description, 60)
                        : '—'
                    )
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                TextColumn::make('amount')
                    ->label('Amount')
                    ->formatStateUsing(fn($state): string => 'IDR ' . number_format((int)$state, 0, ',', '.')
                    )
                    ->sortable()
                    ->weight('semibold')
                    ->color('success'),

                TextColumn::make('payment_stage')
                    ->label('Stage')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'pre_submission' => 'Pre-Submission',
                        'post_acceptance' => 'Post-Acceptance',
                        'enrollment' => 'Enrollment',
                        'installment' => 'Installment',
                        default => ucfirst($state),
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'pre_submission' => 'warning',
                        'post_acceptance' => 'success',
                        'enrollment' => 'primary',
                        'installment' => 'info',
                        default => 'gray',
                    })
                    ->sortable(),

                IconColumn::make('is_mandatory')
                    ->label('Mandatory')
                    ->boolean()
                    ->trueIcon('heroicon-o-exclamation-circle')
                    ->trueColor('danger')
                    ->falseIcon('heroicon-o-minus-circle')
                    ->falseColor('gray'),

                IconColumn::make('is_refundable')
                    ->label('Refundable')
                    ->boolean()
                    ->trueColor('info')
                    ->falseColor('gray')
                    ->toggleable(isToggledHiddenByDefault: true),

                ToggleColumn::make('is_active')
                    ->label('Active')
                    ->afterStateUpdated(fn($record, bool $state) => Notification::make()
                        ->title($state ? 'Payment Type Activated' : 'Payment Type Deactivated')
                        ->{$state ? 'success' : 'warning'}()
                        ->send()
                    ),

            ])
            ->defaultSort('payment_stage')
            ->filters([
                SelectFilter::make('payment_stage')
                    ->label('Stage')
                    ->options([
                        'pre_submission' => 'Pre-Submission',
                        'post_acceptance' => 'Post-Acceptance',
                        'enrollment' => 'Enrollment',
                        'installment' => 'Installment',
                    ])
                    ->native(false),

                TernaryFilter::make('is_mandatory')->label('Mandatory')->native(false),
                TernaryFilter::make('is_active')->label('Active Status')->native(false),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make()->label(''),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->emptyStateIcon('heroicon-o-currency-dollar')
            ->emptyStateHeading('No Payment Types Yet')
            ->emptyStateDescription('Define the payment structure for this school.')
            ->striped();
    }
}
