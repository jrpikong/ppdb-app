<?php

declare(strict_types=1);

namespace App\Filament\My\Resources\Schedules\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SchedulesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('application.application_number')
                    ->label('Application #')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'observation' => 'Observation',
                        'test' => 'Test',
                        'interview' => 'Interview',
                        default => ucfirst($state),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'observation' => 'info',
                        'test' => 'warning',
                        'interview' => 'primary',
                        default => 'gray',
                    }),

                TextColumn::make('scheduled_date')
                    ->label('Date')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('scheduled_time')
                    ->label('Time')
                    ->formatStateUsing(fn (?string $state): string => $state ? date('H:i', strtotime($state)) : '-')
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'scheduled' => 'Scheduled',
                        'confirmed' => 'Confirmed',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                        'rescheduled' => 'Rescheduled',
                        'no_show' => 'No Show',
                        default => ucfirst($state),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'scheduled' => 'info',
                        'confirmed' => 'success',
                        'completed' => 'gray',
                        'cancelled' => 'danger',
                        'rescheduled' => 'warning',
                        'no_show' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('location')
                    ->label('Location')
                    ->placeholder('-')
                    ->formatStateUsing(fn (?string $state, $record): string => $record->is_online ? 'Online Meeting' : ($state ?: '-'))
                    ->toggleable(),

                TextColumn::make('interviewer.name')
                    ->label('Interviewer')
                    ->placeholder('-')
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'observation' => 'Observation',
                        'test' => 'Test',
                        'interview' => 'Interview',
                    ])
                    ->native(false),
                SelectFilter::make('status')
                    ->options([
                        'scheduled' => 'Scheduled',
                        'confirmed' => 'Confirmed',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                        'rescheduled' => 'Rescheduled',
                        'no_show' => 'No Show',
                    ])
                    ->native(false),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->defaultSort('scheduled_date', 'asc')
            ->emptyStateHeading('No schedules yet')
            ->emptyStateDescription('Your interview or test schedules will appear here.');
    }
}

