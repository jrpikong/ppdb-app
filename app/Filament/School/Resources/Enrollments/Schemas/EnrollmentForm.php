<?php

namespace App\Filament\School\Resources\Enrollments\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class EnrollmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('application_id')
                    ->relationship('application', 'id')
                    ->required(),
                TextInput::make('student_id')
                    ->required(),
                TextInput::make('enrollment_number')
                    ->required(),
                DatePicker::make('enrollment_date')
                    ->required(),
                DatePicker::make('start_date')
                    ->required(),
                TextInput::make('class_name'),
                TextInput::make('homeroom_teacher'),
                TextInput::make('total_amount_due')
                    ->required()
                    ->numeric(),
                TextInput::make('total_amount_paid')
                    ->required()
                    ->numeric(),
                TextInput::make('balance')
                    ->required()
                    ->numeric(),
                TextInput::make('payment_status')
                    ->required()
                    ->default('pending'),
                Select::make('status')
                    ->options([
            'enrolled' => 'Enrolled',
            'active' => 'Active',
            'completed' => 'Completed',
            'transferred' => 'Transferred',
            'withdrawn' => 'Withdrawn',
            'expelled' => 'Expelled',
            'graduated' => 'Graduated',
        ])
                    ->default('enrolled')
                    ->required(),
                DatePicker::make('withdrawal_date'),
                Textarea::make('withdrawal_reason')
                    ->columnSpanFull(),
                Textarea::make('notes')
                    ->columnSpanFull(),
                TextInput::make('enrolled_by')
                    ->numeric(),
            ]);
    }
}
