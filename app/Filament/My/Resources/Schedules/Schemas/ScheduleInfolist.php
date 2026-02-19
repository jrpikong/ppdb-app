<?php

declare(strict_types=1);

namespace App\Filament\My\Resources\Schedules\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ScheduleInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Schedule Summary')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('type')
                            ->label('Type')
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
                        TextEntry::make('status')
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
                            }),
                        TextEntry::make('application.application_number')
                            ->label('Application #')
                            ->badge()
                            ->color('gray'),
                    ]),

                Section::make('Session Details')
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('scheduled_date')
                                ->label('Date')
                                ->date('d M Y'),
                            TextEntry::make('scheduled_time')
                                ->label('Time')
                                ->formatStateUsing(fn (?string $state): string => $state ? date('H:i', strtotime($state)) : '-'),
                            TextEntry::make('duration_minutes')
                                ->label('Duration')
                                ->formatStateUsing(fn (?int $state): string => $state ? "{$state} minutes" : '-'),
                            TextEntry::make('interviewer.name')
                                ->label('Interviewer')
                                ->placeholder('Will be assigned by school'),
                            TextEntry::make('location')
                                ->label('Location')
                                ->placeholder('-')
                                ->formatStateUsing(fn (?string $state, $record): string => $record->is_online ? 'Online Meeting' : ($state ?: '-')),
                            TextEntry::make('online_meeting_link')
                                ->label('Meeting Link')
                                ->placeholder('-')
                                ->url(fn ($record): ?string => $record->is_online ? $record->online_meeting_link : null)
                                ->openUrlInNewTab(),
                        ]),
                    ]),

                Section::make('Notes')
                    ->schema([
                        TextEntry::make('notes')
                            ->label('School / Schedule Notes')
                            ->placeholder('-')
                            ->columnSpanFull(),
                        TextEntry::make('result')
                            ->label('Session Result')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}

