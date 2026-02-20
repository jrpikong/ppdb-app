<?php

declare(strict_types=1);

namespace App\Filament\School\Resources\Schedules\Tables;

use App\Support\ParentNotifier;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class SchedulesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                // Type badge
                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'observation' => '👀 Observation',
                        'test'        => '📝 Test',
                        'interview'   => '🗣️ Interview',
                        default       => ucfirst($state),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'observation' => 'info',
                        'test'        => 'warning',
                        'interview'   => 'primary',
                        default       => 'gray',
                    })
                    ->sortable(),

                // Applicant + app number
                TextColumn::make('application.student_first_name')
                    ->label('Applicant')
                    ->formatStateUsing(fn ($state, $record): string =>
                    trim($record->application?->student_first_name . ' ' .
                        $record->application?->student_last_name)
                    )
                    ->description(fn ($record): string =>
                        $record->application?->application_number ?? '—'
                    )
                    ->searchable(['application.student_first_name', 'application.student_last_name'])
                    ->sortable(),

                // Date + time stacked
                TextColumn::make('scheduled_date')
                    ->label('Date & Time')
                    ->date('d M Y')
                    ->description(fn ($record): string =>
                        \Carbon\Carbon::parse($record->scheduled_time)->format('H:i') .
                        ' (' . $record->duration_minutes . ' min)'
                    )
                    ->sortable()
                    ->color(fn ($record): string => match (true) {
                        $record->status === 'completed'                          => 'success',
                        $record->scheduled_date->isToday()                       => 'warning',
                        $record->scheduled_date->isPast()
                        && $record->status === 'scheduled'                   => 'danger',
                        default                                                   => 'gray',
                    }),

                // Interviewer
                TextColumn::make('interviewer.name')
                    ->label('Assigned To')
                    ->placeholder('—')
                    ->icon('heroicon-o-user-circle')
                    ->iconColor('gray'),

                // Location
                TextColumn::make('location')
                    ->label('Location')
                    ->placeholder('—')
                    ->formatStateUsing(fn ($state, $record): string =>
                    $record->is_online ? '🌐 Online' : ($state ?? '—')
                    )
                    ->toggleable(isToggledHiddenByDefault: true),

                // Status badge
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'scheduled'   => 'Scheduled',
                        'confirmed'   => 'Confirmed',
                        'completed'   => 'Completed',
                        'cancelled'   => 'Cancelled',
                        'rescheduled' => 'Rescheduled',
                        'no_show'     => 'No Show',
                        default       => ucfirst($state),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'scheduled'   => 'info',
                        'confirmed'   => 'primary',
                        'completed'   => 'success',
                        'cancelled'   => 'danger',
                        'rescheduled' => 'warning',
                        'no_show'     => 'gray',
                        default       => 'gray',
                    })
                    ->sortable(),

                // Score if completed
                TextColumn::make('score')
                    ->label('Score')
                    ->placeholder('—')
                    ->suffix('/100')
                    ->badge()
                    ->color(fn (?int $state): string => match (true) {
                        $state === null    => 'gray',
                        $state >= 80       => 'success',
                        $state >= 60       => 'warning',
                        default            => 'danger',
                    })
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

            ])

            ->defaultSort('scheduled_date', 'asc')

            ->filters([

                SelectFilter::make('type')
                    ->label('Session Type')
                    ->options([
                        'observation' => 'Observation Day',
                        'test'        => 'Assessment Test',
                        'interview'   => 'Parent Interview',
                    ])
                    ->native(false),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'scheduled'   => 'Scheduled',
                        'confirmed'   => 'Confirmed',
                        'completed'   => 'Completed',
                        'cancelled'   => 'Cancelled',
                        'no_show'     => 'No Show',
                    ])
                    ->native(false),

                TrashedFilter::make(),

            ])

            ->recordActions([

                ViewAction::make()->label(''),
                EditAction::make()->label(''),

                // Mark as Completed
                Action::make('complete')
                    ->label('Complete')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->form([
                        Textarea::make('result')
                            ->label('Session Notes / Result')
                            ->required()
                            ->placeholder('Summarise what happened during the session...')
                            ->rows(3),

                        \Filament\Forms\Components\TextInput::make('score')
                            ->label('Score (optional)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('/ 100'),

                        Select::make('recommendation')
                            ->label('Recommendation')
                            ->options([
                                'recommended'     => '✅ Recommended',
                                'not_recommended' => '❌ Not Recommended',
                                'pending'         => '⏳ Pending Decision',
                            ])
                            ->native(false),
                    ])
                    ->action(function ($record, array $data): void {
                        $record->update([
                            'status'       => 'completed',
                            'result'       => $data['result'],
                            'score'        => $data['score'] ?? null,
                            'recommendation' => $data['recommendation'] ?? 'pending',
                            'completed_at' => now(),
                            'completed_by' => auth()->id(),
                        ]);
                        ParentNotifier::scheduleUpdated($record->refresh(), 'completed');

                        Notification::make()
                            ->title('Session Completed')
                            ->body('Schedule marked as completed.')
                            ->success()
                            ->send();
                    })
                    ->visible(fn ($record): bool =>
                    in_array($record->status, ['scheduled', 'confirmed'])
                    ),

                // Cancel
                Action::make('cancel')
                    ->label('Cancel')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalDescription('This will cancel the schedule. The applicant should be notified separately.')
                    ->action(function ($record): void {
                        $record->update(['status' => 'cancelled']);
                        ParentNotifier::scheduleUpdated($record->refresh(), 'cancelled');

                        Notification::make()
                            ->title('Schedule Cancelled')
                            ->warning()
                            ->send();
                    })
                    ->visible(fn ($record): bool =>
                    in_array($record->status, ['scheduled', 'confirmed'])
                    ),

            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])

            ->emptyStateIcon('heroicon-o-calendar')
            ->emptyStateHeading('No Schedules Yet')
            ->emptyStateDescription('Create an interview or test schedule for applicants.')
            ->striped()
            ->paginated([15, 25, 50]);
    }
}
