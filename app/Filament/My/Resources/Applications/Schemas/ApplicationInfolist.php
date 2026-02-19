<?php

declare(strict_types=1);

namespace App\Filament\My\Resources\Applications\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ApplicationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Application')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('application_number')
                            ->badge()
                            ->color('primary'),
                        TextEntry::make('status')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => (string)str($state)->replace('_', ' ')->title()),
                        TextEntry::make('school.name')
                            ->label('School'),
                        TextEntry::make('admissionPeriod.name')
                            ->label('Admission Period'),
                        TextEntry::make('level.name')
                            ->label('Level/Grade'),
                        TextEntry::make('updated_at')
                            ->dateTime('d M Y H:i'),
                    ]),

                Section::make('Student')
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('student_first_name'),
                            TextEntry::make('student_last_name'),
                            TextEntry::make('gender')
                                ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                            TextEntry::make('birth_date')
                                ->date('d M Y'),
                            TextEntry::make('nationality'),
                            TextEntry::make('phone')
                                ->placeholder('-'),
                        ]),
                        TextEntry::make('current_address')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
