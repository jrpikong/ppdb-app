<?php

declare(strict_types=1);

namespace App\Filament\My\Resources\Applications\Schemas;

use App\Models\AdmissionPeriod;
use App\Models\Document;
use App\Models\Level;
use App\Models\School;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;

class ApplicationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Start New Application')
                    ->description('Choose school, admission period, and level. After saving, continue in the wizard.')
                    ->visibleOn('create')
                    ->columns(2)
                    ->schema([
                        Select::make('school_id')
                            ->label('School')
                            ->options(fn (): array => School::query()
                                ->where('is_active', true)
                                ->where('allow_online_admission', true)
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->toArray()
                            )
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function (Set $set): void {
                                $set('admission_period_id', null);
                                $set('academic_year_id', null);
                                $set('level_id', null);
                            }),

                        Select::make('admission_period_id')
                            ->label('Admission Period')
                            ->options(function (Get $get): array {
                                $schoolId = $get('school_id');

                                if (! $schoolId) {
                                    return [];
                                }

                                return AdmissionPeriod::query()
                                    ->where('school_id', $schoolId)
                                    ->where('is_active', true)
                                    ->where('allow_applications', true)
                                    ->orderBy('start_date')
                                    ->pluck('name', 'id')
                                    ->toArray();
                            })
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function (Set $set, ?string $state): void {
                                if (! $state) {
                                    $set('academic_year_id', null);
                                    return;
                                }

                                $period = AdmissionPeriod::query()->find($state);
                                $set('academic_year_id', $period?->academic_year_id);
                            }),

                        Select::make('level_id')
                            ->label('Applying For Level/Grade')
                            ->options(function (Get $get): array {
                                $schoolId = $get('school_id');

                                if (! $schoolId) {
                                    return [];
                                }

                                return Level::query()
                                    ->where('school_id', $schoolId)
                                    ->where('is_active', true)
                                    ->where('is_accepting_applications', true)
                                    ->orderBy('sort_order')
                                    ->pluck('name', 'id')
                                    ->toArray();
                            })
                            ->required()
                            ->searchable()
                            ->preload(),

                        Hidden::make('academic_year_id'),
                        Hidden::make('status')
                            ->default('draft'),
                    ]),

                Wizard::make([
                    Step::make('Step 2: Student Biodata')
                        ->description('Fill core student information.')
                        ->columns(2)
                        ->schema([
                            TextInput::make('student_first_name')
                                ->required()
                                ->maxLength(100),
                            TextInput::make('student_middle_name')
                                ->maxLength(100),
                            TextInput::make('student_last_name')
                                ->required()
                                ->maxLength(100),
                            TextInput::make('student_preferred_name')
                                ->maxLength(100),
                            Select::make('gender')
                                ->required()
                                ->options([
                                    'male' => 'Male',
                                    'female' => 'Female',
                                ])
                                ->native(false),
                            DatePicker::make('birth_date')
                                ->required()
                                ->native(false)
                                ->maxDate(now()),
                            TextInput::make('birth_place')
                                ->maxLength(100),
                            TextInput::make('nationality')
                                ->required()
                                ->default('Indonesian')
                                ->maxLength(100),
                            TextInput::make('passport_number')
                                ->maxLength(50),
                            TextInput::make('email')
                                ->label('Student Email')
                                ->email()
                                ->maxLength(255),
                            TextInput::make('phone')
                                ->label('Student Phone')
                                ->tel()
                                ->maxLength(20),
                        ]),

                    Step::make('Step 3: Address & Previous School')
                        ->columns(2)
                        ->schema([
                            Textarea::make('current_address')
                                ->required()
                                ->rows(2)
                                ->columnSpanFull(),
                            TextInput::make('current_city')
                                ->required()
                                ->maxLength(100),
                            TextInput::make('current_country')
                                ->required()
                                ->default('Indonesia')
                                ->maxLength(100),
                            TextInput::make('current_postal_code')
                                ->maxLength(20),
                            TextInput::make('previous_school_name')
                                ->maxLength(255)
                                ->columnSpanFull(),
                            TextInput::make('previous_school_country')
                                ->maxLength(100),
                            TextInput::make('current_grade_level')
                                ->maxLength(50),
                            Textarea::make('languages_spoken')
                                ->rows(2)
                                ->columnSpanFull(),
                            Textarea::make('interests_hobbies')
                                ->rows(2)
                                ->columnSpanFull(),
                        ]),

                    Step::make('Step 4: Parent / Guardian')
                        ->description('Add parent or guardian contacts (at least one).')
                        ->schema([
                            Repeater::make('parentGuardians')
                                ->relationship('parentGuardians')
                                ->defaultItems(1)
                                ->reorderable(false)
                                ->cloneable()
                                ->collapsed()
                                ->schema([
                                    Select::make('type')
                                        ->required()
                                        ->options([
                                            'father' => 'Father',
                                            'mother' => 'Mother',
                                            'guardian' => 'Guardian',
                                        ])
                                        ->native(false),
                                    TextInput::make('first_name')
                                        ->required()
                                        ->maxLength(100),
                                    TextInput::make('last_name')
                                        ->required()
                                        ->maxLength(100),
                                    TextInput::make('relationship')
                                        ->maxLength(100),
                                    TextInput::make('email')
                                        ->email()
                                        ->maxLength(255),
                                    TextInput::make('mobile')
                                        ->label('Mobile')
                                        ->tel()
                                        ->maxLength(20),
                                    TextInput::make('phone')
                                        ->tel()
                                        ->maxLength(20),
                                    TextInput::make('occupation')
                                        ->maxLength(255),
                                    Textarea::make('address')
                                        ->rows(2)
                                        ->columnSpanFull(),
                                ])
                                ->columns(2),
                        ]),

                    Step::make('Step 5: Documents')
                        ->description('Upload required documents. You can add more later if requested.')
                        ->schema([
                            Repeater::make('documents')
                                ->relationship('documents')
                                ->defaultItems(0)
                                ->collapsed()
                                ->schema([
                                    Select::make('type')
                                        ->required()
                                        ->options(Document::documentTypeOptions())
                                        ->searchable()
                                        ->preload(),
                                    TextInput::make('name')
                                        ->required()
                                        ->maxLength(255),
                                    FileUpload::make('file_path')
                                        ->required()
                                        ->disk('public')
                                        ->directory('documents')
                                        ->maxSize(10240)
                                        ->acceptedFileTypes([
                                            'application/pdf',
                                            'image/jpeg',
                                            'image/jpg',
                                            'image/png',
                                        ]),
                                    Textarea::make('description')
                                        ->rows(2)
                                        ->columnSpanFull(),
                                    Hidden::make('status')
                                        ->default('pending'),
                                    Hidden::make('file_type'),
                                    Hidden::make('file_size'),
                                ])
                                ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                                    $data['status'] = 'pending';

                                    if (! empty($data['file_path']) && Storage::disk('public')->exists($data['file_path'])) {
                                        $data['file_type'] = Storage::disk('public')->mimeType($data['file_path']) ?: 'application/octet-stream';
                                        $data['file_size'] = Storage::disk('public')->size($data['file_path']) ?: 0;
                                    }

                                    return $data;
                                })
                                ->mutateRelationshipDataBeforeSaveUsing(function (array $data): array {
                                    if (! empty($data['file_path']) && Storage::disk('public')->exists($data['file_path'])) {
                                        $data['file_type'] = Storage::disk('public')->mimeType($data['file_path']) ?: ($data['file_type'] ?? 'application/octet-stream');
                                        $data['file_size'] = Storage::disk('public')->size($data['file_path']) ?: ($data['file_size'] ?? 0);
                                    }

                                    return $data;
                                })
                                ->columns(2),
                        ]),

                    Step::make('Step 6: Review & Submit')
                        ->schema([
                            Placeholder::make('submit_note')
                                ->content('Review your data, then click "Submit Application" from the page header. After submitted, this application becomes read-only.'),
                        ]),
                ])
                    ->visibleOn('edit')
                    ->columnSpanFull(),
            ]);
    }
}
