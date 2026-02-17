<?php

declare(strict_types=1);

namespace App\Filament\School\Resources\Users\Schemas;

use App\Models\User;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\Size;
use Filament\Support\Enums\TextSize;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                // ── SECTION 1: Profile Header ─────────────────────────────────
                Section::make()
                    ->schema([
                        Grid::make(4)
                            ->schema([

                                // Avatar
                                ImageEntry::make('-')
                                    ->label('')
                                    ->circular()
                                    ->defaultImageUrl(fn ($record): string =>
                                        'https://ui-avatars.com/api/?name=' . urlencode($record->name) .
                                        '&color=ffffff&background=6366f1&size=200'
                                    )
                                    ->imageSize(80)
                                    ->columnSpan(1),

                                // Name + email + role
                                Grid::make(1)
                                    ->columnSpan(3)
                                    ->schema([
                                        TextEntry::make('name')
                                            ->label('')
                                            ->size(TextSize::Large)
                                            ->weight(FontWeight::Bold),

                                        TextEntry::make('email')
                                            ->label('')
                                            ->icon('heroicon-o-envelope')
                                            ->iconColor('gray')
                                            ->copyable()
                                            ->copyMessage('Email copied!'),

                                        TextEntry::make('roles.name')
                                            ->label('Roles')
                                            ->badge()
                                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                                'super_admin'     => 'Super Admin',
                                                'school_admin'    => 'School Admin',
                                                'admission_admin' => 'Admission Admin',
                                                'finance_admin'   => 'Finance Admin',
                                                default           => ucwords(str_replace('_', ' ', $state)),
                                            })
                                            ->color(fn (string $state): string => match ($state) {
                                                'super_admin'     => 'danger',
                                                'school_admin'    => 'primary',
                                                'admission_admin' => 'warning',
                                                'finance_admin'   => 'success',
                                                default           => 'gray',
                                            }),
                                    ]),
                            ]),
                    ]),

                // ── SECTION 2: Contact Details ────────────────────────────────
                Section::make('Contact & Identity')
                    ->icon('heroicon-o-identification')
                    ->columns(2)
                    ->schema([

                        TextEntry::make('phone')
                            ->label('Phone Number')
                            ->icon('heroicon-o-phone')
                            ->placeholder('—')
                            ->copyable(),

                        TextEntry::make('employee_id')
                            ->label('Employee ID')
                            ->icon('heroicon-o-identification')
                            ->placeholder('—'),

                        TextEntry::make('department')
                            ->label('Department')
                            ->icon('heroicon-o-building-office')
                            ->placeholder('—'),

                        TextEntry::make('school.name')
                            ->label('School')
                            ->icon('heroicon-o-academic-cap')
                            ->badge()
                            ->color('primary'),

                    ]),

                // ── SECTION 3: Account Status ─────────────────────────────────
                Section::make('Account Status')
                    ->icon('heroicon-o-shield-check')
                    ->columns(2)
                    ->collapsed(false)
                    ->schema([

                        IconEntry::make('is_active')
                            ->label('Account Active')
                            ->boolean()
                            ->trueIcon('heroicon-o-check-circle')
                            ->falseIcon('heroicon-o-x-circle')
                            ->trueColor('success')
                            ->falseColor('danger'),

                        TextEntry::make('email_verified_at')
                            ->label('Email Verified')
                            ->dateTime('d M Y, H:i')
                            ->placeholder('Not yet verified')
                            ->icon('heroicon-o-shield-check')
                            ->iconColor(fn ($record): string =>
                            $record?->email_verified_at ? 'success' : 'danger'
                            ),

                    ]),

                // ── SECTION 4: Timestamps ─────────────────────────────────────
                Section::make('System Information')
                    ->icon('heroicon-o-clock')
                    ->columns(2)
                    ->collapsed(false)
                    ->schema([

                        TextEntry::make('created_at')
                            ->label('Account Created')
                            ->dateTime('d M Y, H:i')
                            ->icon('heroicon-o-calendar')
                            ->placeholder('—'),

                        TextEntry::make('updated_at')
                            ->label('Last Updated')
                            ->dateTime('d M Y, H:i')
                            ->since()
                            ->placeholder('—'),

                        // Hanya tampil jika record sudah dihapus
                        TextEntry::make('deleted_at')
                            ->label('Deleted At')
                            ->dateTime('d M Y, H:i')
                            ->placeholder('—')
                            ->color('danger')
                            ->icon('heroicon-o-trash')
                            ->visible(fn (User $record): bool => $record->trashed()),

                    ]),

            ]);
    }
}
