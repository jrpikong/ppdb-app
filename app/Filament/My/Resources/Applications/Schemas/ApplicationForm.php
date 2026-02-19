<?php

declare(strict_types=1);

namespace App\Filament\My\Resources\Applications\Schemas;

use App\Models\AdmissionPeriod;
use App\Models\Document;
use App\Models\Level;
use App\Models\School;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\UnorderedList;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;

class ApplicationForm
{
    private static function documentMimeTypes(): array
    {
        return [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'image/webp',
        ];
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            // ══════════════════════════════════════════════════════════
            // CREATE MODE  –  Pick school / period / level first
            // ══════════════════════════════════════════════════════════
            Section::make('🎓 Start New Application')
                ->description('Choose the school, admission period, and level. You can complete the rest of the form after saving.')
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
                        })
                        ->helperText('Only schools accepting online admissions are shown.'),

                    Select::make('admission_period_id')
                        ->label('Admission Period')
                        ->options(function (Get $get): array {
                            $schoolId = $get('school_id');
                            if (! $schoolId) return [];
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
                            if (! $state) { $set('academic_year_id', null); return; }
                            $period = AdmissionPeriod::find($state);
                            $set('academic_year_id', $period?->academic_year_id);
                        })
                        ->helperText('Only open admission periods are listed.'),

                    Select::make('level_id')
                        ->label('Level / Grade Applying For')
                        ->options(function (Get $get): array {
                            $schoolId = $get('school_id');
                            if (! $schoolId) return [];
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
                    Hidden::make('status')->default('draft'),
                ]),

            // ══════════════════════════════════════════════════════════
            // EDIT MODE  –  Full multi-step Wizard
            // ══════════════════════════════════════════════════════════
            Wizard::make([

                // ─── Step 1: Admission Setup ────────────────────────────
                Step::make('Admission Setup')
                    ->icon('heroicon-o-building-library')
                    ->description('School, period and level selection')
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
                            ->required()->searchable()->preload()->live()
                            ->afterStateUpdated(function (Set $set): void {
                                $set('admission_period_id', null);
                                $set('academic_year_id', null);
                                $set('level_id', null);
                            }),

                        Select::make('admission_period_id')
                            ->label('Admission Period')
                            ->options(function (Get $get): array {
                                $schoolId = $get('school_id');
                                if (! $schoolId) return [];
                                return AdmissionPeriod::query()
                                    ->where('school_id', $schoolId)
                                    ->where('is_active', true)
                                    ->where('allow_applications', true)
                                    ->orderBy('start_date')
                                    ->pluck('name', 'id')->toArray();
                            })
                            ->required()->searchable()->preload()->live()
                            ->afterStateUpdated(function (Set $set, ?string $state): void {
                                if (! $state) { $set('academic_year_id', null); return; }
                                $period = AdmissionPeriod::find($state);
                                $set('academic_year_id', $period?->academic_year_id);
                            }),

                        Select::make('level_id')
                            ->label('Level / Grade Applying For')
                            ->options(function (Get $get): array {
                                $schoolId = $get('school_id');
                                if (! $schoolId) return [];
                                return Level::query()
                                    ->where('school_id', $schoolId)
                                    ->where('is_active', true)
                                    ->where('is_accepting_applications', true)
                                    ->orderBy('sort_order')
                                    ->pluck('name', 'id')->toArray();
                            })
                            ->required()->searchable()->preload(),

                        Hidden::make('academic_year_id'),
                        Hidden::make('status')->default('draft'),
                    ]),

                // ─── Step 2: Student Biodata ────────────────────────────
                Step::make('Student Biodata')
                    ->icon('heroicon-o-user')
                    ->description('Personal information of the student')
                    ->columns(2)
                    ->schema([
                        Section::make('Full Name')->columns(3)->columnSpanFull()->schema([
                            TextInput::make('student_first_name')->label('First Name')->required()->maxLength(100)->autofocus(),
                            TextInput::make('student_middle_name')->label('Middle Name')->maxLength(100),
                            TextInput::make('student_last_name')->label('Last Name')->required()->maxLength(100),
                        ]),

                        TextInput::make('student_preferred_name')
                            ->label('Preferred / Nick Name')->maxLength(100)
                            ->helperText('Name the school should use day-to-day'),

                        Radio::make('gender')
                            ->label('Gender')->required()
                            ->options(['male' => '♂ Male', 'female' => '♀ Female'])->inline(),

                        DatePicker::make('birth_date')
                            ->label('Date of Birth')->required()
                            ->native(false)->displayFormat('d M Y')->maxDate(now()->subYears(2)),

                        TextInput::make('birth_place')->label('Place of Birth')->maxLength(100),

                        TextInput::make('nationality')
                            ->label('Nationality')->required()->default('Indonesian')->maxLength(100),

                        TextInput::make('passport_number')->label('Passport / NIK Number')->maxLength(50),

                        Section::make('Student Contact (Optional)')->columns(2)->collapsed()->columnSpanFull()->schema([
                            TextInput::make('email')->label('Student Email')->email()->maxLength(255),
                            TextInput::make('phone')->label('Student Phone')->tel()->maxLength(20),
                        ]),
                    ]),

                // ─── Step 3: Address & Previous School ─────────────────
                Step::make('Address & Previous School')
                    ->icon('heroicon-o-map-pin')
                    ->description('Home address and current school information')
                    ->columns(2)
                    ->schema([
                        Section::make('Current Address')->columns(2)->columnSpanFull()->schema([
                            Textarea::make('current_address')->label('Street Address')->required()->rows(2)->columnSpanFull(),
                            TextInput::make('current_city')->label('City')->required()->maxLength(100),
                            TextInput::make('current_country')->label('Country')->required()->default('Indonesia')->maxLength(100),
                            TextInput::make('current_postal_code')->label('Postal Code')->maxLength(10),
                        ]),

                        Section::make('Current / Previous School')->columns(2)->columnSpanFull()->schema([
                            TextInput::make('previous_school_name')->label('School Name')->maxLength(255)->columnSpanFull(),
                            TextInput::make('previous_school_country')->label('Country')->maxLength(100),
                            TextInput::make('current_grade_level')->label('Current Grade Level')->maxLength(50),
                            DatePicker::make('previous_school_start_date')->label('Start Date')->native(false)->displayFormat('M Y'),
                            DatePicker::make('previous_school_end_date')->label('End Date')->native(false)->displayFormat('M Y'),
                        ]),

                        Section::make('Additional Information')->collapsed()->columnSpanFull()->schema([
                            Textarea::make('languages_spoken')->label('Languages Spoken')->rows(2)
                                ->helperText('e.g., English (fluent), Indonesian (native)')->columnSpanFull(),
                            Textarea::make('interests_hobbies')->label('Interests & Hobbies')->rows(2)->columnSpanFull(),
                        ]),
                    ]),

                // ─── Step 4: Parent / Guardian ──────────────────────────
                Step::make('Parent / Guardian')
                    ->icon('heroicon-o-user-group')
                    ->description('At least one parent or guardian contact is required')
                    ->schema([
                        Repeater::make('parentGuardians')
                            ->relationship('parentGuardians')
                            ->label('')->defaultItems(1)->minItems(1)
                            ->reorderable(false)->cloneable()->collapsible()
                            ->collapseAllAction(fn ($action) => $action->label('Collapse All'))
                            ->itemLabel(fn (array $state): string =>
                            trim("{$state['type']} – {$state['first_name']} {$state['last_name']}") ?: 'New Guardian'
                            )
                            ->schema([
                                Grid::make(4)->schema([
                                    Select::make('type')->label('Type')->required()
                                        ->options(['father' => '👨 Father', 'mother' => '👩 Mother', 'guardian' => '🧑 Legal Guardian', 'other' => 'Other'])
                                        ->native(false)->live(),
                                    TextInput::make('first_name')->label('First Name')->required()->maxLength(100),
                                    TextInput::make('last_name')->label('Last Name')->required()->maxLength(100),
                                    TextInput::make('middle_name')->label('Middle Name')->maxLength(100),
                                ]),
                                Grid::make(3)->schema([
                                    TextInput::make('email')->label('Email')->email()->maxLength(255),
                                    TextInput::make('mobile')->label('Mobile Phone')->tel()->maxLength(20),
                                    TextInput::make('phone')->label('Work Phone')->tel()->maxLength(20),
                                ]),
                                Grid::make(3)->schema([
                                    TextInput::make('nationality')->label('Nationality')->maxLength(100),
                                    Select::make('id_type')->label('ID Type')
                                        ->options(['ktp' => 'KTP', 'passport' => 'Passport', 'kitas' => 'KITAS', 'other' => 'Other'])
                                        ->native(false),
                                    TextInput::make('id_number')->label('ID Number')->maxLength(50),
                                ]),
                                Grid::make(2)->schema([
                                    TextInput::make('occupation')->label('Occupation / Job Title')->maxLength(255),
                                    TextInput::make('company_name')->label('Company / Employer')->maxLength(255),
                                ]),
                                Textarea::make('address')->label('Home Address (if different)')->rows(2)->columnSpanFull(),
                                Grid::make(3)->schema([
                                    TextInput::make('city')->label('City')->maxLength(100),
                                    TextInput::make('country')->label('Country')->default('Indonesia')->maxLength(100),
                                    TextInput::make('postal_code')->label('Postal Code')->maxLength(10),
                                ]),
                                Grid::make(2)->schema([
                                    Toggle::make('is_primary_contact')->label('Primary Contact')->helperText('School will contact this person first'),
                                    Toggle::make('is_emergency_contact')->label('Emergency Contact')->helperText('To be notified in emergencies'),
                                ]),
                            ]),
                    ]),

                // ─── Step 5: Medical Information ────────────────────────
                Step::make('Medical Information')
                    ->icon('heroicon-o-heart')
                    ->description('Health details to help the school support your child')
                    ->columns(2)
                    ->schema([
                        Section::make('Physical Data')->columns(3)->columnSpanFull()->schema([
                            Select::make('medicalRecord.blood_type')->label('Blood Type')
                                ->options(['A+'=>'A+','A-'=>'A-','B+'=>'B+','B-'=>'B-','AB+'=>'AB+','AB-'=>'AB-','O+'=>'O+','O-'=>'O-','unknown'=>'Unknown'])
                                ->native(false),
                            TextInput::make('medicalRecord.height')->label('Height (cm)')->numeric()->minValue(50)->maxValue(250),
                            TextInput::make('medicalRecord.weight')->label('Weight (kg)')->numeric()->minValue(5)->maxValue(200),
                        ]),

                        Section::make('Allergies & Conditions')->columns(2)->columnSpanFull()->schema([
                            Toggle::make('medicalRecord.has_food_allergies')->label('Food Allergies')->live(),
                            Textarea::make('medicalRecord.food_allergies_details')->label('Food Allergy Details')->rows(2)
                                ->visible(fn (Get $get): bool => (bool) $get('medicalRecord.has_food_allergies'))->columnSpanFull(),
                            Toggle::make('medicalRecord.has_medical_conditions')->label('Medical Conditions')->live(),
                            Textarea::make('medicalRecord.medical_conditions_details')->label('Medical Condition Details')->rows(2)
                                ->visible(fn (Get $get): bool => (bool) $get('medicalRecord.has_medical_conditions'))->columnSpanFull(),
                            Toggle::make('medicalRecord.requires_daily_medication')->label('Requires Daily Medication')->live(),
                            Textarea::make('medicalRecord.medications_details')->label('Medication Details')->rows(2)
                                ->visible(fn (Get $get): bool => (bool) $get('medicalRecord.requires_daily_medication'))->columnSpanFull(),
                            Toggle::make('medicalRecord.has_dietary_restrictions')->label('Dietary Restrictions')->live(),
                            Textarea::make('medicalRecord.dietary_restrictions_details')->label('Dietary Details')->rows(2)
                                ->visible(fn (Get $get): bool => (bool) $get('medicalRecord.has_dietary_restrictions'))->columnSpanFull(),
                            Toggle::make('medicalRecord.has_special_needs')->label('Special Educational Needs')->live(),
                            Textarea::make('medicalRecord.special_needs_details')->label('Special Needs Details')->rows(2)
                                ->visible(fn (Get $get): bool => (bool) $get('medicalRecord.has_special_needs'))->columnSpanFull(),
                        ]),

                        Section::make('Emergency Medical Contact')->columns(3)->columnSpanFull()->schema([
                            TextInput::make('medicalRecord.emergency_contact_name')->label('Contact Name')->maxLength(255),
                            TextInput::make('medicalRecord.emergency_contact_phone')->label('Contact Phone')->tel()->maxLength(20),
                            TextInput::make('medicalRecord.emergency_contact_relationship')->label('Relationship')->maxLength(100),
                            TextInput::make('medicalRecord.doctor_name')->label('Family Doctor')->maxLength(255),
                            TextInput::make('medicalRecord.doctor_phone')->label('Doctor Phone')->tel()->maxLength(20),
                            TextInput::make('medicalRecord.hospital_preference')->label('Preferred Hospital')->maxLength(255),
                        ]),

                        Toggle::make('medicalRecord.immunizations_up_to_date')
                            ->label('Immunizations are up to date')->columnSpanFull(),

                        Textarea::make('medicalRecord.additional_notes')
                            ->label('Additional Medical Notes')->rows(3)->columnSpanFull(),
                    ]),

                // ─── Step 6: Documents ──────────────────────────────────
                Step::make('Documents')
                    ->icon('heroicon-o-paper-clip')
                    ->description('Upload required supporting documents (PDF or image, max 10 MB each)')
                    ->schema([
                        Repeater::make('documents')
                            ->relationship('documents')
                            ->label('')->defaultItems(0)->collapsible()->cloneable()
                            ->itemLabel(fn (array $state): string =>
                                Document::DOCUMENT_TYPES[$state['type'] ?? ''] ?? ($state['name'] ?? 'Document')
                            )
                            ->schema([
                                Grid::make(2)->schema([
                                    Select::make('type')->label('Document Type')->required()
                                        ->options(Document::documentTypeOptions())
                                        ->searchable()->preload()->native(false),
                                    TextInput::make('name')->label('File Label / Description')->required()->maxLength(255),
                                ]),
                                FileUpload::make('file_path')
                                    ->label('Upload File')->required()
                                    ->disk('public')->directory('documents')->maxSize(10240)
                                    ->acceptedFileTypes(self::documentMimeTypes())
                                    ->helperText('Accepted: PDF, JPG / JPEG, PNG, WebP · Max 10 MB')
                                    ->downloadable()->openable()->columnSpanFull(),
                                Textarea::make('description')->label('Notes (optional)')->rows(1)->columnSpanFull(),
                                Hidden::make('status')->default('pending'),
                                Hidden::make('file_type'),
                                Hidden::make('file_size'),
                            ])
                            ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                                $data['status'] = 'pending';
                                $data['file_type'] = 'application/octet-stream';
                                $data['file_size'] = 0;

                                if (! empty($data['file_path'])) {
                                    // File sudah dipindah ke disk public
                                    if (Storage::disk('public')->exists($data['file_path'])) {
                                        try {
                                            $data['file_type'] = Storage::disk('public')->mimeType($data['file_path']) ?: 'application/octet-stream';
                                            $data['file_size'] = Storage::disk('public')->size($data['file_path']) ?: 0;
                                        } catch (\Throwable) {
                                            // biarkan default values di atas
                                        }
                                    }
                                    // File masih di livewire-tmp (disk local) — ambil size dari sana
                                    elseif (Storage::disk('local')->exists($data['file_path'])) {
                                        try {
                                            $data['file_size'] = Storage::disk('local')->size($data['file_path']) ?: 0;
                                        } catch (\Throwable) {
                                            // biarkan default
                                        }
                                    }
                                }

                                return $data;
                            })
                            ->mutateRelationshipDataBeforeSaveUsing(function (array $data): array {
                                if (! empty($data['file_path'])) {
                                    if (Storage::disk('public')->exists($data['file_path'])) {
                                        try {
                                            $data['file_type'] = Storage::disk('public')->mimeType($data['file_path']) ?: ($data['file_type'] ?? 'application/octet-stream');
                                            $data['file_size'] = Storage::disk('public')->size($data['file_path']) ?: ($data['file_size'] ?? 0);
                                        } catch (\Throwable) {
                                            // pertahankan nilai lama
                                        }
                                    } elseif (Storage::disk('local')->exists($data['file_path'])) {
                                        try {
                                            $data['file_size'] = Storage::disk('local')->size($data['file_path']) ?: ($data['file_size'] ?? 0);
                                        } catch (\Throwable) {
                                            // pertahankan nilai lama
                                        }
                                    }
                                }

                                return $data;
                            })
                            ->columns(2),
                    ]),

                // ─── Step 7: Review & Submit ────────────────────────────
                Step::make('Review & Submit')
                    ->icon('heroicon-o-check-circle')
                    ->description('Verify all information before submitting')
                    ->schema([
                        Section::make()
                            ->schema([
                                Text::make(new HtmlString(
                                    '<span class="font-medium">Please review all the information you have entered.</span> ' .
                                    'Once you click <strong>"Submit Application"</strong> from the page header, ' .
                                    'your application will be <strong>locked</strong> and sent to the school for processing.'
                                ))->color('neutral'),
                            ])->secondary()->compact(),

                        Section::make('📋 Before Submitting')
                            ->description('Make sure all of the following are complete:')
                            ->schema([
                                UnorderedList::make([
                                    Text::make(new HtmlString('<strong>Student biodata</strong> is complete and accurate'))->color('neutral')->size(TextSize::Small),
                                    Text::make(new HtmlString('At least <strong>one parent / guardian</strong> contact is added'))->color('neutral')->size(TextSize::Small),
                                    Text::make(new HtmlString('<strong>Current address</strong> is provided'))->color('neutral')->size(TextSize::Small),
                                    Text::make(new HtmlString('At least <strong>one supporting document</strong> is uploaded'))->color('neutral')->size(TextSize::Small),
                                    Text::make(new HtmlString('All <strong>required fields</strong> in each step are filled'))->color('neutral')->size(TextSize::Small),
                                ])->size(TextSize::Small),
                            ])->compact(),

                        Section::make('📬 After Submission')
                            ->schema([
                                UnorderedList::make([
                                    Text::make(new HtmlString('🔒 Application becomes <strong>read-only</strong> — no further edits allowed'))->color('neutral')->size(TextSize::Small),
                                    Text::make(new HtmlString('📧 You will receive a <strong>confirmation email</strong>'))->color('neutral')->size(TextSize::Small),
                                    Text::make(new HtmlString('📋 The school will <strong>review your application</strong> and contact you'))->color('neutral')->size(TextSize::Small),
                                ])->size(TextSize::Small),
                            ])->compact()->secondary(),

                        Section::make()
                            ->schema([
                                Text::make(new HtmlString(
                                    '✅ When you\'re ready, click the <strong>"Submit Application"</strong> button in the page header above.'
                                ))->color('success')->weight(FontWeight::SemiBold)->size(TextSize::Medium),
                            ])->compact(),
                    ]),

            ])
                ->visibleOn('edit')
                ->columnSpanFull()
                ->persistStepInQueryString()
                ->skippable(false)
                // ── AUTO-SAVE ON NEXT ─────────────────────────────────────
                // nextAction()->before() fires AFTER wizard validates the current
                // step but BEFORE the wizard advances — this is the safe window
                // to persist data without interrupting the step transition.
                //
                // We use save(shouldRedirect: false, shouldSendSavedNotification: false)
                // so Livewire does NOT redirect or re-render the full page.
                // A subtle toast is shown manually instead.
                ->nextAction(
                    fn (Action $action) => $action
                        ->label('Next')
                        ->before(function (EditRecord $livewire): void {
                            $livewire->save(
                                shouldRedirect: false,
                                shouldSendSavedNotification: false,
                            );

                            Notification::make()
                                ->title('Progress saved')
                                ->body('Your cheanges have been saved automatically.')
                                ->success()
                                ->duration(2000)
                                ->send();
                        })
                ),
        ]);
    }
}
