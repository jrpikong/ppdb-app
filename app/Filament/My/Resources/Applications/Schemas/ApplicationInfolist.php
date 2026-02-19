<?php

declare(strict_types=1);

namespace App\Filament\My\Resources\Applications\Schemas;

use App\Models\Application;
use App\Models\Document;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;

class ApplicationInfolist
{
    // ── Helpers ──────────────────────────────────────────────────────────────

    private static function statusColor(string $state): string
    {
        return match ($state) {
            'draft'                              => 'gray',
            'submitted'                          => 'info',
            'under_review', 'documents_verified' => 'warning',
            'interview_scheduled'                => 'purple',
            'interview_completed'                => 'violet',
            'payment_pending'                    => 'orange',
            'payment_verified'                   => 'lime',
            'accepted', 'enrolled'               => 'success',
            'rejected'                           => 'danger',
            'waitlisted'                         => 'amber',
            default                              => 'gray',
        };
    }

    private static function statusLabel(string $state): string
    {
        return (string) str($state)->replace('_', ' ')->title();
    }

    // ─────────────────────────────────────────────────────────────────────────

    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([

            // ══════════════════════════════════════════════════════════════
            // APPLICATION HEADER
            // ══════════════════════════════════════════════════════════════
            Section::make()
                ->schema([
                    Grid::make(4)->schema([
                        TextEntry::make('application_number')
                            ->label('Application #')
                            ->badge()
                            ->color('primary')
                            ->size(TextSize::Large)
                            ->weight(FontWeight::Bold)
                            ->copyable()
                            ->copyMessage('Copied!'),

                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->size(TextSize::Large)
                            ->weight(FontWeight::SemiBold)
                            ->formatStateUsing(fn (string $state): string => self::statusLabel($state))
                            ->color(fn (string $state): string => self::statusColor($state)),

                        TextEntry::make('school.name')
                            ->label('School')
                            ->icon(Heroicon::OutlinedBuildingLibrary)
                            ->color('neutral')
                            ->weight(FontWeight::Medium),

                        TextEntry::make('submitted_at')
                            ->label('Submitted On')
                            ->dateTime('d M Y, H:i')
                            ->placeholder('Not yet submitted')
                            ->icon(Heroicon::OutlinedPaperAirplane)
                            ->color('neutral'),
                    ]),

                    Grid::make(3)->schema([
                        TextEntry::make('admissionPeriod.name')
                            ->label('Admission Period')
                            ->icon(Heroicon::OutlinedCalendarDays)
                            ->color('neutral'),

                        TextEntry::make('level.name')
                            ->label('Applying for Level')
                            ->badge()
                            ->color('info'),

                        TextEntry::make('created_at')
                            ->label('Application Created')
                            ->dateTime('d M Y')
                            ->color('neutral'),
                    ]),

                    // School note — rendered as callout when present
                    Callout::make('Note from School')
                        ->color('warning')
                        ->icon(Heroicon::OutlinedChatBubbleLeftRight)
                        ->description(fn (Application $record): string => $record->status_notes ?? '')
                        ->visible(fn (Application $record): bool => filled($record->status_notes))
                        ->columnSpanFull(),
                ]),

            // ══════════════════════════════════════════════════════════════
            // STUDENT BIODATA
            // ══════════════════════════════════════════════════════════════
            Section::make('Student Information')
                ->icon(Heroicon::OutlinedUser)
                ->iconColor('primary')
                ->collapsible()
                ->schema([
                    Grid::make(3)->schema([
                        TextEntry::make('student_full_name')
                            ->label('Full Name')
                            ->getStateUsing(fn (Application $record): string =>
                            trim(implode(' ', array_filter([
                                $record->student_first_name,
                                $record->student_middle_name,
                                $record->student_last_name,
                            ])))
                            )
                            ->color('neutral')
                            ->weight(FontWeight::Bold)
                            ->size(TextSize::Medium),

                        TextEntry::make('student_preferred_name')
                            ->label('Preferred Name')
                            ->placeholder('—')
                            ->color('neutral'),

                        TextEntry::make('gender')
                            ->label('Gender')
                            ->formatStateUsing(fn (?string $state): string => match ($state) {
                                'male'   => '♂  Male',
                                'female' => '♀  Female',
                                default  => '—',
                            })
                            ->color('neutral'),

                        TextEntry::make('birth_date')
                            ->label('Date of Birth')
                            ->date('d M Y')
                            ->color('neutral'),

                        TextEntry::make('birth_place')
                            ->label('Place of Birth')
                            ->placeholder('—')
                            ->color('neutral'),

                        TextEntry::make('nationality')
                            ->label('Nationality')
                            ->placeholder('—')
                            ->icon(Heroicon::OutlinedFlag)
                            ->color('neutral'),

                        TextEntry::make('passport_number')
                            ->label('Passport / NIK')
                            ->placeholder('—')
                            ->copyable()
                            ->copyMessage('Copied!')
                            ->color('neutral'),

                        TextEntry::make('email')
                            ->label('Student Email')
                            ->placeholder('—')
                            ->icon(Heroicon::OutlinedEnvelope)
                            ->color('neutral'),

                        TextEntry::make('phone')
                            ->label('Student Phone')
                            ->placeholder('—')
                            ->icon(Heroicon::OutlinedPhone)
                            ->color('neutral'),
                    ]),
                ]),

            // ══════════════════════════════════════════════════════════════
            // ADDRESS & PREVIOUS SCHOOL
            // ══════════════════════════════════════════════════════════════
            Section::make('Address & Previous School')
                ->icon(Heroicon::OutlinedMapPin)
                ->iconColor('warning')
                ->collapsible()
                ->schema([
                    // Current address sub-section
                    Section::make('Current Address')
                        ->compact()
                        ->secondary()
                        ->columns(3)
                        ->schema([
                            TextEntry::make('current_address')
                                ->label('Street Address')
                                ->placeholder('—')
                                ->color('neutral')
                                ->columnSpan(3),

                            TextEntry::make('current_city')
                                ->label('City')
                                ->placeholder('—')
                                ->color('neutral'),

                            TextEntry::make('current_country')
                                ->label('Country')
                                ->placeholder('—')
                                ->color('neutral'),

                            TextEntry::make('current_postal_code')
                                ->label('Postal Code')
                                ->placeholder('—')
                                ->color('neutral'),
                        ]),

                    // Previous school sub-section
                    Section::make('Previous School')
                        ->compact()
                        ->secondary()
                        ->columns(3)
                        ->schema([
                            TextEntry::make('previous_school_name')
                                ->label('School Name')
                                ->placeholder('—')
                                ->color('neutral')
                                ->columnSpan(2),

                            TextEntry::make('previous_school_country')
                                ->label('Country')
                                ->placeholder('—')
                                ->color('neutral'),

                            TextEntry::make('current_grade_level')
                                ->label('Grade Level')
                                ->placeholder('—')
                                ->color('neutral'),

                            TextEntry::make('previous_school_start_date')
                                ->label('From')
                                ->date('M Y')
                                ->placeholder('—')
                                ->color('neutral'),

                            TextEntry::make('previous_school_end_date')
                                ->label('To')
                                ->date('M Y')
                                ->placeholder('—')
                                ->color('neutral'),
                        ]),

                    TextEntry::make('languages_spoken')
                        ->label('Languages Spoken')
                        ->placeholder('—')
                        ->color('neutral'),

                    TextEntry::make('interests_hobbies')
                        ->label('Interests & Hobbies')
                        ->placeholder('—')
                        ->color('neutral'),
                ]),

            // ══════════════════════════════════════════════════════════════
            // PARENT / GUARDIAN
            // ══════════════════════════════════════════════════════════════
            Section::make('Parent / Guardian Contacts')
                ->icon(Heroicon::OutlinedUserGroup)
                ->iconColor('info')
                ->collapsible()
                ->schema([
                    RepeatableEntry::make('parentGuardians')
                        ->label('')
                        ->contained(false)
                        ->schema([
                            Grid::make(4)->schema([
                                TextEntry::make('type')
                                    ->label('Type')
                                    ->badge()
                                    ->formatStateUsing(fn (string $state): string => match ($state) {
                                        'father'   => 'Father',
                                        'mother'   => 'Mother',
                                        'guardian' => 'Guardian',
                                        default    => ucfirst($state),
                                    })
                                    ->color(fn (string $state): string => match ($state) {
                                        'father'   => 'blue',
                                        'mother'   => 'pink',
                                        'guardian' => 'violet',
                                        default    => 'gray',
                                    }),

                                TextEntry::make('full_name')
                                    ->label('Full Name')
                                    ->getStateUsing(fn ($record): string =>
                                    trim(implode(' ', array_filter([
                                        $record->first_name,
                                        $record->middle_name,
                                        $record->last_name,
                                    ])))
                                    )
                                    ->color('neutral')
                                    ->weight(FontWeight::SemiBold),

                                TextEntry::make('email')
                                    ->label('Email')
                                    ->placeholder('—')
                                    ->copyable()
                                    ->icon(Heroicon::OutlinedEnvelope)
                                    ->color('neutral'),

                                TextEntry::make('mobile')
                                    ->label('Mobile')
                                    ->placeholder('—')
                                    ->icon(Heroicon::OutlinedPhone)
                                    ->color('neutral'),
                            ]),

                            Grid::make(4)->schema([
                                TextEntry::make('occupation')
                                    ->label('Occupation')
                                    ->placeholder('—')
                                    ->color('neutral'),

                                TextEntry::make('company_name')
                                    ->label('Company')
                                    ->placeholder('—')
                                    ->color('neutral'),

                                IconEntry::make('is_primary_contact')
                                    ->label('Primary Contact')
                                    ->boolean()
                                    ->trueIcon(Heroicon::OutlinedStar)
                                    ->falseIcon(Heroicon::OutlinedXMark)
                                    ->trueColor('warning')
                                    ->falseColor('gray'),

                                IconEntry::make('is_emergency_contact')
                                    ->label('Emergency Contact')
                                    ->boolean()
                                    ->trueIcon(Heroicon::OutlinedBellAlert)
                                    ->falseIcon(Heroicon::OutlinedXMark)
                                    ->trueColor('danger')
                                    ->falseColor('gray'),
                            ]),
                        ]),
                ]),

            // ══════════════════════════════════════════════════════════════
            // MEDICAL INFORMATION
            // ══════════════════════════════════════════════════════════════
            Section::make('Medical Information')
                ->icon(Heroicon::OutlinedHeart)
                ->iconColor('danger')
                ->collapsible()
                ->collapsed(false)
                ->visible(fn (Application $record): bool => $record->medicalRecord !== null)
                ->schema([
                    Grid::make(3)->schema([
                        TextEntry::make('medicalRecord.blood_type')
                            ->label('Blood Type')
                            ->badge()
                            ->color('danger')
                            ->placeholder('Unknown'),

                        TextEntry::make('medicalRecord.height')
                            ->label('Height')
                            ->formatStateUsing(fn ($state): string => $state ? "{$state} cm" : '—')
                            ->color('neutral'),

                        TextEntry::make('medicalRecord.weight')
                            ->label('Weight')
                            ->formatStateUsing(fn ($state): string => $state ? "{$state} kg" : '—')
                            ->color('neutral'),
                    ]),

                    Section::make('Conditions & Needs')
                        ->compact()
                        ->secondary()
                        ->columns(2)
                        ->schema([
                            IconEntry::make('medicalRecord.has_food_allergies')
                                ->label('Food Allergies')
                                ->boolean()
                                ->trueColor('warning')
                                ->falseColor('gray'),

                            TextEntry::make('medicalRecord.food_allergies_details')
                                ->label('Allergy Details')
                                ->placeholder('—')
                                ->color('neutral'),

                            IconEntry::make('medicalRecord.has_medical_conditions')
                                ->label('Medical Conditions')
                                ->boolean()
                                ->trueColor('danger')
                                ->falseColor('gray'),

                            TextEntry::make('medicalRecord.medical_conditions_details')
                                ->label('Details')
                                ->placeholder('—')
                                ->color('neutral'),

                            IconEntry::make('medicalRecord.requires_daily_medication')
                                ->label('Daily Medication')
                                ->boolean()
                                ->trueColor('warning')
                                ->falseColor('gray'),

                            TextEntry::make('medicalRecord.medications_details')
                                ->label('Medication Details')
                                ->placeholder('—')
                                ->color('neutral'),

                            IconEntry::make('medicalRecord.has_dietary_restrictions')
                                ->label('Dietary Restrictions')
                                ->boolean()
                                ->trueColor('info')
                                ->falseColor('gray'),

                            TextEntry::make('medicalRecord.dietary_restrictions_details')
                                ->label('Dietary Details')
                                ->placeholder('—')
                                ->color('neutral'),

                            IconEntry::make('medicalRecord.has_special_needs')
                                ->label('Special Educational Needs')
                                ->boolean()
                                ->trueColor('purple')
                                ->falseColor('gray'),

                            TextEntry::make('medicalRecord.special_needs_details')
                                ->label('Special Needs Details')
                                ->placeholder('—')
                                ->color('neutral'),

                            IconEntry::make('medicalRecord.immunizations_up_to_date')
                                ->label('Immunizations Up to Date')
                                ->boolean()
                                ->trueColor('success')
                                ->falseColor('danger')
                                ->columnSpan(2),
                        ]),

                    Section::make('Emergency Medical Contact')
                        ->compact()
                        ->secondary()
                        ->columns(3)
                        ->schema([
                            TextEntry::make('medicalRecord.emergency_contact_name')
                                ->label('Contact Name')
                                ->placeholder('—')
                                ->color('neutral')
                                ->weight(FontWeight::SemiBold),

                            TextEntry::make('medicalRecord.emergency_contact_phone')
                                ->label('Phone')
                                ->placeholder('—')
                                ->icon(Heroicon::OutlinedPhone)
                                ->color('neutral'),

                            TextEntry::make('medicalRecord.emergency_contact_relationship')
                                ->label('Relationship')
                                ->placeholder('—')
                                ->color('neutral'),

                            TextEntry::make('medicalRecord.doctor_name')
                                ->label('Family Doctor')
                                ->placeholder('—')
                                ->color('neutral'),

                            TextEntry::make('medicalRecord.doctor_phone')
                                ->label('Doctor Phone')
                                ->placeholder('—')
                                ->icon(Heroicon::OutlinedPhone)
                                ->color('neutral'),

                            TextEntry::make('medicalRecord.hospital_preference')
                                ->label('Preferred Hospital')
                                ->placeholder('—')
                                ->color('neutral'),
                        ]),

                    // ✅ BENAR — TextEntry dengan visual menyerupai callout
                    TextEntry::make('medicalRecord.additional_notes')
                        ->label('Additional Medical Notes')
                        ->placeholder('—')
                        ->color('neutral')
                        ->icon(Heroicon::OutlinedInformationCircle)
                        ->iconColor('info')
                        ->columnSpanFull()
                        ->visible(fn (Application $record): bool =>
                        filled($record->medicalRecord?->additional_notes)
                        ),
                ]),

            // ══════════════════════════════════════════════════════════════
            // UPLOADED DOCUMENTS
            // ══════════════════════════════════════════════════════════════
            Section::make('Uploaded Documents')
                ->icon(Heroicon::OutlinedPaperClip)
                ->iconColor('gray')
                ->collapsible()
                ->schema([
                    RepeatableEntry::make('documents')
                        ->label('')
                        ->contained(false)
                        ->schema([
                            Grid::make(4)->schema([
                                TextEntry::make('type')
                                    ->label('Type')
                                    ->formatStateUsing(fn (string $state): string =>
                                        Document::DOCUMENT_TYPES[$state]
                                        ?? (string) str($state)->replace('_', ' ')->title()
                                    )
                                    ->badge()
                                    ->color('gray'),

                                TextEntry::make('name_and_size')
                                    ->label('File')
                                    ->getStateUsing(function ($record): string {
                                        $name = $record->name ?? '—';

                                        if (! $record->file_size) {
                                            return $name;
                                        }

                                        $size = $record->file_size >= 1_048_576
                                            ? round($record->file_size / 1_048_576, 1) . ' MB'
                                            : round($record->file_size / 1024, 0) . ' KB';

                                        return "{$name} ({$size})";
                                    })
                                    ->color('neutral')
                                    ->weight(FontWeight::Medium),

                                TextEntry::make('status')
                                    ->label('Verification')
                                    ->badge()
                                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                                    ->color(fn (string $state): string => match ($state) {
                                        'verified' => 'success',
                                        'rejected' => 'danger',
                                        'pending'  => 'warning',
                                        default    => 'gray',
                                    }),

                                // Clickable link to view / download the file
                                TextEntry::make('file_path')
                                    ->label('File')
                                    ->formatStateUsing(fn (?string $state): string =>
                                    $state ? 'View / Download' : '—'
                                    )
                                    ->url(fn (?string $state): ?string =>
                                    $state ? Storage::disk('public')->url($state) : null
                                    )
                                    ->openUrlInNewTab()
                                    ->color('primary'),
                            ]),

                            // ⚠️ Rejection callout — only when rejected
                            Callout::make('Rejection Reason')
                                ->color('danger')
                                ->icon(Heroicon::OutlinedExclamationTriangle)
                                ->description(fn ($record): string => $record->rejection_reason ?? '')
                                ->visible(fn ($record): bool =>
                                    $record->status === 'rejected' && filled($record->rejection_reason)
                                )
                                ->columnSpanFull(),
                        ]),
                ]),

            // ══════════════════════════════════════════════════════════════
            // PAYMENTS
            // ══════════════════════════════════════════════════════════════
            Section::make('Payments')
                ->icon(Heroicon::OutlinedCreditCard)
                ->iconColor('success')
                ->collapsible()
                ->collapsed()
                ->visible(fn (Application $record): bool => $record->payments->isNotEmpty())
                ->schema([
                    RepeatableEntry::make('payments')
                        ->label('')
                        ->contained(false)
                        ->schema([
                            Grid::make(4)->schema([
                                TextEntry::make('paymentType.name')
                                    ->label('Payment Type')
                                    ->color('neutral')
                                    ->weight(FontWeight::Medium),

                                TextEntry::make('amount')
                                    ->label('Amount')
                                    ->money('IDR')
                                    ->color('neutral')
                                    ->weight(FontWeight::Bold),

                                TextEntry::make('status')
                                    ->label('Status')
                                    ->badge()
                                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                                    ->color(fn (string $state): string => match ($state) {
                                        'verified' => 'success',
                                        'rejected' => 'danger',
                                        'pending'  => 'warning',
                                        'refunded' => 'gray',
                                        default    => 'gray',
                                    }),

                                TextEntry::make('paid_at')
                                    ->label('Paid On')
                                    ->dateTime('d M Y, H:i')
                                    ->placeholder('—')
                                    ->color('neutral'),
                            ]),

                            Callout::make('Note')
                                ->color('warning')
                                ->icon(Heroicon::OutlinedInformationCircle)
                                ->description(fn ($record): string => $record->notes ?? '')
                                ->visible(fn ($record): bool =>
                                    in_array($record->status, ['rejected', 'refunded'], true) && filled($record->notes)
                                )
                                ->columnSpanFull(),
                        ]),
                ]),

            // ══════════════════════════════════════════════════════════════
            // ENROLLMENT
            // ══════════════════════════════════════════════════════════════
            Section::make('Enrollment')
                ->icon(Heroicon::OutlinedAcademicCap)
                ->iconColor('success')
                ->collapsible()
                ->collapsed()
                ->visible(fn (Application $record): bool => $record->enrollment !== null)
                ->schema([
                    Grid::make(3)->schema([
                        TextEntry::make('enrollment.student_id_number')
                            ->label('Student ID')
                            ->badge()
                            ->color('success')
                            ->size(TextSize::Large)
                            ->weight(FontWeight::Bold)
                            ->copyable()
                            ->copyMessage('Student ID copied!'),

                        TextEntry::make('enrollment.enrollment_date')
                            ->label('Enrolled On')
                            ->date('d M Y')
                            ->icon(Heroicon::OutlinedCalendarDays)
                            ->color('neutral'),

                        TextEntry::make('enrollment.status')
                            ->label('Enrollment Status')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => ucfirst($state))
                            ->color(fn (string $state): string => match ($state) {
                                'active'    => 'success',
                                'withdrawn' => 'danger',
                                'graduated' => 'info',
                                default     => 'gray',
                            }),
                    ]),

                    TextEntry::make('enrollment.notes')
                        ->label('Enrollment Notes')
                        ->placeholder('—')
                        ->color('neutral')
                        ->columnSpanFull()
                        ->visible(fn (Application $record): bool => filled($record->enrollment?->notes)),
                ]),
        ]);
    }
}
