<?php
declare(strict_types=1);
namespace App\Filament\School\Resources\Payments\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\ViewAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class PaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                // Transaction code
                TextColumn::make('transaction_code')
                    ->label('Transaction')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('semibold')
                    ->badge()
                    ->color('gray'),

                // Applicant
                TextColumn::make('application.student_first_name')
                    ->label('Applicant')
                    ->formatStateUsing(fn ($state, $record): string =>
                    trim($record->application?->student_first_name . ' ' .
                        $record->application?->student_last_name)
                    )
                    ->description(fn ($record): string =>
                        $record->application?->application_number ?? '—'
                    )
                    ->searchable()
                    ->sortable(),

                // Payment type + stage
                TextColumn::make('paymentType.name')
                    ->label('Payment Type')
                    ->description(fn ($record): string => match ($record->paymentType?->payment_stage) {
                        'pre_submission'  => '📋 Pre-Submission',
                        'post_acceptance' => '✅ Post-Acceptance',
                        'enrollment'      => '🎓 Enrollment',
                        default           => '—',
                    })
                    ->searchable(),

                // Amount
                TextColumn::make('amount')
                    ->label('Amount')
                    ->formatStateUsing(fn ($state): string =>
                        'IDR ' . number_format($state, 0, ',', '.')
                    )
                    ->sortable()
                    ->weight('semibold')
                    ->color('success'),

                // Payment date
                TextColumn::make('payment_date')
                    ->label('Paid On')
                    ->date('d M Y')
                    ->sortable(),

                // Status
                TextColumn::make('status')
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
                        'pending'   => 'gray',
                        'submitted' => 'warning',
                        'verified'  => 'success',
                        'rejected'  => 'danger',
                        'refunded'  => 'info',
                        default     => 'gray',
                    })
                    ->sortable(),

                // Verified by
                TextColumn::make('verifier.name')
                    ->label('Verified By')
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('verified_at')
                    ->label('Verified At')
                    ->dateTime('d M Y, H:i')
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

            ])

            ->defaultSort('created_at', 'desc')

            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending'   => 'Pending',
                        'submitted' => 'Awaiting Verification',
                        'verified'  => 'Verified',
                        'rejected'  => 'Rejected',
                        'refunded'  => 'Refunded',
                    ])
                    ->native(false),

                SelectFilter::make('payment_type_id')
                    ->label('Payment Type')
                    ->options(fn (): array =>
                    \App\Models\PaymentType::where('school_id', Filament::getTenant()?->id)
                        ->where('is_active', true)
                        ->pluck('name', 'id')
                        ->toArray()
                    )
                    ->native(false),

                TrashedFilter::make(),
            ])

            ->recordActions([

                // View proof in new tab
                Action::make('viewProof')
                    ->label('View Proof')
                    ->icon('heroicon-o-photo')
                    ->color('info')
                    ->url(fn ($record): ?string =>
                    $record->proof_file
                        ? \Illuminate\Support\Facades\Storage::url($record->proof_file)
                        : null
                    )
                    ->openUrlInNewTab()
                    ->visible(fn ($record): bool => filled($record->proof_file)),

                // Verify
                Action::make('verify')
                    ->label('Verify')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Verify Payment')
                    ->modalDescription(fn ($record): string =>
                        "Verify IDR " . number_format($record->amount, 0, ',', '.') .
                        " from {$record->application?->student_first_name}?"
                    )
                    ->schema([
                        Textarea::make('notes')
                            ->label('Notes (optional)')
                            ->rows(2),
                    ])
                    ->action(function ($record, array $data): void {
                        $record->verify(auth()->id(), $data['notes'] ?? null);
                        Notification::make()->title('Payment Verified')->success()->send();
                    })
                    ->visible(fn ($record): bool => $record->status === 'submitted'),

                // Reject
                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->form([
                        Textarea::make('rejection_reason')
                            ->label('Rejection Reason')
                            ->required()
                            ->placeholder('Wrong amount, unclear proof, etc...')
                            ->rows(3),
                    ])
                    ->action(function ($record, array $data): void {
                        $record->reject(auth()->id(), $data['rejection_reason']);
                        Notification::make()->title('Payment Rejected')->warning()->send();
                    })
                    ->visible(fn ($record): bool => $record->status === 'submitted'),

                ViewAction::make()->label(''),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('verifySelected')
                        ->label('Verify Selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $count = 0;
                            foreach ($records as $record) {
                                if ($record->status === 'submitted') {
                                    $record->verify(auth()->id());
                                    $count++;
                                }
                            }
                            Notification::make()->title("{$count} Payment(s) Verified")->success()->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])

            ->emptyStateIcon('heroicon-o-banknotes')
            ->emptyStateHeading('No Payments Found')
            ->emptyStateDescription('Payment submissions from applicants will appear here.')
            ->striped()
            ->paginated([15, 25, 50]);
    }
}
