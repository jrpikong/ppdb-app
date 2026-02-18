<?php
// ── FILE: Schemas/ScheduleInfolist.php ────────────────────────────────────

declare(strict_types=1);

namespace App\Filament\School\Resources\Schedules\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;

class ScheduleInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Session Overview')
                ->columns(3)
                ->schema([
                    TextEntry::make('type')
                        ->label('Session Type')
                        ->badge()
                        ->formatStateUsing(fn (string $state): string => match ($state) {
                            'observation' => '👀 Observation Day',
                            'test'        => '📝 Assessment Test',
                            'interview'   => '🗣️ Parent Interview',
                            default       => ucfirst($state),
                        })
                        ->color(fn (string $state): string => match ($state) {
                            'observation' => 'info',
                            'test'        => 'warning',
                            'interview'   => 'primary',
                            default       => 'gray',
                        }),

                    TextEntry::make('status')
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
                            'no_show'     => 'gray',
                            default       => 'warning',
                        }),

                    TextEntry::make('score')
                        ->label('Score')
                        ->placeholder('Not scored yet')
                        ->suffix(' / 100')
                        ->badge()
                        ->color(fn (?int $state): string => match(true) {
                            $state === null => 'gray',
                            $state >= 80   => 'success',
                            $state >= 60   => 'warning',
                            default        => 'danger',
                        }),
                ]),

            Section::make('Applicant')
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
                        ->badge()
                        ->color('primary')
                        ->copyable(),

                    TextEntry::make('application.user.name')
                        ->label('Parent / Guardian')
                        ->default(fn ($record): string =>
                            $record->application?->user?->phone ?? '—'
                        ),
                ]),

            Section::make('Schedule')
                ->icon('heroicon-o-calendar-days')
                ->columns(3)
                ->schema([
                    TextEntry::make('scheduled_date')
                        ->label('Date')
                        ->date('d M Y, l'),

                    TextEntry::make('scheduled_time')
                        ->label('Time')
                        ->formatStateUsing(fn ($state): string =>
                        \Carbon\Carbon::parse($state)->format('H:i')
                        ),

                    TextEntry::make('duration_minutes')
                        ->label('Duration')
                        ->suffix(' minutes'),

                    TextEntry::make('interviewer.name')
                        ->label('Assigned Staff')
                        ->placeholder('Not assigned')
                        ->icon('heroicon-o-user-circle'),

                    TextEntry::make('location')
                        ->label('Location')
                        ->placeholder('—')
                        ->formatStateUsing(fn ($state, $record): string =>
                        $record->is_online ? '🌐 Online Session' : ($state ?? '—')
                        ),

                    TextEntry::make('online_meeting_link')
                        ->label('Meeting Link')
                        ->placeholder('—')
                        ->url(fn ($record): ?string => $record->online_meeting_link)
                        ->openUrlInNewTab()
                        ->visible(fn ($record): bool => (bool) $record->is_online),
                ]),

            Section::make('Result & Notes')
                ->icon('heroicon-o-document-text')
                ->columns(2)
                ->schema([
                    TextEntry::make('recommendation')
                        ->label('Recommendation')
                        ->badge()
                        ->placeholder('—')
                        ->formatStateUsing(fn (?string $state): string => match ($state) {
                            'recommended'     => '✅ Recommended',
                            'not_recommended' => '❌ Not Recommended',
                            'pending'         => '⏳ Pending',
                            default           => '—',
                        })
                        ->color(fn (?string $state): string => match ($state) {
                            'recommended'     => 'success',
                            'not_recommended' => 'danger',
                            default           => 'gray',
                        }),

                    TextEntry::make('completed_at')
                        ->label('Completed At')
                        ->dateTime('d M Y, H:i')
                        ->placeholder('—'),

                    TextEntry::make('notes')
                        ->label('Pre-Session Notes')
                        ->placeholder('No notes')
                        ->columnSpanFull(),

                    TextEntry::make('result')
                        ->label('Session Result / Notes')
                        ->placeholder('Not completed yet')
                        ->columnSpanFull(),
                ])
                ->collapsible(),

        ]);
    }
}
