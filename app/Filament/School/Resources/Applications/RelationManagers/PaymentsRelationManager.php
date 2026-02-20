<?php

declare(strict_types=1);

namespace App\Filament\School\Resources\Applications\RelationManagers;

use App\Support\ParentNotifier;
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
use Illuminate\Database\Eloquent\Builder;
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
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('payment_type_id')
                            ->label('Payment Type')
                            ->relationship(
                                name: 'paymentType',
                                titleAttribute: 'name',
                                modifyQueryUsing: function (Builder $query): Builder {
                                    $tenantId = Filament::getTenant()?->id;

                                    if (! $tenantId) {
                                        return $query->whereRaw('1 = 0');
                                    }

                                    return $query
                                        ->where('school_id', $tenantId)
                                        ->where('is_active', true);
                                }
                            )
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function (Set $set, $state): void {
                                if (! $state) {
                                    return;
                                }

                                $paymentType = \App\Models\PaymentType::find($state);

                                $set('amount', $paymentType?->amount);
                                $set('currency', $paymentType?->currency ?? 'IDR');
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

                        Forms\Components\DatePicker::make('payment_date')
                            ->label('Payment Date')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->required(),

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
                            ->native(false),

                        Forms\Components\TextInput::make('bank_name')
                            ->label('Bank Name')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('account_number')
                            ->label('Account Number')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('reference_number')
                            ->label('Reference Number')
                            ->maxLength(255),

                        Forms\Components\FileUpload::make('proof_file')
                            ->label('Payment Proof')
                            ->disk('local')
                            ->directory('payments')
                            ->maxSize(10240)
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'])
                            ->helperText('Maximum file size: 10MB. Allowed types: PDF, JPG, PNG')
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('notes')
                            ->label('Notes')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),

                Section::make('Verification & Refund')
                    ->columns(2)
                    ->collapsible()
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Payment Status')
                            ->options([
                                'pending' => 'Pending',
                                'submitted' => 'Submitted',
                                'verified' => 'Verified',
                                'rejected' => 'Rejected',
                                'refunded' => 'Refunded',
                            ])
                            ->default('pending')
                            ->required()
                            ->native(false)
                            ->live(),

                        Forms\Components\DateTimePicker::make('verified_at')
                            ->label('Verified At')
                            ->native(false)
                            ->disabled()
                            ->visible(fn (Get $get): bool => in_array((string) $get('status'), ['verified', 'rejected', 'refunded'], true)),

                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Rejection Reason')
                            ->rows(2)
                            ->visible(fn (Get $get): bool => $get('status') === 'rejected')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('refund_amount')
                            ->label('Refund Amount')
                            ->numeric()
                            ->minValue(0)
                            ->visible(fn (Get $get): bool => $get('status') === 'refunded'),

                        Forms\Components\DatePicker::make('refund_date')
                            ->label('Refund Date')
                            ->native(false)
                            ->visible(fn (Get $get): bool => $get('status') === 'refunded'),

                        Forms\Components\Textarea::make('refund_reason')
                            ->label('Refund Reason')
                            ->rows(2)
                            ->visible(fn (Get $get): bool => $get('status') === 'refunded')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('transaction_code')
            ->columns([
                Tables\Columns\TextColumn::make('transaction_code')
                    ->label('Transaction')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold),

                Tables\Columns\TextColumn::make('paymentType.name')
                    ->label('Payment Type')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->formatStateUsing(fn ($state, $record): string => $record->currency . ' ' . number_format((float) $state, 0, ',', '.'))
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->color('success'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->sortable()
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'submitted' => 'warning',
                        'verified' => 'success',
                        'rejected' => 'danger',
                        'refunded' => 'info',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('payment_date')
                    ->label('Payment Date')
                    ->date('d M Y')
                    ->sortable()
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Method')
                    ->formatStateUsing(fn (?string $state): string => $state ? (string) str($state)->replace('_', ' ')->title() : '-')
                    ->placeholder('-')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('verifier.name')
                    ->label('Verified By')
                    ->placeholder('Not verified')
                    ->limit(25)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('verified_at')
                    ->label('Verified At')
                    ->dateTime('d M Y H:i')
                    ->placeholder('-')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('payment_type_id')
                    ->label('Payment Type')
                    ->relationship(
                        name: 'paymentType',
                        titleAttribute: 'name',
                        modifyQueryUsing: function (Builder $query): Builder {
                            $tenantId = Filament::getTenant()?->id;

                            return $tenantId
                                ? $query->where('school_id', $tenantId)
                                : $query->whereRaw('1 = 0');
                        }
                    )
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'submitted' => 'Submitted',
                        'verified' => 'Verified',
                        'rejected' => 'Rejected',
                        'refunded' => 'Refunded',
                    ])
                    ->multiple(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->icon('heroicon-o-plus')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['status'] = $data['status'] ?? 'pending';
                        return $data;
                    }),
            ])
            ->recordActions([
                Action::make('viewProof')
                    ->label('View Proof')
                    ->icon('heroicon-o-document-magnifying-glass')
                    ->color('info')
                    ->url(fn ($record): ?string => $record->proof_file ? route('secure-files.payments.proof', ['payment' => $record->id]) : null)
                    ->openUrlInNewTab()
                    ->visible(fn ($record): bool => filled($record->proof_file)),

                Action::make('markSubmitted')
                    ->label('Mark Submitted')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function ($record): void {
                        $record->update([
                            'status' => 'submitted',
                        ]);
                    })
                    ->visible(fn ($record): bool => $record->status === 'pending'),

                Action::make('verify')
                    ->label('Verify')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->label('Verification Notes')
                            ->rows(3),
                    ])
                    ->action(function ($record, array $data): void {
                        $record->update([
                            'status' => 'verified',
                            'notes' => $data['notes'] ?? $record->notes,
                            'verified_at' => now(),
                            'verified_by' => auth()->id(),
                            'rejection_reason' => null,
                        ]);

                        ParentNotifier::paymentStatusChanged($record->refresh(), 'verified', $data['notes'] ?? null);
                    })
                    ->successNotificationTitle('Payment verified successfully')
                    ->visible(fn ($record): bool => $record->status === 'submitted'),

                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->schema([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Rejection Reason')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function ($record, array $data): void {
                        $record->update([
                            'status' => 'rejected',
                            'rejection_reason' => $data['rejection_reason'],
                            'verified_at' => now(),
                            'verified_by' => auth()->id(),
                        ]);

                        ParentNotifier::paymentStatusChanged($record->refresh(), 'rejected', $data['rejection_reason']);
                    })
                    ->successNotificationTitle('Payment rejected')
                    ->visible(fn ($record): bool => $record->status === 'submitted'),

                Action::make('refund')
                    ->label('Refund')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('info')
                    ->schema([
                        Forms\Components\TextInput::make('refund_amount')
                            ->label('Refund Amount')
                            ->numeric()
                            ->required(),
                        Forms\Components\DatePicker::make('refund_date')
                            ->label('Refund Date')
                            ->native(false)
                            ->required()
                            ->default(today()),
                        Forms\Components\Textarea::make('refund_reason')
                            ->label('Refund Reason')
                            ->required()
                            ->rows(2),
                    ])
                    ->action(function ($record, array $data): void {
                        $record->update([
                            'status' => 'refunded',
                            'refund_amount' => $data['refund_amount'],
                            'refund_date' => $data['refund_date'],
                            'refund_reason' => $data['refund_reason'],
                        ]);

                        ParentNotifier::paymentStatusChanged($record->refresh(), 'refunded', $data['refund_reason']);
                    })
                    ->successNotificationTitle('Payment refunded')
                    ->visible(fn ($record): bool => $record->status === 'verified'),

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
                            $records->each(function ($record): void {
                                if ($record->status !== 'submitted') {
                                    return;
                                }

                                $record->update([
                                    'status' => 'verified',
                                    'verified_at' => now(),
                                    'verified_by' => auth()->id(),
                                    'rejection_reason' => null,
                                ]);

                                ParentNotifier::paymentStatusChanged($record->refresh(), 'verified');
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
