<?php
declare(strict_types=1);
namespace App\Filament\School\Resources\Enrollments\Pages;

use App\Filament\School\Resources\Enrollments\EnrollmentResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewEnrollment extends ViewRecord
{
    protected static string $resource = EnrollmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),

            Action::make('activate')
                ->label('Activate Enrollment')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->action(function (): void {
                    $this->getRecord()->activate();
                    Notification::make()->title('Enrollment Activated')->success()->send();
                    $this->refreshFormData(['status']);
                })
                ->visible(fn (): bool => $this->getRecord()->status === 'enrolled'),

            Action::make('withdraw')
                ->label('Withdraw Enrollment')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->form([
                    DatePicker::make('withdrawal_date')
                        ->label('Withdrawal Date')
                        ->required()
                        ->native(false)
                        ->default(today())
                        ->maxDate(today()),

                    Textarea::make('withdrawal_reason')
                        ->label('Reason for Withdrawal')
                        ->required()
                        ->rows(3),
                ])
                ->action(function (array $data): void {
                    $this->getRecord()->update([
                        'status'            => 'withdrawn',
                        'withdrawal_date'   => $data['withdrawal_date'],
                        'withdrawal_reason' => $data['withdrawal_reason'],
                    ]);
                    Notification::make()->title('Enrollment Withdrawn')->warning()->send();
                    $this->refreshFormData(['status', 'withdrawal_date', 'withdrawal_reason']);
                })
                ->visible(fn (): bool =>
                in_array($this->getRecord()->status, ['enrolled', 'active'])
                ),

            DeleteAction::make(),
        ];
    }
}
