<?php
declare(strict_types=1);
namespace App\Filament\School\Resources\Schedules\Pages;

use App\Filament\School\Resources\Schedules\ScheduleResource;
use App\Support\ParentNotifier;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewSchedule extends ViewRecord
{
    protected static string $resource = ScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),

            Action::make('complete')
                ->label('Mark as Completed')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->form([
                    Textarea::make('result')
                        ->label('Session Result / Notes')
                        ->required()
                        ->rows(3),

                    \Filament\Forms\Components\TextInput::make('score')
                        ->label('Score (optional)')
                        ->numeric()->minValue(0)->maxValue(100)->suffix('/ 100'),

                    Select::make('recommendation')
                        ->label('Recommendation')
                        ->options([
                            'recommended'     => '✅ Recommended',
                            'not_recommended' => '❌ Not Recommended',
                            'pending'         => '⏳ Pending Decision',
                        ])
                        ->native(false),
                ])
                ->action(function (array $data): void {
                    $this->getRecord()->update([
                        'status'         => 'completed',
                        'result'         => $data['result'],
                        'score'          => $data['score'] ?? null,
                        'recommendation' => $data['recommendation'] ?? 'pending',
                        'completed_at'   => now(),
                        'completed_by'   => auth()->id(),
                    ]);
                    ParentNotifier::scheduleUpdated($this->getRecord()->refresh(), 'completed');
                    Notification::make()->title('Session Completed')->success()->send();
                    $this->refreshFormData(['status', 'result', 'score', 'recommendation']);
                })
                ->visible(fn (): bool => in_array($this->getRecord()->status, ['scheduled', 'confirmed'])),

            Action::make('cancel')
                ->label('Cancel Schedule')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->action(function (): void {
                    $this->getRecord()->update(['status' => 'cancelled']);
                    ParentNotifier::scheduleUpdated($this->getRecord()->refresh(), 'cancelled');
                    Notification::make()->title('Schedule Cancelled')->warning()->send();
                    $this->refreshFormData(['status']);
                })
                ->visible(fn (): bool => in_array($this->getRecord()->status, ['scheduled', 'confirmed'])),

            DeleteAction::make(),
        ];
    }
}
