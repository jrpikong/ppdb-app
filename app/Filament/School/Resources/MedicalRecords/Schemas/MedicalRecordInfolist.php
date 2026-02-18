<?php

declare(strict_types=1);

namespace App\Filament\School\Resources\MedicalRecords\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;

class MedicalRecordInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Student Information')
                ->icon('heroicon-o-user')
                ->columns(3)
                ->schema([
                    TextEntry::make('application.student_first_name')
                        ->label('Full Name')
                        ->formatStateUsing(fn ($state, $record): string =>
                        trim($record->application?->student_first_name . ' ' .
                            $record->application?->student_last_name)
                        )
                        ->weight(FontWeight::Bold),

                    TextEntry::make('application.application_number')
                        ->label('Application #')
                        ->badge()
                        ->color('info'),

                    TextEntry::make('application.birth_date')
                        ->label('Age')
                        ->state(fn ($record): string =>
                        $record->application?->birth_date
                            ? $record->application->birth_date->age . ' years old'
                            : '—'
                        ),
                ]),

            Section::make('Physical Information')
                ->icon('heroicon-o-scale')
                ->columns(4)
                ->schema([
                    TextEntry::make('blood_type')
                        ->label('Blood Type')
                        ->badge()
                        ->color('danger')
                        ->formatStateUsing(fn (?string $state): string =>
                            $state ?? 'Unknown'
                        ),

                    TextEntry::make('height')
                        ->label('Height')
                        ->state(fn ($record): string =>
                        $record->height ? $record->height . ' cm' : '—'
                        ),

                    TextEntry::make('weight')
                        ->label('Weight')
                        ->state(fn ($record): string =>
                        $record->weight ? $record->weight . ' kg' : '—'
                        ),

                    TextEntry::make('bmi')
                        ->label('BMI')
                        ->state(fn ($record): string =>
                        $record->bmi
                            ? $record->bmi . ' (' . $record->bmi_category . ')'
                            : '—'
                        )
                        ->badge()
                        ->color(fn ($record): string => match ($record->bmi_category) {
                            'Normal'      => 'success',
                            'Underweight' => 'warning',
                            'Overweight'  => 'warning',
                            'Obese'       => 'danger',
                            default       => 'gray',
                        }),
                ]),

            Section::make('Allergies & Dietary')
                ->icon('heroicon-o-exclamation-triangle')
                ->columns(2)
                ->schema([
                    IconEntry::make('has_food_allergies')
                        ->label('Has Food Allergies')
                        ->boolean()
                        ->trueColor('warning')
                        ->falseColor('success'),

                    TextEntry::make('food_allergies_details')
                        ->label('Food Allergies')
                        ->placeholder('None')
                        ->visible(fn ($record): bool => $record->has_food_allergies),

                    TextEntry::make('allergies')
                        ->label('Other Allergies')
                        ->placeholder('None')
                        ->columnSpanFull(),

                    IconEntry::make('has_dietary_restrictions')
                        ->label('Has Dietary Restrictions')
                        ->boolean()
                        ->trueColor('info')
                        ->falseColor('success'),

                    TextEntry::make('dietary_restrictions')
                        ->label('Dietary Restrictions')
                        ->placeholder('None')
                        ->visible(fn ($record): bool => $record->has_dietary_restrictions),
                ]),

            Section::make('Medical Conditions')
                ->icon('heroicon-o-heart')
                ->columns(2)
                ->schema([
                    IconEntry::make('has_medical_conditions')
                        ->label('Has Medical Conditions')
                        ->boolean()
                        ->trueColor('danger')
                        ->falseColor('success'),

                    TextEntry::make('medical_conditions')
                        ->label('Medical Conditions')
                        ->placeholder('None')
                        ->visible(fn ($record): bool => $record->has_medical_conditions)
                        ->columnSpanFull(),

                    IconEntry::make('requires_daily_medication')
                        ->label('Requires Daily Medication')
                        ->boolean()
                        ->trueColor('warning')
                        ->falseColor('success'),

                    TextEntry::make('daily_medications')
                        ->label('Daily Medications')
                        ->placeholder('None')
                        ->visible(fn ($record): bool => $record->requires_daily_medication)
                        ->columnSpanFull(),
                ]),

            Section::make('Special Needs & Learning Support')
                ->icon('heroicon-o-star')
                ->columns(2)
                ->schema([
                    IconEntry::make('has_special_needs')
                        ->label('Has Special Needs')
                        ->boolean()
                        ->trueColor('info')
                        ->falseColor('success'),

                    TextEntry::make('special_needs_description')
                        ->label('Special Needs')
                        ->placeholder('None')
                        ->visible(fn ($record): bool => $record->has_special_needs)
                        ->columnSpanFull(),

                    IconEntry::make('requires_learning_support')
                        ->label('Requires Learning Support')
                        ->boolean()
                        ->trueColor('warning')
                        ->falseColor('success'),

                    TextEntry::make('learning_support_details')
                        ->label('Learning Support Details')
                        ->placeholder('None')
                        ->visible(fn ($record): bool => $record->requires_learning_support)
                        ->columnSpanFull(),
                ]),

            Section::make('Immunizations')
                ->icon('heroicon-o-shield-check')
                ->columns(2)
                ->schema([
                    IconEntry::make('immunizations_up_to_date')
                        ->label('Immunizations Up-to-Date')
                        ->boolean()
                        ->trueColor('success')
                        ->falseColor('danger'),

                    TextEntry::make('immunization_records')
                        ->label('Immunization Records')
                        ->placeholder('No records provided')
                        ->columnSpanFull(),
                ]),

            Section::make('Emergency Contact')
                ->icon('heroicon-o-phone')
                ->columns(2)
                ->schema([
                    TextEntry::make('emergency_contact_name')
                        ->label('Name')
                        ->weight(FontWeight::Bold),

                    TextEntry::make('emergency_contact_relationship')
                        ->label('Relationship'),

                    TextEntry::make('emergency_contact_phone')
                        ->label('Phone')
                        ->copyable()
                        ->icon('heroicon-o-phone'),

                    TextEntry::make('emergency_contact_email')
                        ->label('Email')
                        ->copyable()
                        ->icon('heroicon-o-envelope'),
                ]),

            Section::make('Doctor & Insurance')
                ->icon('heroicon-o-building-office-2')
                ->columns(2)
                ->collapsed()
                ->schema([
                    TextEntry::make('doctor_name')
                        ->label('Family Doctor')
                        ->placeholder('Not provided'),

                    TextEntry::make('doctor_phone')
                        ->label('Doctor Phone')
                        ->placeholder('Not provided'),

                    TextEntry::make('hospital_preference')
                        ->label('Preferred Hospital')
                        ->placeholder('Not provided')
                        ->columnSpanFull(),

                    TextEntry::make('health_insurance_provider')
                        ->label('Insurance Provider')
                        ->placeholder('Not provided'),

                    TextEntry::make('health_insurance_number')
                        ->label('Policy Number')
                        ->placeholder('Not provided')
                        ->copyable(),
                ]),

            Section::make('Additional Notes')
                ->icon('heroicon-o-document-text')
                ->visible(fn ($record): bool => filled($record->additional_notes))
                ->schema([
                    TextEntry::make('additional_notes')
                        ->label('')
                        ->columnSpanFull(),
                ]),

        ]);
    }
}
