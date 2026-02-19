<?php

declare(strict_types=1);

namespace App\Filament\My\Resources\Schedules\Pages;

use App\Filament\My\Resources\Schedules\ScheduleResource;
use App\Models\ActivityLog;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewSchedule extends ViewRecord
{
    protected static string $resource = ScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('confirmAttendance')
                ->label('Confirm Attendance')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn (): bool => in_array($this->getRecord()->status, ['scheduled', 'rescheduled'], true) && ! $this->getRecord()->scheduledDateTime->isPast())
                ->action(function (): void {
                    $record = $this->getRecord();

                    if (! in_array($record->status, ['scheduled', 'rescheduled'], true) || $record->scheduledDateTime->isPast()) {
                        Notification::make()
                            ->title('Cannot confirm attendance')
                            ->body('This schedule can no longer be confirmed.')
                            ->danger()
                            ->send();

                        return;
                    }

                    $record->update([
                        'status' => 'confirmed',
                    ]);

                    ActivityLog::logActivity(
                        description: 'Parent confirmed schedule attendance.',
                        subject: $record,
                        logName: 'schedule',
                        event: 'confirmed'
                    );

                    Notification::make()
                        ->title('Attendance confirmed')
                        ->body('School has been notified that you will attend this schedule.')
                        ->success()
                        ->send();

                    $this->refreshFormData(['status', 'notes', 'updated_at']);
                }),

            Action::make('requestReschedule')
                ->label('Request Reschedule')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->schema([
                    DatePicker::make('preferred_date')
                        ->label('Preferred Date')
                        ->required()
                        ->native(false)
                        ->minDate(today()),
                    TimePicker::make('preferred_time')
                        ->label('Preferred Time')
                        ->required()
                        ->seconds(false)
                        ->native(false),
                    Textarea::make('reason')
                        ->label('Reason')
                        ->required()
                        ->maxLength(2000)
                        ->rows(4),
                ])
                ->visible(fn (): bool => in_array($this->getRecord()->status, ['scheduled', 'confirmed', 'rescheduled'], true) && ! $this->getRecord()->scheduledDateTime->isPast())
                ->action(function (array $data): void {
                    $record = $this->getRecord();

                    if (! in_array($record->status, ['scheduled', 'confirmed', 'rescheduled'], true) || $record->scheduledDateTime->isPast()) {
                        Notification::make()
                            ->title('Cannot request reschedule')
                            ->body('This schedule can no longer be rescheduled.')
                            ->danger()
                            ->send();

                        return;
                    }

                    $requestNote = sprintf(
                        '[Parent Reschedule Request %s] Preferred: %s %s. Reason: %s',
                        now()->format('Y-m-d H:i'),
                        $data['preferred_date'],
                        $data['preferred_time'],
                        trim($data['reason'])
                    );

                    $record->update([
                        'status' => 'rescheduled',
                        'notes' => trim(implode("\n\n", array_filter([$record->notes, $requestNote]))),
                    ]);

                    ActivityLog::logActivity(
                        description: 'Parent requested schedule reschedule.',
                        subject: $record,
                        logName: 'schedule',
                        event: 'reschedule_requested',
                        properties: [
                            'preferred_date' => $data['preferred_date'],
                            'preferred_time' => $data['preferred_time'],
                            'reason' => $data['reason'],
                        ]
                    );

                    Notification::make()
                        ->title('Reschedule request submitted')
                        ->body('School will review your request and update the schedule.')
                        ->success()
                        ->send();

                    $this->refreshFormData(['status', 'notes', 'updated_at']);
                }),
        ];
    }
}

