<?php

namespace App\Filament\School\Resources\Enrollments\Schemas;

use App\Models\Enrollment;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class EnrollmentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('application.id')
                    ->label('Application'),
                TextEntry::make('student_id'),
                TextEntry::make('enrollment_number'),
                TextEntry::make('enrollment_date')
                    ->date(),
                TextEntry::make('start_date')
                    ->date(),
                TextEntry::make('class_name')
                    ->placeholder('-'),
                TextEntry::make('homeroom_teacher')
                    ->placeholder('-'),
                TextEntry::make('total_amount_due')
                    ->numeric(),
                TextEntry::make('total_amount_paid')
                    ->numeric(),
                TextEntry::make('balance')
                    ->numeric(),
                TextEntry::make('payment_status'),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('withdrawal_date')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('withdrawal_reason')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('notes')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('enrolled_by')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (Enrollment $record): bool => $record->trashed()),
            ]);
    }
}
