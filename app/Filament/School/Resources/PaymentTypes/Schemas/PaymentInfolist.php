<?php
declare(strict_types=1);
namespace App\Filament\School\Resources\Payments\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;

class PaymentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Payment Overview')
                ->columns(3)
                ->schema([
                    TextEntry::make('transaction_code')
                        ->label('Transaction Code')
                        ->badge()->color('primary')->copyable()->weight(FontWeight::Bold),

                    TextEntry::make('status')
                        ->label('Status')
                        ->badge()
                        ->formatStateUsing(fn (string $state): string => match ($state) {
                            'pending'   => 'Pending',
                            'submitted' => 'Awaiting Verification',
                            'verified'  => 'Verified',
                            'rejected'  => 'Rejected',
                            'refunded'  => 'Refunded',
                            default     => ucfirst($state),
                        })
                        ->color(fn (string $state): string => match ($state) {
                            'submitted' => 'warning',
                            'verified'  => 'success',
                            'rejected'  => 'danger',
                            'refunded'  => 'info',
                            default     => 'gray',
                        }),

                    TextEntry::make('amount')
                        ->label('Amount')
                        ->formatStateUsing(fn ($state): string =>
                            'IDR ' . number_format($state, 0, ',', '.')
                        )
                        ->weight(FontWeight::Bold)
                        ->color('success'),
                ]),

            Section::make('Applicant & Payment Type')
                ->icon('heroicon-o-user')
                ->columns(3)
                ->schema([
                    TextEntry::make('application.student_first_name')
                        ->label('Student Name')
                        ->formatStateUsing(fn ($state, $record): string =>
                        trim($record->application?->student_first_name . ' ' .
                            $record->application?->student_last_name)
                        )
                        ->weight(FontWeight::SemiBold),

                    TextEntry::make('application.application_number')
                        ->label('Application #')
                        ->badge()->color('primary')->copyable(),

                    TextEntry::make('paymentType.name')
                        ->label('Payment Type')
                        ->description(fn ($record): string =>
                        ucwords(str_replace('_', ' ', $record->paymentType?->payment_stage ?? ''))
                        ),
                ]),

            Section::make('Payment Details')
                ->icon('heroicon-o-banknotes')
                ->columns(3)
                ->schema([
                    TextEntry::make('payment_date')->label('Payment Date')->date('d M Y'),
                    TextEntry::make('payment_method')->label('Method')->placeholder('—'),
                    TextEntry::make('bank_name')->label('Bank')->placeholder('—'),
                    TextEntry::make('account_number')->label('Account Number')->placeholder('—')->copyable(),
                    TextEntry::make('reference_number')->label('Reference Number')->placeholder('—')->copyable(),

                    TextEntry::make('proof_file')
                        ->label('Payment Proof')
                        ->formatStateUsing(fn (?string $state): string =>
                        $state ? '🔗 View payment proof' : 'No proof uploaded'
                        )
                        ->url(fn ($record): ?string =>
                        $record->proof_file
                            ? \Illuminate\Support\Facades\Storage::url($record->proof_file)
                            : null
                        )
                        ->openUrlInNewTab()
                        ->color('primary'),
                ]),

            Section::make('Verification')
                ->icon('heroicon-o-shield-check')
                ->columns(2)
                ->schema([
                    TextEntry::make('verifier.name')->label('Verified By')->placeholder('Not yet verified'),
                    TextEntry::make('verified_at')->label('Verified At')->dateTime('d M Y, H:i')->placeholder('—'),
                    TextEntry::make('notes')->label('Notes')->placeholder('—')->columnSpanFull(),
                    TextEntry::make('rejection_reason')
                        ->label('Rejection Reason')->placeholder('—')->color('danger')
                        ->visible(fn ($record): bool => $record->status === 'rejected')
                        ->columnSpanFull(),
                ]),

            Section::make('Refund Details')
                ->icon('heroicon-o-arrow-uturn-left')
                ->columns(3)
                ->visible(fn ($record): bool => $record->status === 'refunded')
                ->schema([
                    TextEntry::make('refund_amount')
                        ->label('Refund Amount')
                        ->formatStateUsing(fn ($state): string =>
                            'IDR ' . number_format($state, 0, ',', '.')
                        ),
                    TextEntry::make('refund_date')->label('Refund Date')->date('d M Y'),
                    TextEntry::make('refund_reason')->label('Refund Reason')->columnSpanFull(),
                ]),

        ]);
    }
}
