<?php

declare(strict_types=1);

namespace App\Filament\School\Resources\Enrollments\Schemas;

use App\Models\Application;
use App\Models\Enrollment;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EnrollmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()
                    ->schema([

                        // ── SECTION 1: Student & Application ─────────────
                        Section::make('Student Information')
                            ->icon('heroicon-o-user')
                            ->columns(2)
                            ->schema([

                                Select::make('application_id')
                                    ->label('Application')
                                    ->required()
                                    ->searchable()
                                    ->native(false)
                                    ->options(function (): array {
                                        $schoolId = Filament::getTenant()?->id;
                                        if (!$schoolId) return [];

                                        return Application::query()
                                            ->where('school_id', $schoolId)
                                            ->where('status', 'accepted')
                                            ->whereDoesntHave('enrollment') // Only show accepted apps without enrollment
                                            ->with('user')
                                            ->get()
                                            ->mapWithKeys(fn ($app): array => [
                                                $app->id => "[{$app->application_number}] " .
                                                    ($app->student_first_name . ' ' . $app->student_last_name),
                                            ])
                                            ->toArray();
                                    })
                                    ->helperText('Only accepted applications without enrollment are shown.')
                                    ->disabled(fn (?Enrollment $record) => $record !== null)
                                    ->columnSpanFull(),

                                TextInput::make('student_id')
                                    ->label('Student ID')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->placeholder('Auto-generated upon save')
                                    ->helperText('Format: VIS-BIN-2024-S-0001'),

                                TextInput::make('enrollment_number')
                                    ->label('Enrollment Number')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->placeholder('Auto-generated upon save')
                                    ->helperText('Format: ENR-2024-0001'),

                            ]),

                        // ── SECTION 2: Enrollment Details ────────────────
                        Section::make('Enrollment Details')
                            ->icon('heroicon-o-calendar')
                            ->columns(2)
                            ->schema([

                                DatePicker::make('enrollment_date')
                                    ->label('Enrollment Date')
                                    ->required()
                                    ->native(false)
                                    ->displayFormat('d M Y')
                                    ->default(today())
                                    ->maxDate(today()),

                                DatePicker::make('start_date')
                                    ->label('Expected Start Date')
                                    ->required()
                                    ->native(false)
                                    ->displayFormat('d M Y')
                                    ->helperText('First day of school'),

                                Select::make('status')
                                    ->label('Enrollment Status')
                                    ->required()
                                    ->native(false)
                                    ->options([
                                        'enrolled'     => '📝 Enrolled',
                                        'active'       => '✅ Active',
                                        'completed'    => '🎓 Completed',
                                        'transferred'  => '🔄 Transferred',
                                        'withdrawn'    => '❌ Withdrawn',
                                        'expelled'     => '🚫 Expelled',
                                        'graduated'    => '👨‍🎓 Graduated',
                                    ])
                                    ->default('enrolled'),

                                Select::make('payment_status')
                                    ->label('Payment Status')
                                    ->required()
                                    ->native(false)
                                    ->options([
                                        'pending' => '⏳ Pending',
                                        'partial' => '💳 Partial',
                                        'paid'    => '✅ Paid',
                                    ])
                                    ->default('pending'),

                            ]),

                        // ── SECTION 3: Class Assignment ──────────────────
                        Section::make('Class Assignment')
                            ->icon('heroicon-o-academic-cap')
                            ->columns(2)
                            ->schema([

                                TextInput::make('class_name')
                                    ->label('Class Name')
                                    ->placeholder('e.g. Grade 1A, Preschool Sunshine')
                                    ->maxLength(100),

                                TextInput::make('homeroom_teacher')
                                    ->label('Homeroom Teacher')
                                    ->placeholder('e.g. Mrs. Smith')
                                    ->maxLength(100),

                            ]),

                        // ── SECTION 4: Financial Summary ─────────────────
                        Section::make('Payment Summary')
                            ->icon('heroicon-o-currency-dollar')
                            ->columns(3)
                            ->schema([

                                TextInput::make('total_amount_due')
                                    ->label('Total Amount Due')
                                    ->required()
                                    ->numeric()
                                    ->prefix('IDR')
                                    ->default(0)
                                    ->helperText('Total fees for this enrollment'),

                                TextInput::make('total_amount_paid')
                                    ->label('Total Paid')
                                    ->numeric()
                                    ->prefix('IDR')
                                    ->default(0)
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $due = $get('total_amount_due') ?? 0;
                                        $paid = $state ?? 0;
                                        $set('balance', max(0, $due - $paid));
                                    }),

                                TextInput::make('balance')
                                    ->label('Balance')
                                    ->numeric()
                                    ->prefix('IDR')
                                    ->disabled()
                                    ->dehydrated()
                                    ->default(0),

                            ]),

                        // ── SECTION 5: Withdrawal Info ───────────────────
                        Section::make('Withdrawal Information')
                            ->icon('heroicon-o-x-circle')
                            ->columns(2)
                            ->collapsed()
                            ->visible(fn ($get) => in_array($get('status'), ['withdrawn', 'expelled', 'transferred']))
                            ->schema([

                                DatePicker::make('withdrawal_date')
                                    ->label('Withdrawal Date')
                                    ->native(false)
                                    ->displayFormat('d M Y')
                                    ->maxDate(today()),

                                Textarea::make('withdrawal_reason')
                                    ->label('Reason')
                                    ->rows(3)
                                    ->columnSpanFull(),

                            ]),

                        // ── SECTION 6: Notes ──────────────────────────────
                        Section::make('Notes')
                            ->icon('heroicon-o-document-text')
                            ->collapsed()
                            ->schema([

                                Textarea::make('notes')
                                    ->label('Additional Notes')
                                    ->rows(3)
                                    ->columnSpanFull(),

                            ]),

                    ])
                    ->columnSpan(['lg' => fn (?Enrollment $record) => $record === null ? 3 : 2]),

                // ── SIDEBAR: Metadata & Stats (only on edit) ─────────
                Section::make()
                    ->schema([
                        TextEntry::make('enrolledBy.name')
                            ->label('Enrolled By'),

                        TextEntry::make('created_at')
                            ->label('Created')
                            ->dateTime('d M Y, H:i'),

                        TextEntry::make('updated_at')
                            ->label('Last Updated')
                            ->dateTime('d M Y, H:i'),

                        TextEntry::make('payment_percentage')
                            ->label('Payment Progress')
                            ->state(fn (Enrollment $record): string =>
                                $record->payment_percentage . '%'
                            )
                            ->badge()
                            ->color(fn (Enrollment $record): string => match (true) {
                                $record->payment_percentage >= 100 => 'success',
                                $record->payment_percentage >= 50  => 'warning',
                                default                             => 'danger',
                            }),

                        TextEntry::make('days_enrolled')
                            ->label('Days Enrolled')
                            ->state(fn (Enrollment $record): string =>
                                $record->days_enrolled . ' days'
                            )
                            ->icon('heroicon-o-clock'),
                    ])
                    ->columnSpan(['lg' => 1])
                    ->hidden(fn (?Enrollment $record) => $record === null),

            ])
            ->columns(3);
    }
}
