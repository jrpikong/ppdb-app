<?php

declare(strict_types=1);

namespace App\Filament\School\Resources\MedicalRecords\Schemas;

use App\Models\Application;
use App\Models\MedicalRecord;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class MedicalRecordForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()
                    ->schema([

                        // ── SECTION 1: Student Selection ─────────────────
                        Section::make('Student Information')
                            ->icon('heroicon-o-user')
                            ->schema([

                                Select::make('application_id')
                                    ->label('Student')
                                    ->required()
                                    ->searchable()
                                    ->native(false)
                                    ->options(function (): array {
                                        $schoolId = Filament::getTenant()?->id;
                                        if (!$schoolId) return [];

                                        return Application::query()
                                            ->where('school_id', $schoolId)
                                            ->whereIn('status', ['submitted', 'under_review', 'accepted', 'enrolled'])
                                            ->whereDoesntHave('medicalRecord')
                                            ->with('user')
                                            ->get()
                                            ->mapWithKeys(fn ($app): array => [
                                                $app->id => "[{$app->application_number}] " .
                                                    ($app->student_first_name . ' ' . $app->student_last_name),
                                            ])
                                            ->toArray();
                                    })
                                    ->helperText('Only applications without medical records are shown.')
                                    ->disabled(fn (?MedicalRecord $record) => $record !== null)
                                    ->columnSpanFull(),

                            ]),

                        // ── SECTION 2: Basic Physical Info ───────────────
                        Section::make('Physical Information')
                            ->icon('heroicon-o-scale')
                            ->columns(3)
                            ->schema([

                                Select::make('blood_type')
                                    ->label('Blood Type')
                                    ->native(false)
                                    ->options([
                                        'A+'      => 'A+',
                                        'A-'      => 'A-',
                                        'B+'      => 'B+',
                                        'B-'      => 'B-',
                                        'AB+'     => 'AB+',
                                        'AB-'     => 'AB-',
                                        'O+'      => 'O+',
                                        'O-'      => 'O-',
                                        'unknown' => 'Unknown',
                                    ]),

                                TextInput::make('height')
                                    ->label('Height')
                                    ->numeric()
                                    ->step(0.1)
                                    ->suffix('cm')
                                    ->placeholder('120.5'),

                                TextInput::make('weight')
                                    ->label('Weight')
                                    ->numeric()
                                    ->step(0.1)
                                    ->suffix('kg')
                                    ->placeholder('30.5'),

                            ]),

                        // ── SECTION 3: Allergies ──────────────────────────
                        Section::make('Allergies')
                            ->icon('heroicon-o-exclamation-triangle')
                            ->columns(2)
                            ->schema([

                                Toggle::make('has_food_allergies')
                                    ->label('Has Food Allergies')
                                    ->reactive()
                                    ->inline(false),

                                Textarea::make('food_allergies_details')
                                    ->label('Food Allergies Details')
                                    ->placeholder('e.g., Peanuts, Shellfish, Dairy...')
                                    ->rows(3)
                                    ->visible(fn (Get $get): bool => $get('has_food_allergies'))
                                    ->columnSpanFull(),

                                Textarea::make('allergies')
                                    ->label('Other Allergies (Medicine, Environmental)')
                                    ->placeholder('e.g., Penicillin, Pollen, Dust...')
                                    ->rows(3)
                                    ->columnSpanFull(),

                            ]),

                        // ── SECTION 4: Medical Conditions ────────────────
                        Section::make('Medical Conditions')
                            ->icon('heroicon-o-heart')
                            ->columns(2)
                            ->schema([

                                Toggle::make('has_medical_conditions')
                                    ->label('Has Medical Conditions')
                                    ->reactive()
                                    ->inline(false),

                                Textarea::make('medical_conditions')
                                    ->label('Medical Conditions Details')
                                    ->placeholder('e.g., Asthma, Diabetes, Epilepsy...')
                                    ->rows(3)
                                    ->visible(fn (Get $get): bool => $get('has_medical_conditions'))
                                    ->columnSpanFull(),

                                Toggle::make('requires_daily_medication')
                                    ->label('Requires Daily Medication')
                                    ->reactive()
                                    ->inline(false),

                                Textarea::make('daily_medications')
                                    ->label('Daily Medications')
                                    ->placeholder('List medications, dosage, and schedule...')
                                    ->rows(3)
                                    ->visible(fn (Get $get): bool => $get('requires_daily_medication'))
                                    ->columnSpanFull(),

                            ]),

                        // ── SECTION 5: Dietary & Special Needs ───────────
                        Section::make('Dietary & Special Needs')
                            ->icon('heroicon-o-beaker')
                            ->columns(2)
                            ->schema([

                                Toggle::make('has_dietary_restrictions')
                                    ->label('Has Dietary Restrictions')
                                    ->reactive()
                                    ->inline(false),

                                Textarea::make('dietary_restrictions')
                                    ->label('Dietary Restrictions')
                                    ->placeholder('e.g., Vegetarian, Halal, Kosher...')
                                    ->rows(3)
                                    ->visible(fn (Get $get): bool => $get('has_dietary_restrictions'))
                                    ->columnSpanFull(),

                                Toggle::make('has_special_needs')
                                    ->label('Has Special Needs')
                                    ->reactive()
                                    ->inline(false),

                                Textarea::make('special_needs_description')
                                    ->label('Special Needs Description')
                                    ->placeholder('Describe any special needs...')
                                    ->rows(3)
                                    ->visible(fn (Get $get): bool => $get('has_special_needs'))
                                    ->columnSpanFull(),

                                Toggle::make('requires_learning_support')
                                    ->label('Requires Learning Support')
                                    ->reactive()
                                    ->inline(false),

                                Textarea::make('learning_support_details')
                                    ->label('Learning Support Details')
                                    ->placeholder('Describe required learning support...')
                                    ->rows(3)
                                    ->visible(fn (Get $get): bool => $get('requires_learning_support'))
                                    ->columnSpanFull(),

                            ]),

                        // ── SECTION 6: Immunizations ──────────────────────
                        Section::make('Immunizations')
                            ->icon('heroicon-o-shield-check')
                            ->schema([

                                Toggle::make('immunizations_up_to_date')
                                    ->label('Immunizations Up-to-Date')
                                    ->helperText('Are all required immunizations current?')
                                    ->inline(false),

                                Textarea::make('immunization_records')
                                    ->label('Immunization Records')
                                    ->placeholder('List recent immunizations and dates...')
                                    ->rows(3)
                                    ->columnSpanFull(),

                            ]),

                        // ── SECTION 7: Emergency Contact ─────────────────
                        Section::make('Emergency Contact')
                            ->icon('heroicon-o-phone')
                            ->columns(2)
                            ->description('Primary emergency contact in case of medical emergency')
                            ->schema([

                                TextInput::make('emergency_contact_name')
                                    ->label('Name')
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('emergency_contact_relationship')
                                    ->label('Relationship')
                                    ->placeholder('e.g., Father, Mother, Guardian')
                                    ->maxLength(100),

                                TextInput::make('emergency_contact_phone')
                                    ->label('Phone Number')
                                    ->required()
                                    ->tel()
                                    ->maxLength(20)
                                    ->placeholder('+62 812-xxxx-xxxx'),

                                TextInput::make('emergency_contact_email')
                                    ->label('Email')
                                    ->email()
                                    ->maxLength(255),

                            ]),

                        // ── SECTION 8: Doctor & Insurance ────────────────
                        Section::make('Doctor & Insurance Information')
                            ->icon('heroicon-o-building-office-2')
                            ->columns(2)
                            ->collapsed()
                            ->schema([

                                TextInput::make('doctor_name')
                                    ->label('Family Doctor Name')
                                    ->maxLength(255),

                                TextInput::make('doctor_phone')
                                    ->label('Doctor Phone')
                                    ->tel()
                                    ->maxLength(20),

                                TextInput::make('hospital_preference')
                                    ->label('Preferred Hospital')
                                    ->maxLength(255)
                                    ->columnSpanFull(),

                                TextInput::make('health_insurance_provider')
                                    ->label('Health Insurance Provider')
                                    ->placeholder('e.g., Allianz, Prudential...')
                                    ->maxLength(255),

                                TextInput::make('health_insurance_number')
                                    ->label('Insurance Policy Number')
                                    ->maxLength(100),

                            ]),

                        // ── SECTION 9: Additional Notes ──────────────────
                        Section::make('Additional Notes')
                            ->icon('heroicon-o-document-text')
                            ->collapsed()
                            ->schema([

                                Textarea::make('additional_notes')
                                    ->label('Additional Medical Notes')
                                    ->placeholder('Any other relevant medical information...')
                                    ->rows(4)
                                    ->columnSpanFull(),

                            ]),

                    ])
                    ->columnSpan(['lg' => fn (?MedicalRecord $record) => $record === null ? 3 : 2]),

                // ── SIDEBAR: Health Summary (only on edit) ───────────
                Section::make('Health Summary')
                    ->schema([
                        TextEntry::make('completion_percentage')
                            ->label('Form Completion')
                            ->state(fn (MedicalRecord $record): string =>
                                $record->getCompletionPercentage() . '%'
                            )
                            ->badge()
                            ->color(fn (MedicalRecord $record): string => match (true) {
                                $record->getCompletionPercentage() >= 80 => 'success',
                                $record->getCompletionPercentage() >= 50 => 'warning',
                                default                                   => 'danger',
                            }),

                        TextEntry::make('bmi')
                            ->label('BMI')
                            ->state(fn (MedicalRecord $record): string =>
                            $record->bmi
                                ? $record->bmi . ' (' . $record->bmi_category . ')'
                                : '—'
                            ),

                        TextEntry::make('created_at')
                            ->label('Created')
                            ->dateTime('d M Y, H:i'),

                        TextEntry::make('updated_at')
                            ->label('Last Updated')
                            ->dateTime('d M Y, H:i'),
                    ])
                    ->columnSpan(['lg' => 1])
                    ->hidden(fn (?MedicalRecord $record) => $record === null),

            ])
            ->columns(3);
    }
}
