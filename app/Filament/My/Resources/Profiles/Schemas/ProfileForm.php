<?php

declare(strict_types=1);

namespace App\Filament\My\Resources\Profiles\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProfileForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Account Information')
                    ->description('Manage your parent account details used across all applications.')
                    ->icon('heroicon-o-user')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Full Name')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),

                        TextInput::make('phone')
                            ->label('Phone Number')
                            ->tel()
                            ->maxLength(20),
                    ]),

                Section::make('Parent Details')
                    ->description('Optional details to speed up future application forms.')
                    ->icon('heroicon-o-identification')
                    ->columns(2)
                    ->schema([
                        TextInput::make('occupation')
                            ->label('Occupation')
                            ->maxLength(150),
                        Textarea::make('address')
                            ->label('Address')
                            ->rows(3)
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ]),

                Section::make('Email Verification')
                    ->icon('heroicon-o-shield-check')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('email_verified_at')
                            ->label('Verification Status')
                            ->default(fn ($record): string => $record?->email_verified_at
                                ? 'Verified at '.$record->email_verified_at->format('d M Y H:i')
                                : 'Not verified'),
                    ]),
            ]);
    }
}

