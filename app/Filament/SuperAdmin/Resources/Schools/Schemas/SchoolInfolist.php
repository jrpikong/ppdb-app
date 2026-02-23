<?php

declare(strict_types=1);

namespace App\Filament\SuperAdmin\Resources\Schools\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SchoolInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('School Overview')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('code')
                            ->badge()
                            ->color('info'),
                        TextEntry::make('name'),
                        TextEntry::make('full_name')
                            ->placeholder('-'),
                        TextEntry::make('type')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                        TextEntry::make('city'),
                        TextEntry::make('country'),
                    ]),

                Section::make('Contact')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('email')
                            ->placeholder('-'),
                        TextEntry::make('phone')
                            ->placeholder('-'),
                        TextEntry::make('website')
                            ->placeholder('-')
                            ->url(fn (?string $state): ?string => $state),
                        TextEntry::make('address')
                            ->placeholder('-'),
                    ]),

                Section::make('Tenant Provisioning')
                    ->columns(4)
                    ->schema([
                        IconEntry::make('is_active')
                            ->label('School Active')
                            ->boolean(),
                        IconEntry::make('allow_online_admission')
                            ->label('Online Admission')
                            ->boolean(),
                        TextEntry::make('users_count')
                            ->label('Total Users')
                            ->state(fn ($record): int => $record->users()->count()),
                        TextEntry::make('roles_count')
                            ->label('Tenant Roles')
                            ->state(fn ($record): int => $record->roles()->count()),
                    ]),
            ]);
    }
}

