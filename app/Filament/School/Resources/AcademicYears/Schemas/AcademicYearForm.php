<?php

namespace App\Filament\School\Resources\AcademicYears\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AcademicYearForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Academic Year Information')
                    ->description('Enter information about academic year')
                    ->schema([
                        Grid::make(2)
                            ->columnSpanFull()
                            ->schema([
                                TextInput::make('name')
                                    ->columnSpanFull()
                                    ->label('Name of Academic Year')
                                    ->placeholder('Example: 2024/2025')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true)
                                    ->helperText('Format: YYYY/YYYY'),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('start_year')
                                    ->label('Start Year')
                                    ->placeholder('2024')
                                    ->required()
                                    ->numeric()
                                    ->minValue(2020)
                                    ->maxValue(2050)
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        if ($state && !$get('end_year')) {
                                            $set('end_year', $state + 1);
                                        }
                                        if ($state && $get('end_year')) {
                                            $set('name', $state . '/' . $get('end_year'));
                                        }
                                    }),

                                TextInput::make('end_year')
                                    ->label('End Year')
                                    ->placeholder('2025')
                                    ->required()
                                    ->numeric()
                                    ->minValue(fn (callable $get) => $get('start_year') ?: 2020)
                                    ->maxValue(2050)
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        if ($state && $get('start_year')) {
                                            $set('name', $get('start_year') . '/' . $state);
                                        }
                                    }),
                            ]),

                        Grid::make(2)
                            ->schema([
                                DatePicker::make('start_date')
                                    ->label('Starting Date')
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        if ($state && !$get('end_date')) {
                                            $set('end_date', $state);
                                        }
                                    }),

                                DatePicker::make('end_date')
                                    ->label('End Date')
                                    ->required()
                                    ->minDate(fn (callable $get) => $get('start_date')),
                            ]),

                        Toggle::make('is_active')
                            ->label('Status Active')
                            ->helperText('Only one academy year can be active')
                            ->default(false)
                            ->inline(false),

                        Textarea::make('description')
                            ->label('Description')
                            ->placeholder('Optional: Additional notes about this school year')
                            ->maxLength(500)
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
