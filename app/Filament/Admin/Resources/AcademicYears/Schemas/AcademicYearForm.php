<?php

namespace App\Filament\Admin\Resources\AcademicYears\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AcademicYearForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Tahun Ajaran')
                    ->description('Masukkan informasi tahun ajaran')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nama Tahun Ajaran')
                                    ->placeholder('Contoh: 2024/2025')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true)
                                    ->helperText('Format: YYYY/YYYY'),

                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('start_year')
                                            ->label('Tahun Mulai')
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
                                            ->label('Tahun Selesai')
                                            ->placeholder('2025')
                                            ->required()
                                            ->numeric()
                                            ->minValue(fn(callable $get) => $get('start_year') ?: 2020)
                                            ->maxValue(2050)
                                            ->reactive()
                                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                if ($state && $get('start_year')) {
                                                    $set('name', $get('start_year') . '/' . $state);
                                                }
                                            }),
                                    ]),
                            ]),

                        Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->helperText('Hanya satu tahun ajaran yang bisa aktif')
                            ->default(false)
                            ->inline(false),

                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->placeholder('Opsional: Catatan tambahan tentang tahun ajaran ini')
                            ->maxLength(500)
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
