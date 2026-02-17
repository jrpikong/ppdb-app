<?php

declare(strict_types=1);

namespace App\Filament\School\Resources\Applications\Schemas;

use App\Filament\School\Resources\Applications;
use Filament\Actions;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;

class ApplicationInfolist extends ViewRecord
{
    protected static string $resource = Applications\ApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Application Overview')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('application_number')
                                    ->label('Application Number')
                                    ->badge()
                                    ->color('primary')
                                    ->weight(FontWeight::Bold)
                                    ->copyable()
                                    ->icon('heroicon-o-document-text'),

                                TextEntry::make('status')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'draft' => 'gray',
                                        'submitted' => 'info',
                                        'under_review', 'documents_verified' => 'warning',
                                        'interview_scheduled', 'interview_completed' => 'purple',
                                        'payment_pending', 'payment_verified' => 'indigo',
                                        'accepted', 'enrolled' => 'success',
                                        'rejected' => 'danger',
                                        'waitlisted' => 'orange',
                                        'withdrawn' => 'gray',
                                        default => 'gray',
                                    })
                                    ->formatStateUsing(fn (string $state): string => (string)str($state)->replace('_', ' ')->title()),

                                TextEntry::make('priority_score')
                                    ->label('Priority Score')
                                    ->badge()
                                    ->suffix(' / 100')
                                    ->color(fn ($state) => match (true) {
                                        $state >= 80 => 'success',
                                        $state >= 60 => 'warning',
                                        $state < 60 => 'danger',
                                        default => 'gray',
                                    })
                                    ->placeholder('Not scored'),
                            ]),

                        Grid::make(3)
                            ->schema([
                                TextEntry::make('admissionPeriod.name')
                                    ->label('Admission Period')
                                    ->icon('heroicon-o-calendar'),

                                TextEntry::make('level.name')
                                    ->label('Level/Grade Applying')
                                    ->badge()
                                    ->color('info')
                                    ->icon('heroicon-o-academic-cap'),

                                TextEntry::make('assignedTo.name')
                                    ->label('Assigned Reviewer')
                                    ->icon('heroicon-o-user')
                                    ->placeholder('Not assigned yet'),
                            ]),

                        TextEntry::make('status_notes')
                            ->label('Status Notes')
                            ->placeholder('No notes')
                            ->columnSpanFull(),
                    ])
                    ->columns(1)
                    ->collapsible(),

                Section::make('Student Information')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('student_first_name')
                                    ->label('First Name')
                                    ->weight(FontWeight::Bold),

                                TextEntry::make('student_middle_name')
                                    ->label('Middle Name')
                                    ->placeholder('N/A'),

                                TextEntry::make('student_last_name')
                                    ->label('Last Name')
                                    ->weight(FontWeight::Bold),
                            ]),

                        Grid::make(3)
                            ->schema([
                                TextEntry::make('student_preferred_name')
                                    ->label('Preferred Name')
                                    ->placeholder('N/A'),

                                TextEntry::make('gender')
                                    ->badge()
                                    ->color(fn (string $state): string => $state === 'male' ? 'blue' : 'pink')
                                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),

                                TextEntry::make('birth_date')
                                    ->label('Birth Date')
                                    ->date('d F Y')
                                    ->suffix(fn ($record) => ' (' . $record->age . ' years old)'),
                            ]),

                        Grid::make(3)
                            ->schema([
                                TextEntry::make('birth_place')
                                    ->label('Birth Place')
                                    ->placeholder('N/A'),

                                TextEntry::make('nationality')
                                    ->label('Nationality')
                                    ->icon('heroicon-o-flag'),

                                TextEntry::make('passport_number')
                                    ->label('Passport Number')
                                    ->placeholder('N/A')
                                    ->copyable(),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextEntry::make('email')
                                    ->label('Student Email')
                                    ->icon('heroicon-o-envelope')
                                    ->placeholder('N/A')
                                    ->copyable(),

                                TextEntry::make('phone')
                                    ->label('Student Phone')
                                    ->icon('heroicon-o-phone')
                                    ->placeholder('N/A')
                                    ->copyable(),
                            ]),

                        TextEntry::make('languages_spoken')
                            ->label('Languages Spoken')
                            ->placeholder('N/A')
                            ->columnSpanFull(),

                        TextEntry::make('interests_hobbies')
                            ->label('Interests & Hobbies')
                            ->placeholder('N/A')
                            ->columnSpanFull(),
                    ])
                    ->columns(1)
                    ->collapsible(),

                Section::make('Current Address')
                    ->schema([
                        TextEntry::make('current_address')
                            ->label('Street Address')
                            ->placeholder('N/A')
                            ->columnSpanFull(),

                        Grid::make(3)
                            ->schema([
                                TextEntry::make('current_city')
                                    ->label('City')
                                    ->placeholder('N/A'),

                                TextEntry::make('current_country')
                                    ->label('Country')
                                    ->icon('heroicon-o-globe-alt')
                                    ->placeholder('N/A'),

                                TextEntry::make('current_postal_code')
                                    ->label('Postal Code')
                                    ->placeholder('N/A'),
                            ]),
                    ])
                    ->columns(1)
                    ->collapsible(),

                Section::make('Parent/Guardian Information')
                    ->schema([
                        TextEntry::make('user.name')
                            ->label('Primary Contact')
                            ->icon('heroicon-o-user')
                            ->weight(FontWeight::Bold),

                        TextEntry::make('user.email')
                            ->label('Contact Email')
                            ->icon('heroicon-o-envelope')
                            ->copyable(),

                        TextEntry::make('user.phone')
                            ->label('Contact Phone')
                            ->icon('heroicon-o-phone')
                            ->placeholder('N/A')
                            ->copyable(),
                    ])
                    ->columns(3)
                    ->collapsible(),

                Section::make('Previous School Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('previous_school_name')
                                    ->label('School Name')
                                    ->placeholder('N/A'),

                                TextEntry::make('previous_school_country')
                                    ->label('Country')
                                    ->icon('heroicon-o-globe-alt')
                                    ->placeholder('N/A'),
                            ]),

                        Grid::make(3)
                            ->schema([
                                TextEntry::make('current_grade_level')
                                    ->label('Current Grade')
                                    ->badge()
                                    ->placeholder('N/A'),

                                TextEntry::make('previous_school_start_date')
                                    ->label('Start Date')
                                    ->date('M Y')
                                    ->placeholder('N/A'),

                                TextEntry::make('previous_school_end_date')
                                    ->label('End Date')
                                    ->date('M Y')
                                    ->placeholder('N/A'),
                            ]),
                    ])
                    ->columns(1)
                    ->collapsible(),

                Section::make('Special Needs & Requirements')
                    ->schema([
                        TextEntry::make('special_needs')
                            ->label('Special Needs')
                            ->placeholder('None specified')
                            ->columnSpanFull(),

                        TextEntry::make('learning_support_required')
                            ->label('Learning Support Required')
                            ->placeholder('None specified')
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),

                Section::make('Assessment Requirements')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                IconEntry::make('requires_observation')
                                    ->label('Observation Required')
                                    ->boolean()
                                    ->trueIcon('heroicon-o-check-circle')
                                    ->falseIcon('heroicon-o-x-circle')
                                    ->trueColor('success')
                                    ->falseColor('gray'),

                                IconEntry::make('requires_test')
                                    ->label('Test Required')
                                    ->boolean()
                                    ->trueIcon('heroicon-o-check-circle')
                                    ->falseIcon('heroicon-o-x-circle')
                                    ->trueColor('success')
                                    ->falseColor('gray'),

                                IconEntry::make('requires_interview')
                                    ->label('Interview Required')
                                    ->boolean()
                                    ->trueIcon('heroicon-o-check-circle')
                                    ->falseIcon('heroicon-o-x-circle')
                                    ->trueColor('success')
                                    ->falseColor('gray'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Internal Notes & Decision')
                    ->schema([
                        TextEntry::make('interview_notes')
                            ->label('Interview Notes')
                            ->placeholder('No notes yet')
                            ->columnSpanFull(),

                        TextEntry::make('rejection_reason')
                            ->label('Rejection Reason')
                            ->placeholder('N/A')
                            ->visible(fn ($record) => $record->status === 'rejected')
                            ->color('danger')
                            ->columnSpanFull(),

                        TextEntry::make('internal_notes')
                            ->label('Internal Notes')
                            ->placeholder('No internal notes')
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),

                Section::make('Timeline')
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Created')
                                    ->dateTime('d M Y H:i')
                                    ->icon('heroicon-o-plus-circle'),

                                TextEntry::make('submitted_at')
                                    ->label('Submitted')
                                    ->dateTime('d M Y H:i')
                                    ->icon('heroicon-o-paper-airplane')
                                    ->placeholder('Not submitted'),

                                TextEntry::make('reviewed_at')
                                    ->label('Reviewed')
                                    ->dateTime('d M Y H:i')
                                    ->icon('heroicon-o-eye')
                                    ->placeholder('Not reviewed'),

                                TextEntry::make('decision_made_at')
                                    ->label('Decision Made')
                                    ->dateTime('d M Y H:i')
                                    ->icon('heroicon-o-check-circle')
                                    ->placeholder('Pending decision'),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextEntry::make('enrolled_at')
                                    ->label('Enrolled')
                                    ->dateTime('d M Y H:i')
                                    ->icon('heroicon-o-academic-cap')
                                    ->color('success')
                                    ->visible(fn ($record) => $record->enrolled_at)
                                    ->placeholder('Not enrolled'),

                                TextEntry::make('updated_at')
                                    ->label('Last Updated')
                                    ->dateTime('d M Y H:i')
                                    ->icon('heroicon-o-clock'),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}
