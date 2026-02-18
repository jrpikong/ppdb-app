<?php

declare(strict_types=1);

namespace App\Filament\School\Resources\Levels\Schemas;

use App\Models\Level;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LevelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()
                    ->schema([

                        // ── SECTION 1: Basic Information ─────────────────
                        Section::make('Level Information')
                            ->icon('heroicon-o-academic-cap')
                            ->columns(2)
                            ->schema([

                                TextInput::make('code')
                                    ->label('Code')
                                    ->required()
                                    ->maxLength(10)
                                    ->placeholder('e.g. G1, G2, EP, PS')
                                    ->helperText('Short unique identifier (e.g., G1, EP, PK)')
                                    ->prefixIcon('heroicon-o-hashtag')
                                    ->unique(ignoreRecord: true, modifyRuleUsing: fn ($rule, $get) =>
                                    $rule->where('school_id', \Filament\Facades\Filament::getTenant()?->id)
                                    ),

                                TextInput::make('name')
                                    ->label('Level Name')
                                    ->required()
                                    ->maxLength(100)
                                    ->placeholder('e.g. Grade 1, Early Preschool')
                                    ->prefixIcon('heroicon-o-tag'),

                                Select::make('program_category')
                                    ->label('Program Category')
                                    ->required()
                                    ->native(false)
                                    ->options([
                                        'early_years'   => '🧸 Early Years Program',
                                        'primary_years' => '📚 Primary Years Program',
                                        'middle_years'  => '🎓 Middle Years Program',
                                    ])
                                    ->helperText('Choose the program category'),

                                TextInput::make('sort_order')
                                    ->label('Sort Order')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->helperText('Lower numbers appear first')
                                    ->prefixIcon('heroicon-o-arrows-up-down'),

                            ]),

                        // ── SECTION 2: Age Requirements ──────────────────
                        Section::make('Age Requirements')
                            ->icon('heroicon-o-calendar')
                            ->columns(2)
                            ->schema([

                                TextInput::make('age_min')
                                    ->label('Minimum Age')
                                    ->required()
                                    ->numeric()
                                    ->step(0.1)
                                    ->minValue(0)
                                    ->maxValue(18)
                                    ->suffix('years')
                                    ->placeholder('e.g. 2.5')
                                    ->helperText('Minimum age for this level (decimals allowed: 2.5 = 2 years 6 months)'),

                                TextInput::make('age_max')
                                    ->label('Maximum Age')
                                    ->required()
                                    ->numeric()
                                    ->step(0.1)
                                    ->minValue(0)
                                    ->maxValue(18)
                                    ->suffix('years')
                                    ->placeholder('e.g. 3.5')
                                    ->helperText('Maximum age for this level'),

                            ]),

                        // ── SECTION 3: Capacity & Fees ───────────────────
                        Section::make('Capacity & Tuition')
                            ->icon('heroicon-o-currency-dollar')
                            ->columns(3)
                            ->schema([

                                TextInput::make('quota')
                                    ->label('Student Quota')
                                    ->required()
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->suffix('students')
                                    ->helperText('Maximum number of students'),

                                TextInput::make('current_enrollment')
                                    ->label('Current Enrollment')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->suffix('students')
                                    ->helperText('Currently enrolled students')
                                    ->disabled(fn (?Level $record) => $record === null),

                                TextInput::make('annual_tuition_fee')
                                    ->label('Annual Tuition Fee')
                                    ->required()
                                    ->numeric()
                                    ->default(0)
                                    ->prefix('IDR')
                                    ->placeholder('50000000')
                                    ->helperText('Annual tuition fee in IDR'),

                            ]),

                        // ── SECTION 4: Status & Settings ─────────────────
                        Section::make('Status')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->columns(2)
                            ->schema([

                                Toggle::make('is_active')
                                    ->label('Active')
                                    ->helperText('Inactive levels won\'t appear in selections')
                                    ->default(true)
                                    ->inline(false),

                                Toggle::make('is_accepting_applications')
                                    ->label('Accepting Applications')
                                    ->helperText('Whether this level is accepting new applications')
                                    ->default(true)
                                    ->inline(false),

                            ]),

                        // ── SECTION 5: Description ───────────────────────
                        Section::make('Additional Information')
                            ->icon('heroicon-o-document-text')
                            ->collapsed()
                            ->schema([

                                RichEditor::make('description')
                                    ->label('Description')
                                    ->placeholder('Describe the level, curriculum focus, etc...')
                                    ->columnSpanFull()
                                    ->toolbarButtons([
                                        'bold',
                                        'italic',
                                        'bulletList',
                                        'orderedList',
                                    ]),

                            ]),

                    ])
                    ->columnSpan(['lg' => fn (?Level $record) => $record === null ? 3 : 2]),

                // ── SIDEBAR: Metadata (only on edit) ─────────────────────
                Section::make()
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Created')
                            ->dateTime('d M Y, H:i'),

                        TextEntry::make('updated_at')
                            ->label('Last Updated')
                            ->dateTime('d M Y, H:i'),

                        TextEntry::make('applications_count')
                            ->label('Total Applications')
                            ->state(fn (Level $record): int => $record->applications()->count())
                            ->badge()
                            ->color('info'),

                        TextEntry::make('accepted_applications_count')
                            ->label('Accepted Applications')
                            ->state(fn (Level $record): int => $record->acceptedApplications()->count())
                            ->badge()
                            ->color('success'),
                    ])
                    ->columnSpan(['lg' => 1])
                    ->hidden(fn (?Level $record) => $record === null),

            ])
            ->columns(3);
    }
}
