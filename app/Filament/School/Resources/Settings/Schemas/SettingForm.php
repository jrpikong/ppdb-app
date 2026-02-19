<?php

namespace App\Filament\School\Resources\Settings\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('System')
                    ->columns(2)
                    ->schema([
                        TextInput::make('app_name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('app_version')
                            ->required()
                            ->maxLength(20),
                        Toggle::make('multi_school_enabled'),
                        Select::make('default_school_id')
                            ->relationship('defaultSchool', 'name')
                            ->searchable()
                            ->preload(),
                    ]),

                Section::make('Admission')
                    ->columns(3)
                    ->schema([
                        Toggle::make('online_admission_enabled')
                            ->label('Online Admission Enabled'),
                        Toggle::make('require_payment_before_submission'),
                        TextInput::make('application_review_days')
                            ->numeric()
                            ->minValue(1)
                            ->required(),
                    ]),

                Section::make('Email')
                    ->columns(2)
                    ->schema([
                        Toggle::make('email_notifications_enabled'),
                        TextInput::make('email_from_name')->maxLength(255),
                        TextInput::make('email_from_address')->email()->maxLength(255),
                        Toggle::make('send_submission_confirmation'),
                        Toggle::make('send_status_updates'),
                        Toggle::make('send_interview_reminders'),
                        Toggle::make('send_acceptance_letters'),
                    ]),

                Section::make('Payments & Documents')
                    ->columns(2)
                    ->schema([
                        TextInput::make('default_currency')
                            ->required()
                            ->maxLength(3),
                        TextInput::make('max_file_size_mb')
                            ->numeric()
                            ->minValue(1)
                            ->required(),
                        TagsInput::make('allowed_file_types')
                            ->placeholder('pdf')
                            ->helperText('Example: pdf, jpg, jpeg, png'),
                        TagsInput::make('required_documents')
                            ->helperText('Document keys used by the admission flow'),
                        Textarea::make('payment_instructions')
                            ->columnSpanFull()
                            ->rows(3),
                    ]),

                Section::make('Interview & Maintenance')
                    ->columns(3)
                    ->schema([
                        Toggle::make('auto_schedule_interviews'),
                        TextInput::make('interview_duration_minutes')
                            ->numeric()
                            ->minValue(15)
                            ->required(),
                        TextInput::make('interview_buffer_minutes')
                            ->numeric()
                            ->minValue(0)
                            ->required(),
                        Toggle::make('maintenance_mode'),
                        Textarea::make('maintenance_message')
                            ->columnSpan(2)
                            ->rows(2),
                        KeyValue::make('extra_settings')
                            ->label('Extra Settings')
                            ->keyLabel('Key')
                            ->valueLabel('Value')
                            ->columnSpanFull()
                            ->addActionLabel('Add key'),
                    ]),
            ]);
    }
}
