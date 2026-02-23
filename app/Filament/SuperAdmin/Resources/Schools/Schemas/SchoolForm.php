<?php

declare(strict_types=1);

namespace App\Filament\SuperAdmin\Resources\Schools\Schemas;

use App\Models\School;
use App\Models\User;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SchoolForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('School Identity')
                    ->columns(2)
                    ->schema([
                        TextInput::make('code')
                            ->label('School Code')
                            ->required()
                            ->maxLength(20)
                            ->unique(table: School::class, column: 'code', ignoreRecord: true)
                            ->dehydrateStateUsing(fn (string $state): string => strtoupper(trim($state)))
                            ->rule('regex:/^[A-Z0-9-]+$/')
                            ->helperText('Example: VIS-BIN'),

                        TextInput::make('name')
                            ->label('School Name')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('full_name')
                            ->label('Full Legal Name')
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Select::make('type')
                            ->required()
                            ->native(false)
                            ->options([
                                'main' => 'Main Campus',
                                'branch' => 'Branch Campus',
                            ])
                            ->default('branch'),

                        Select::make('timezone')
                            ->required()
                            ->native(false)
                            ->searchable()
                            ->options([
                                'Asia/Jakarta' => 'Asia/Jakarta',
                                'Asia/Makassar' => 'Asia/Makassar',
                                'Asia/Jayapura' => 'Asia/Jayapura',
                            ])
                            ->default('Asia/Jakarta'),
                    ]),

                Section::make('Contact & Location')
                    ->columns(2)
                    ->schema([
                        TextInput::make('email')
                            ->email()
                            ->maxLength(255),

                        TextInput::make('phone')
                            ->tel()
                            ->maxLength(20),

                        TextInput::make('website')
                            ->url()
                            ->maxLength(255),

                        TextInput::make('city')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('country')
                            ->required()
                            ->maxLength(255)
                            ->default('Indonesia'),

                        TextInput::make('postal_code')
                            ->maxLength(10),

                        Textarea::make('address')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),

                Section::make('Branding & Principal')
                    ->columns(2)
                    ->schema([
                        FileUpload::make('logo')
                            ->image()
                            ->directory('schools/logos')
                            ->maxSize(2048),

                        FileUpload::make('banner')
                            ->image()
                            ->directory('schools/banners')
                            ->maxSize(4096),

                        TextInput::make('principal_name')
                            ->maxLength(255),

                        TextInput::make('principal_email')
                            ->email()
                            ->maxLength(255),

                        Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Section::make('Activation & Admission')
                    ->columns(2)
                    ->schema([
                        Toggle::make('is_active')
                            ->label('School Active')
                            ->default(true),

                        Toggle::make('allow_online_admission')
                            ->label('Allow Online Admission')
                            ->default(true),
                    ]),

                Section::make('Initial Tenant Super Admin')
                    ->description('Provision 1 school-level super admin user and a role template when this school is created.')
                    ->visibleOn('create')
                    ->columns(2)
                    ->schema([
                        TextInput::make('admin_name')
                            ->label('Admin Full Name')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('admin_email')
                            ->label('Admin Email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(table: User::class, column: 'email'),

                        TextInput::make('admin_password')
                            ->label('Admin Password')
                            ->password()
                            ->revealable()
                            ->required()
                            ->minLength(8)
                            ->same('admin_password_confirmation'),

                        TextInput::make('admin_password_confirmation')
                            ->label('Confirm Password')
                            ->password()
                            ->revealable()
                            ->required()
                            ->dehydrated(false),
                    ]),
            ]);
    }
}

