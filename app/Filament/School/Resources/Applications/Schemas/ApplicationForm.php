<?php

namespace App\Filament\School\Resources\Applications\Schemas;

use App\Models\AdmissionPeriod;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class ApplicationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Application Information')
                    ->schema([
                        Select::make('admission_period_id')
                            ->label('Admission Period')
                            ->relationship(
                                name: 'admissionPeriod',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn (Builder $query) => $query
                                    ->where('school_id', Filament::getTenant()->id)
                                    ->where('is_active', true)
                            )
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function (Set $set, $state) {
                                if ($state) {
                                    $period = AdmissionPeriod::find($state);
                                    $set('academic_year_id', $period?->academic_year_id);
                                    $set('level_id', $period?->level_id);
                                }
                            })
                            ->columnSpanFull(),

                        Select::make('level_id')
                            ->label('Level/Grade Applying For')
                            ->relationship(
                                name: 'level',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn (Builder $query) => $query
                                    ->where('school_id', Filament::getTenant()->id)
                                    ->where('is_active', true)
//                                    ->orderBy('sequence')
                            )
                            ->required()
                            ->searchable()
                            ->preload()
                            ->columnSpanFull(),

                        Select::make('status')
                            ->label('Application Status')
                            ->options([
                                'draft' => 'Draft',
                                'submitted' => 'Submitted',
                                'under_review' => 'Under Review',
                                'documents_verified' => 'Documents Verified',
                                'interview_scheduled' => 'Interview Scheduled',
                                'interview_completed' => 'Interview Completed',
                                'payment_pending' => 'Payment Pending',
                                'payment_verified' => 'Payment Verified',
                                'accepted' => 'Accepted',
                                'rejected' => 'Rejected',
                                'waitlisted' => 'Waitlisted',
                                'enrolled' => 'Enrolled',
                                'withdrawn' => 'Withdrawn',
                            ])
                            ->default('draft')
                            ->required()
                            ->columnSpan(1),

                        Select::make('assigned_to')
                            ->label('Assigned Reviewer')
                            ->relationship(
                                name: 'assignedTo',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn (Builder $query) => $query
                                    ->where('school_id', Filament::getTenant()->id)
                                    ->where('is_active', true)
                                    ->whereHas('roles', function ($q) {
                                        $q->whereIn('name', ['school_admin', 'admission_admin']);
                                    })
                            )
                            ->searchable()
                            ->preload()
                            ->columnSpan(1),

                        TextInput::make('priority_score')
                            ->label('Priority Score')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('/ 100')
                            ->columnSpan(1),

                        Textarea::make('status_notes')
                            ->label('Status Notes')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Section::make('Student Information')
                    ->schema([
                        TextInput::make('student_first_name')
                            ->label('First Name')
                            ->required()
                            ->maxLength(100)
                            ->columnSpan(1),

                        TextInput::make('student_middle_name')
                            ->label('Middle Name')
                            ->maxLength(100)
                            ->columnSpan(1),

                        TextInput::make('student_last_name')
                            ->label('Last Name')
                            ->required()
                            ->maxLength(100)
                            ->columnSpan(1),

                        TextInput::make('student_preferred_name')
                            ->label('Preferred Name')
                            ->maxLength(100)
                            ->columnSpan(1),

                        Select::make('gender')
                            ->options([
                                'male' => 'Male',
                                'female' => 'Female',
                            ])
                            ->required()
                            ->native(false)
                            ->columnSpan(1),

                        DatePicker::make('birth_date')
                            ->label('Birth Date')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->maxDate(now())
                            ->columnSpan(1),

                        TextInput::make('birth_place')
                            ->label('Birth Place')
                            ->maxLength(100)
                            ->columnSpan(1),

                        TextInput::make('nationality')
                            ->label('Nationality')
                            ->required()
                            ->default('Indonesian')
                            ->maxLength(100)
                            ->columnSpan(1),

                        TextInput::make('passport_number')
                            ->label('Passport Number')
                            ->maxLength(50)
                            ->columnSpan(1),

                        TextInput::make('email')
                            ->label('Student Email')
                            ->email()
                            ->maxLength(255)
                            ->columnSpan(1),

                        TextInput::make('phone')
                            ->label('Student Phone')
                            ->tel()
                            ->maxLength(20)
                            ->columnSpan(1),

                        Textarea::make('languages_spoken')
                            ->label('Languages Spoken')
                            ->rows(2)
                            ->columnSpanFull(),

                        Textarea::make('interests_hobbies')
                            ->label('Interests & Hobbies')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Section::make('Current Address')
                    ->schema([
                        Textarea::make('current_address')
                            ->label('Street Address')
                            ->rows(2)
                            ->columnSpanFull(),

                        TextInput::make('current_city')
                            ->label('City')
                            ->maxLength(100)
                            ->columnSpan(1),

                        TextInput::make('current_country')
                            ->label('Country')
                            ->default('Indonesia')
                            ->maxLength(100)
                            ->columnSpan(1),

                        TextInput::make('current_postal_code')
                            ->label('Postal Code')
                            ->maxLength(20)
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Section::make('Previous School Information')
                    ->schema([
                        TextInput::make('previous_school_name')
                            ->label('School Name')
                            ->maxLength(255)
                            ->columnSpan(2),

                        TextInput::make('previous_school_country')
                            ->label('Country')
                            ->maxLength(100)
                            ->columnSpan(1),

                        TextInput::make('current_grade_level')
                            ->label('Current Grade/Level')
                            ->maxLength(50)
                            ->columnSpan(1),

                        DatePicker::make('previous_school_start_date')
                            ->label('Start Date')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->columnSpan(1),

                        DatePicker::make('previous_school_end_date')
                            ->label('End Date')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Section::make('Special Needs & Requirements')
                    ->schema([
                        Textarea::make('special_needs')
                            ->label('Special Needs')
                            ->rows(3)
                            ->helperText('Any medical, physical, or learning needs the school should be aware of')
                            ->columnSpanFull(),

                        Textarea::make('learning_support_required')
                            ->label('Learning Support Required')
                            ->rows(3)
                            ->helperText('Any additional learning support or accommodations needed')
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),

                Section::make('Assessment Requirements')
                    ->schema([
                        Toggle::make('requires_observation')
                            ->label('Requires Observation')
                            ->default(false)
                            ->inline(false)
                            ->columnSpan(1),

                        Toggle::make('requires_test')
                            ->label('Requires Test')
                            ->default(false)
                            ->inline(false)
                            ->columnSpan(1),

                        Toggle::make('requires_interview')
                            ->label('Requires Interview')
                            ->default(true)
                            ->inline(false)
                            ->columnSpan(1),
                    ])
                    ->columns(3)
                    ->collapsible(),

                Section::make('Internal Notes')
                    ->schema([
                        Textarea::make('interview_notes')
                            ->label('Interview Notes')
                            ->rows(3)
                            ->columnSpanFull(),

                        Textarea::make('rejection_reason')
                            ->label('Rejection Reason')
                            ->rows(3)
                            ->visible(fn (Get $get) => $get('status') === 'rejected')
                            ->columnSpanFull(),

                        Textarea::make('internal_notes')
                            ->label('Internal Notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}
