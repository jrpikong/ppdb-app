<?php

namespace App\Filament\School\Resources\AdmissionPeriods\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AdmissionPeriodForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Admission Periods')
                    ->description('SettingAdmission Period Form')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('academic_year_id')
                                    ->label('Academic Year')
                                    ->required()
                                    ->preload()
                                    ->options(function () {
                                        return \App\Models\AcademicYear::query()
                                            ->orderByDesc('start_year')
                                            ->get()
                                            ->mapWithKeys(function ($year) {
                                                $status = $year->is_active ? 'Active' : 'Inactive';
                                                return [$year->id => "{$year->name} ({$status})"];
                                            })
                                            ->toArray();
                                    }),

                                TextInput::make('name')
                                    ->label('Period Name')
                                    ->required()
                                    ->maxLength(255),

                                DatePicker::make('start_date')
                                    ->label('Start Date')
                                    ->required(),

                                DatePicker::make('end_date')
                                    ->label('End Date')
                                    ->required()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        if ($state < $get('start_date')) {
                                            $set('start_date', $state);
                                        }
                                    }),

                                DatePicker::make('decision_date')
                                    ->label('Decision Date')
                                    ->nullable(),

                                DatePicker::make('enrollment_deadline')
                                    ->label('Enrollment Deadline')
                                    ->nullable(),

                                Toggle::make('is_active')
                                    ->label('Active Status')
                                    ->helperText('Only one period can be active per school')
                                    ->default(false),

                                Toggle::make('allow_applications')
                                    ->label('Allow Applications')
                                    ->default(true),

                                Toggle::make('is_rolling')
                                    ->label('Rolling')
                                    ->helperText('If rolling, the period is always considered open')
                                    ->default(false),

                                Textarea::make('description')
                                    ->label('Description')
                                    ->maxLength(500)
                                    ->rows(3),

                                KeyValue::make('settings')
                                    ->label('Settings')
                                    ->columnSpanFull(),
                            ]),
                    ])
            ]);
    }
}
