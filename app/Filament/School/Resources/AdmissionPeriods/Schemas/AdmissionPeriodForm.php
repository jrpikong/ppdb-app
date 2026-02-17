<?php

declare(strict_types=1);

namespace App\Filament\School\Resources\AdmissionPeriods\Schemas;

use App\Models\AcademicYear;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class AdmissionPeriodForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                // ── SECTION 1: Basic Information ──────────────────────────────
                Section::make('Period Information')
                    ->description('Define the basic details of this admission period.')
                    ->icon('heroicon-o-calendar-days')
                    ->columns(2)
                    ->schema([

                        // ✅ Scoped ke academic year milik tenant ini saja
                        Select::make('academic_year_id')
                            ->label('Academic Year')
                            ->required()
                            ->searchable()
                            ->native(false)
                            ->options(function (): array {
                                $schoolId = Filament::getTenant()?->id;

                                if (!$schoolId) {
                                    return [];
                                }

                                return AcademicYear::query()
                                    ->where('school_id', $schoolId)
                                    ->orderByDesc('start_date')
                                    ->get()
                                    ->mapWithKeys(fn ($year): array => [
                                        $year->id => $year->name . ($year->is_active ? ' ✅' : ''),
                                    ])
                                    ->toArray();
                            })
                            ->helperText('Select the academic year for this admission period.'),


                        TextInput::make('name')
                            ->label('Period Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g. 2025-2026 Intake 1')
                            ->prefixIcon('heroicon-o-tag'),

                    ]),

                // ── SECTION 2: Timeline ───────────────────────────────────────
                Section::make('Important Dates')
                    ->description('Set the key dates for this admission period.')
                    ->icon('heroicon-o-clock')
                    ->columns(2)
                    ->schema([

                        DatePicker::make('start_date')
                            ->label('Application Opens')
                            ->required()
                            ->native(false)
                            ->displayFormat('d M Y')
                            ->prefixIcon('heroicon-o-calendar')
                            ->helperText('Date when applications start being accepted.'),

                        DatePicker::make('end_date')
                            ->label('Application Closes')
                            ->required()
                            ->native(false)
                            ->displayFormat('d M Y')
                            ->prefixIcon('heroicon-o-calendar')
                            ->after('start_date')
                            ->helperText('Date when applications stop being accepted.'),

                        DatePicker::make('decision_date')
                            ->label('Decision Date')
                            ->nullable()
                            ->native(false)
                            ->displayFormat('d M Y')
                            ->prefixIcon('heroicon-o-megaphone')
                            ->helperText('When acceptance/rejection decisions are sent to parents.'),

                        DatePicker::make('enrollment_deadline')
                            ->label('Enrollment Deadline')
                            ->nullable()
                            ->native(false)
                            ->displayFormat('d M Y')
                            ->prefixIcon('heroicon-o-check-badge')
                            ->helperText('Deadline for accepted students to complete enrollment.'),

                    ]),

                // ── SECTION 3: Settings (Toggles) ─────────────────────────────
                Section::make('Period Settings')
                    ->description('Configure the behavior of this admission period.')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->columns(3)
                    ->schema([

                        Toggle::make('is_active')
                            ->label('Active Period')
                            ->helperText('Makes this the current active admission period for this school.')
                            ->default(false)
                            ->inline(false),

                        Toggle::make('allow_applications')
                            ->label('Accept Applications')
                            ->helperText('When enabled, parents can submit new applications.')
                            ->default(true)
                            ->inline(false),

                        Toggle::make('is_rolling')
                            ->label('Rolling Admission')
                            ->helperText('Applications reviewed continuously with no fixed closing date.')
                            ->default(false)
                            ->inline(false),

                    ]),

                // ── SECTION 4: Description & Extra Settings ───────────────────
                Section::make('Additional Information')
                    ->description('Optional description and custom settings.')
                    ->icon('heroicon-o-information-circle')
                    ->collapsed()
                    ->schema([

                        Textarea::make('description')
                            ->label('Description')
                            ->placeholder('Add any notes or details about this admission period...')
                            ->maxLength(1000)
                            ->rows(3)
                            ->columnSpanFull(),

                        KeyValue::make('settings')
                            ->label('Custom Settings (JSON)')
                            ->helperText('Advanced: add custom key-value settings for this period.')
                            ->columnSpanFull(),

                    ]),

            ]);
    }
}
