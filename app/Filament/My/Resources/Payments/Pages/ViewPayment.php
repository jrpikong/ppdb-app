<?php

declare(strict_types=1);

namespace App\Filament\My\Resources\Payments\Pages;

use App\Filament\My\Resources\Payments\PaymentResource;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use RuntimeException;

class ViewPayment extends ViewRecord
{
    protected static string $resource = PaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('submitProof')
                ->label('Submit Payment Proof')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('primary')
                ->visible(fn (): bool => in_array($this->getRecord()->status, ['pending', 'rejected'], true))
                ->schema([
                    DatePicker::make('payment_date')
                        ->label('Payment Date')
                        ->required()
                        ->native(false)
                        ->default(today()),
                    Select::make('payment_method')
                        ->required()
                        ->native(false)
                        ->options([
                            'bank_transfer' => 'Bank Transfer',
                            'virtual_account' => 'Virtual Account',
                            'credit_card' => 'Credit Card',
                            'debit_card' => 'Debit Card',
                            'e_wallet' => 'E-Wallet',
                            'cash' => 'Cash',
                        ]),
                    TextInput::make('bank_name')
                        ->maxLength(255),
                    TextInput::make('account_number')
                        ->maxLength(255),
                    TextInput::make('reference_number')
                        ->maxLength(255),
                    FileUpload::make('proof_file')
                        ->label('Payment Proof')
                        ->required()
                        ->disk('local')
                        ->directory('payments')
                        ->maxSize(10240)
                        ->acceptedFileTypes([
                            'application/pdf',
                            'image/jpeg',
                            'image/jpg',
                            'image/png',
                        ]),
                    Textarea::make('notes')
                        ->rows(3)
                        ->maxLength(2000),
                ])
                ->action(function (array $data): void {
                    $record = $this->getRecord();

                    if (! in_array($record->status, ['pending', 'rejected'], true)) {
                        Notification::make()
                            ->title('Cannot submit payment proof')
                            ->body('Only pending or rejected payments can be submitted.')
                            ->danger()
                            ->send();

                        return;
                    }

                    try {
                        $changed = $record->submitProof([
                            'payment_date' => $data['payment_date'],
                            'payment_method' => $data['payment_method'],
                            'bank_name' => $data['bank_name'] ?? null,
                            'account_number' => $data['account_number'] ?? null,
                            'reference_number' => $data['reference_number'] ?? null,
                            'proof_file' => $data['proof_file'],
                            'notes' => $data['notes'] ?? null,
                        ], auth()->id());
                    } catch (RuntimeException $e) {
                        Notification::make()
                            ->title('Cannot submit payment proof')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();

                        return;
                    }

                    if (! $changed) {
                        Notification::make()
                            ->title('Payment proof already submitted')
                            ->warning()
                            ->send();

                        return;
                    }

                    Notification::make()
                        ->title('Payment proof submitted')
                        ->body('Your payment is now awaiting verification.')
                        ->success()
                        ->send();

                    $this->refreshFormData([
                        'payment_date',
                        'payment_method',
                        'bank_name',
                        'account_number',
                        'reference_number',
                        'proof_file',
                        'notes',
                        'status',
                        'rejection_reason',
                        'verified_at',
                        'verified_by',
                    ]);
                }),
        ];
    }
}
