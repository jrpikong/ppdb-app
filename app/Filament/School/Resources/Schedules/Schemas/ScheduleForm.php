<?php

declare(strict_types=1);

namespace App\Filament\School\Resources\Schedules\Schemas;

use App\Models\Application;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class ScheduleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            // ── SECTION 1: Applicant & Type ───────────────────────────────
            Section::make('Schedule Details')
                ->icon('heroicon-o-calendar-days')
                ->columns(2)
                ->schema([

                    Select::make('application_id')
                        ->label('Applicant')
                        ->required()
                        ->searchable()
                        ->native(false)
                        ->options(function (): array {
                            $schoolId = Filament::getTenant()?->id;
                            if (!$schoolId) return [];

                            return Application::query()
                                ->where('school_id', $schoolId)
                                ->whereIn('status', [
                                    'submitted', 'under_review',
                                    'documents_verified', 'interview_scheduled',
                                ])
                                ->with('user')
                                ->get()
                                ->mapWithKeys(fn ($app): array => [
                                    $app->id => "[{$app->application_number}] " .
                                        ($app->student_first_name . ' ' . $app->student_last_name),
                                ])
                                ->toArray();
                        })
                        ->helperText('Only active applications are shown.')
                        ->columnSpanFull(),

                    Select::make('type')
                        ->label('Schedule Type')
                        ->required()
                        ->native(false)
                        ->options([
                            'observation' => '👀 Observation Day',
                            'test'        => '📝 Assessment Test',
                            'interview'   => '🗣️ Parent Interview',
                        ])
                        ->helperText('Select the type of session being scheduled.'),

                    Select::make('interviewer_id')
                        ->label('Assigned Staff')
                        ->searchable()
                        ->native(false)
                        ->options(function (): array {
                            $schoolId = Filament::getTenant()?->id;
                            if (!$schoolId) return [];

                            return \App\Models\User::query()
                                ->where('users.school_id', $schoolId)
                                ->where('is_active', true)
                                ->whereHas('roles', fn (Builder $q) =>
                                $q->whereIn('name', ['school_admin', 'admission_admin', 'super_admin'])
                                    ->where('users.school_id', $schoolId)
                                )
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->toArray();
                        })
                        ->placeholder('Not assigned'),

                ]),

            // ── SECTION 2: Date, Time & Duration ──────────────────────────
            Section::make('Date & Time')
                ->icon('heroicon-o-clock')
                ->columns(3)
                ->schema([

                    DatePicker::make('scheduled_date')
                        ->label('Date')
                        ->required()
                        ->native(false)
                        ->displayFormat('d M Y')
                        ->minDate(today())
                        ->prefixIcon('heroicon-o-calendar'),

                    TimePicker::make('scheduled_time')
                        ->label('Time')
                        ->required()
                        ->native(false)
                        ->displayFormat('H:i')
                        ->seconds(false)
                        ->prefixIcon('heroicon-o-clock'),

                    TextInput::make('duration_minutes')
                        ->label('Duration')
                        ->numeric()
                        ->required()
                        ->default(60)
                        ->minValue(15)
                        ->maxValue(480)
                        ->suffix('minutes')
                        ->prefixIcon('heroicon-o-arrow-path'),

                ]),

            // ── SECTION 3: Location ───────────────────────────────────────
            Section::make('Location')
                ->icon('heroicon-o-map-pin')
                ->columns(2)
                ->schema([

                    Toggle::make('is_online')
                        ->label('Online Session')
                        ->helperText('Toggle if this session is held online.')
                        ->default(false)
                        ->live()
                        ->inline(false),

                    TextInput::make('location')
                        ->label('Room / Building')
                        ->placeholder('e.g. Meeting Room 2, 2nd Floor')
                        ->prefixIcon('heroicon-o-building-office')
                        ->visible(fn (Get $get): bool => !$get('is_online')),

                    TextInput::make('online_meeting_link')
                        ->label('Meeting Link')
                        ->url()
                        ->placeholder('https://meet.google.com/...')
                        ->prefixIcon('heroicon-o-video-camera')
                        ->visible(fn (Get $get): bool => (bool) $get('is_online')),

                    Textarea::make('location_details')
                        ->label('Additional Location Details')
                        ->placeholder('Parking info, entrance directions, etc.')
                        ->rows(2)
                        ->columnSpanFull(),

                ]),

            // ── SECTION 4: Notes ──────────────────────────────────────────
            Section::make('Notes & Instructions')
                ->icon('heroicon-o-document-text')
                ->collapsed()
                ->schema([

                    Textarea::make('notes')
                        ->label('Pre-Session Notes')
                        ->placeholder('Instructions for the applicant before the session...')
                        ->rows(3)
                        ->columnSpanFull(),

                ]),

        ]);
    }
}
