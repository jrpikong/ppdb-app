<?php

declare(strict_types=1);

namespace App\Filament\School\Resources\Applications\RelationManagers;

use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Support\Enums\FontWeight;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Storage;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    protected static ?string $title = 'Payment Records';

    protected static string|null|\BackedEnum $icon = 'heroicon-o-banknotes';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Payment Information')
                    ->schema([
                        Forms\Components\Select::make('payment_type_id')
                            ->label('Payment Type')
                            ->relationship(
                                name: 'paymentType',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn ($query) => $query
                                    ->where('school_id', Filament::getTenant()->id)
                                    ->where('is_active', true)
                            )
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function (Set $set, $state) {
                                if ($state) {
                                    $paymentType = \App\Models\PaymentType::find($state);
                                    $set('amount', $paymentType?->amount);
                                    $set('currency', $paymentType?->currency ?? 'IDR');
                                }
                            })
                            ->columnSpan(2),

                        Forms\Components\TextInput::make('amount')
                            ->label('Amount')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->prefix('Rp')
                            ->columnSpan(1),

                        Forms\Components\Select::make('currency')
                            ->label('Currency')
                            ->options([
                                'IDR' => 'IDR (Indonesian Rupiah)',
                                'USD' => 'USD (US Dollar)',
                                'SGD' => 'SGD (Singapore Dollar)',
                            ])
                            ->default('IDR')
                            ->required()
                            ->native(false)
                            ->columnSpan(1),

                        Forms\Components\Select::make('payment_method')
                            ->label('Payment Method')
                            ->options([
                                'bank_transfer' => 'Bank Transfer',
                                'virtual_account' => 'Virtual Account',
                                'credit_card' => 'Credit Card',
                                'debit_card' => 'Debit Card',
                                'e_wallet' => 'E-Wallet',
                                'cash' => 'Cash',
                                'other' => 'Other',
                            ])
                            ->native(false)
                            ->columnSpan(1),

                        Forms\Components\DateTimePicker::make('paid_at')
                            ->label('Payment Date')
                            ->native(false)
                            ->displayFormat('d/m/Y H:i')
                            ->columnSpan(1),

                        Forms\Components\FileUpload::make('payment_proof')
                            ->label('Payment Proof')
                            ->disk('public')
                            ->directory('payments')
                            ->visibility('private')
                            ->maxSize(5120)
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'])
                            ->helperText('Maximum file size: 5MB. Allowed types: PDF, JPG, PNG')
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('notes')
                            ->label('Notes')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Verification')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Payment Status')
                            ->options([
                                'pending' => 'Pending',
                                'verified' => 'Verified',
                                'rejected' => 'Rejected',
                                'refunded' => 'Refunded',
                            ])
                            ->default('pending')
                            ->required()
                            ->native(false)
                            ->live()
                            ->columnSpan(1),

                        Forms\Components\DateTimePicker::make('verified_at')
                            ->label('Verified At')
                            ->native(false)
                            ->disabled()
                            ->visible(fn (Get $get) => $get('status') === 'verified')
                            ->columnSpan(1),

                        Forms\Components\Textarea::make('verification_notes')
                            ->label('Verification Notes')
                            ->rows(2)
                            ->visible(fn (Get $get) => in_array($get('status'), ['verified', 'rejected']))
                            ->columnSpanFull(),

                        Forms\Components\DateTimePicker::make('refunded_at')
                            ->label('Refunded At')
                            ->native(false)
                            ->visible(fn (Get $get) => $get('status') === 'refunded')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('payment_type_id')
            ->columns([
                Tables\Columns\TextColumn::make('paymentType.name')
                    ->label('Payment Type')
                    ->sortable()
                    ->searchable()
                    ->weight(FontWeight::Bold),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->money('IDR', locale: 'id')
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->color('success'),

                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Method')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => (string)str($state)->replace('_', ' ')->title()),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->sortable()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'verified' => 'success',
                        'rejected' => 'danger',
                        'refunded' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),

                Tables\Columns\TextColumn::make('paid_at')
                    ->label('Paid Date')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->placeholder('Not paid'),

                Tables\Columns\TextColumn::make('verifiedBy.name')
                    ->label('Verified By')
                    ->placeholder('Not verified')
                    ->limit(25)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('verified_at')
                    ->label('Verified At')
                    ->dateTime('d M Y H:i')
                    ->placeholder('N/A')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('payment_type_id')
                    ->label('Payment Type')
                    ->relationship(
                        name: 'paymentType',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn ($query) => $query
                            ->where('school_id', Filament::getTenant()->id)
                    )
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'verified' => 'Verified',
                        'rejected' => 'Rejected',
                        'refunded' => 'Refunded',
                    ])
                    ->multiple(),

                Tables\Filters\SelectFilter::make('payment_method')
                    ->options([
                        'bank_transfer' => 'Bank Transfer',
                        'virtual_account' => 'Virtual Account',
                        'credit_card' => 'Credit Card',
                        'debit_card' => 'Debit Card',
                        'e_wallet' => 'E-Wallet',
                        'cash' => 'Cash',
                        'other' => 'Other',
                    ])
                    ->multiple(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->icon('heroicon-o-plus'),
            ])
            ->recordActions([
                Action::make('viewProof')
                    ->label('View Proof')
                    ->icon('heroicon-o-document-magnifying-glass')
                    ->color('info')
                    ->url(fn ($record) => $record->payment_proof ? Storage::disk('public')->url($record->payment_proof) : null)
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => $record->payment_proof),

                Action::make('verify')
                    ->label('Verify')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->schema([
                        Forms\Components\Textarea::make('verification_notes')
                            ->label('Verification Notes')
                            ->rows(3),
                    ])
                    ->action(function ($record, array $data): void {
                        $record->update([
                            'status' => 'verified',
                            'verification_notes' => $data['verification_notes'],
                            'verified_at' => now(),
                            'verified_by' => auth()->id(),
                        ]);
                    })
                    ->successNotificationTitle('Payment verified successfully')
                    ->visible(fn ($record) => $record->status === 'pending'),

                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->schema([
                        Forms\Components\Textarea::make('verification_notes')
                            ->label('Rejection Reason')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function ($record, array $data): void {
                        $record->update([
                            'status' => 'rejected',
                            'verification_notes' => $data['verification_notes'],
                            'verified_at' => now(),
                            'verified_by' => auth()->id(),
                        ]);
                    })
                    ->successNotificationTitle('Payment rejected')
                    ->visible(fn ($record) => $record->status === 'pending'),

                Action::make('refund')
                    ->label('Refund')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function ($record): void {
                        $record->update([
                            'status' => 'refunded',
                            'refunded_at' => now(),
                        ]);
                    })
                    ->successNotificationTitle('Payment refunded')
                    ->visible(fn ($record) => $record->status === 'verified'),

                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('verifyBulk')
                        ->label('Verify Selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records): void {
                            $records->each(function ($record) {
                                if ($record->status === 'pending') {
                                    $record->update([
                                        'status' => 'verified',
                                        'verified_at' => now(),
                                        'verified_by' => auth()->id(),
                                    ]);
                                }
                            });
                        })
                        ->deselectRecordsAfterCompletion()
                        ->successNotificationTitle('Payments verified successfully'),

                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No payments recorded yet')
            ->emptyStateDescription('Payment records will appear here once added.')
            ->emptyStateIcon('heroicon-o-banknotes');
    }
}
