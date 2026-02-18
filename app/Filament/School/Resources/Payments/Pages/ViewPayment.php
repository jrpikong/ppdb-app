<?php
declare(strict_types=1);
namespace App\Filament\School\Resources\Payments\Pages;

use App\Filament\School\Resources\Payments\PaymentResource;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewPayment extends ViewRecord
{
    protected static string $resource = PaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [

            Action::make('viewProof')
                ->label('View Proof')
                ->icon('heroicon-o-photo')
                ->color('info')
                ->url(fn (): ?string =>
                $this->getRecord()->proof_file
                    ? \Illuminate\Support\Facades\Storage::url($this->getRecord()->proof_file)
                    : null
                )
                ->openUrlInNewTab()
                ->visible(fn (): bool => filled($this->getRecord()->proof_file)),

            Action::make('verify')
                ->label('Verify Payment')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->form([
                    Textarea::make('notes')->label('Notes (optional)')->rows(2),
                ])
                ->action(function (array $data): void {
                    $this->getRecord()->verify(auth()->id(), $data['notes'] ?? null);
                    Notification::make()->title('Payment Verified')->success()->send();
                    $this->refreshFormData(['status', 'verified_by', 'verified_at']);
                })
                ->visible(fn (): bool => $this->getRecord()->status === 'submitted'),

            Action::make('reject')
                ->label('Reject Payment')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->form([
                    Textarea::make('rejection_reason')
                        ->label('Rejection Reason')->required()->rows(3),
                ])
                ->action(function (array $data): void {
                    $this->getRecord()->reject(auth()->id(), $data['rejection_reason']);
                    Notification::make()->title('Payment Rejected')->warning()->send();
                    $this->refreshFormData(['status', 'rejection_reason']);
                })
                ->visible(fn (): bool => $this->getRecord()->status === 'submitted'),

            Action::make('refund')
                ->label('Process Refund')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('info')
                ->form([
                    TextInput::make('refund_amount')
                        ->label('Refund Amount (IDR)')
                        ->numeric()->required()
                        ->default(fn () => $this->getRecord()->amount),
                    DatePicker::make('refund_date')
                        ->label('Refund Date')->required()->native(false)->default(today()),
                    Textarea::make('refund_reason')
                        ->label('Reason')->required()->rows(2),
                ])
                ->action(function (array $data): void {
                    $this->getRecord()->refund(
                        $data['refund_amount'],
                        $data['refund_reason'],
                    );
                    $this->getRecord()->update(['refund_date' => $data['refund_date']]);
                    Notification::make()->title('Refund Processed')->info()->send();
                    $this->refreshFormData(['status', 'refund_amount', 'refund_date']);
                })
                ->visible(fn (): bool => $this->getRecord()->status === 'verified'),

        ];
    }
}
