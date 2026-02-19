<?php

declare(strict_types=1);

namespace App\Filament\School\Resources\Users\Schemas;

use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // SECTION 1: Personal Information
                Section::make('Personal Information')
                    ->description('Basic identity and contact details for this staff member.')
                    ->icon('heroicon-o-user-circle')
                    ->columns(2)
                    ->schema([
                        // Avatar full width
                        FileUpload::make('avatar')
                            ->label('Profile Photo')
                            ->image()
                            ->imageEditor()
                            ->circleCropper()
                            ->directory('avatars')
                            ->maxSize(2048)
                            ->columnSpanFull(),

                        TextInput::make('name')
                            ->label('Full Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g. Sarah Johnson')
                            ->prefixIcon('heroicon-o-user'),

                        TextInput::make('email')
                            ->label('Email Address')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->placeholder('staff@school.sch.id')
                            ->prefixIcon('heroicon-o-envelope'),

                        TextInput::make('phone')
                            ->label('Phone Number')
                            ->tel()
                            ->maxLength(20)
                            ->placeholder('+62-812-xxxx-xxxx')
                            ->prefixIcon('heroicon-o-phone'),

                        TextInput::make('employee_id')
                            ->label('Employee ID')
                            ->maxLength(50)
                            ->placeholder('e.g. EMP-001')
                            ->prefixIcon('heroicon-o-identification'),
                    ]),

                // SECTION 2: Department & Role
                Section::make('Role & Department')
                    ->description('Assign the staff member\'s role and department within this school.')
                    ->icon('heroicon-o-shield-check')
                    ->columns(2)
                    ->schema([
                        Select::make('roles')
                            ->relationship(
                                'roles',
                                'name',
                                modifyQueryUsing: fn (Builder $query) => $query
                                    ->where('roles.school_id', Filament::getTenant()?->id)
                            )
                            ->saveRelationshipsUsing(function (Model $record, $state) {
                                $record->roles()->syncWithPivotValues($state, [config('permission.column_names.team_foreign_key') => getPermissionsTeamId()]);
                            })
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->native(false)
                            ->helperText('Select the role for this staff member in your school.'),

                        TextInput::make('department')
                            ->label('Department')
                            ->maxLength(100)
                            ->placeholder('e.g. Admissions Office')
                            ->prefixIcon('heroicon-o-building-office'),
                    ]),

                // SECTION 3: Account Settings
                Section::make('Account Settings')
                    ->description('Password and account status configuration.')
                    ->icon('heroicon-o-lock-closed')
                    ->columns(2)
                    ->schema([
                        TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->revealable()
                            ->minLength(8)
                            ->maxLength(255)
                            ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->placeholder(fn (string $operation): string =>
                            $operation === 'create'
                                ? 'Set a strong password'
                                : 'Leave blank to keep current password'
                            )
                            ->helperText('Minimum 8 characters. Leave blank when editing to keep current password.'),

                        Toggle::make('is_active')
                            ->label('Active Account')
                            ->helperText('Inactive accounts cannot log in to the system.')
                            ->default(true)
                            ->inline(false),
                    ]),

                // SECTION 4: Readonly Meta (Edit only)
                Section::make('Account Information')
                    ->description('System-generated metadata.')
                    ->icon('heroicon-o-information-circle')
                    ->columns(2)
                    ->collapsed()
                    ->visibleOn('edit')
                    ->schema([
                        TextEntry::make('email_verified_at')
                            ->label('Email Verified')
                            ->default(fn ($record): string =>
                            $record?->email_verified_at
                                ? 'Verified at ' . $record->email_verified_at->format('d M Y, H:i')
                                : 'Not yet verified'
                            ),

                        TextEntry::make('created_at')
                            ->label('Account Created')
                            ->default(fn ($record): string =>
                                $record?->created_at?->format('d M Y, H:i') ?? '-'
                            ),
                    ]),
            ]);
    }
}
