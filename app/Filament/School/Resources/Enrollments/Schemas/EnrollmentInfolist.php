<?php

declare(strict_types=1);

namespace App\Filament\School\Resources\Enrollments\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;

class EnrollmentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Student & Enrollment')
                ->icon('heroicon-o-user')
                ->columns(3)
                ->schema([
                    TextEntry::make('student_id')
                        ->label('Student ID')
                        ->badge()
                        ->color('primary')
                        ->copyable(),

                    TextEntry::make('enrollment_number')
                        ->label('Enrollment Number')
                        ->badge()
                        ->color('gray')
                        ->copyable(),

                    TextEntry::make('status')
                        ->label('Status')
                        ->badge()
                        ->formatStateUsing(fn (string $state): string => match ($state) {
                            'enrolled'    => 'Enrolled',
                            'active'      => 'Active',
                            'completed'   => 'Completed',
                            'transferred' => 'Transferred',
                            'withdrawn'   => 'Withdrawn',
                            'expelled'    => 'Expelled',
                            'graduated'   => 'Graduated',
                            default       => ucfirst($state),
                        })
                        ->color(fn (string $state): string => match ($state) {
                            'active'    => 'success',
                            'withdrawn' => 'danger',
                            'graduated' => 'success',
                            default     => 'gray',
                        }),
                ]),

            Section::make('Student Information')
                ->icon('heroicon-o-identification')
                ->columns(3)
                ->schema([
                    TextEntry::make('application.student_first_name')
                        ->label('Full Name')
                        ->formatStateUsing(fn ($state, $record): string =>
                        trim($record->application?->student_first_name . ' ' .
                            $record->application?->student_last_name)
                        )
                        ->weight(FontWeight::Bold),

                    TextEntry::make('application.gender')
                        ->label('Gender')
                        ->formatStateUsing(fn (string $state): string => ucfirst($state)),

                    TextEntry::make('application.birth_date')
                        ->label('Birth Date')
                        ->date('d M Y')
                        ->default(fn ($record): string =>
                        $record->application?->birth_date
                            ? $record->application->birth_date->age . ' years old'
                            : '—'
                        ),

                    TextEntry::make('application.user.name')
                        ->label('Parent / Guardian')
                        ->default(fn ($record): string =>
                            $record->application?->user?->phone ?? '—'
                        ),

                    TextEntry::make('application.user.email')
                        ->label('Email')
                        ->copyable(),

                    TextEntry::make('application.application_number')
                        ->label('Application #')
                        ->badge()
                        ->color('info'),
                ]),

            Section::make('Enrollment Details')
                ->icon('heroicon-o-calendar')
                ->columns(3)
                ->schema([
                    TextEntry::make('enrollment_date')
                        ->label('Enrollment Date')
                        ->date('d M Y, l'),

                    TextEntry::make('start_date')
                        ->label('Start Date')
                        ->date('d M Y, l'),

                    TextEntry::make('days_enrolled')
                        ->label('Days Enrolled')
                        ->state(fn ($record): string => $record->days_enrolled . ' days')
                        ->icon('heroicon-o-clock'),

                    TextEntry::make('class_name')
                        ->label('Class')
                        ->placeholder('Not assigned'),

                    TextEntry::make('homeroom_teacher')
                        ->label('Homeroom Teacher')
                        ->placeholder('Not assigned'),

                    TextEntry::make('enrolledBy.name')
                        ->label('Enrolled By')
                        ->placeholder('—'),
                ]),

            Section::make('Payment Summary')
                ->icon('heroicon-o-currency-dollar')
                ->columns(4)
                ->schema([
                    TextEntry::make('total_amount_due')
                        ->label('Total Due')
                        ->formatStateUsing(fn ($state): string =>
                            'IDR ' . number_format($state, 0, ',', '.')
                        )
                        ->weight(FontWeight::Bold),

                    TextEntry::make('total_amount_paid')
                        ->label('Total Paid')
                        ->formatStateUsing(fn ($state): string =>
                            'IDR ' . number_format($state, 0, ',', '.')
                        )
                        ->color('success')
                        ->weight(FontWeight::Bold),

                    TextEntry::make('balance')
                        ->label('Balance')
                        ->formatStateUsing(fn ($state): string =>
                            'IDR ' . number_format($state, 0, ',', '.')
                        )
                        ->color(fn ($state): string => $state > 0 ? 'danger' : 'success')
                        ->weight(FontWeight::Bold),

                    TextEntry::make('payment_status')
                        ->label('Payment Status')
                        ->badge()
                        ->formatStateUsing(fn (string $state): string => match ($state) {
                            'pending' => 'Pending',
                            'partial' => 'Partial',
                            'paid'    => 'Paid',
                            default   => ucfirst($state),
                        })
                        ->color(fn (string $state): string => match ($state) {
                            'paid'    => 'success',
                            'partial' => 'warning',
                            default   => 'gray',
                        }),

                    TextEntry::make('payment_percentage')
                        ->label('Payment Progress')
                        ->state(fn ($record): string => $record->payment_percentage . '%')
                        ->badge()
                        ->color(fn ($record): string => match (true) {
                            $record->payment_percentage >= 100 => 'success',
                            $record->payment_percentage >= 50  => 'warning',
                            default                             => 'danger',
                        })
                        ->columnSpanFull(),
                ]),

            Section::make('Withdrawal Information')
                ->icon('heroicon-o-x-circle')
                ->columns(2)
                ->visible(fn ($record): bool => $record->status === 'withdrawn')
                ->schema([
                    TextEntry::make('withdrawal_date')
                        ->label('Withdrawal Date')
                        ->date('d M Y'),

                    TextEntry::make('withdrawal_reason')
                        ->label('Reason')
                        ->columnSpanFull(),
                ]),

            Section::make('Notes')
                ->icon('heroicon-o-document-text')
                ->visible(fn ($record): bool => filled($record->notes))
                ->schema([
                    TextEntry::make('notes')
                        ->label('')
                        ->columnSpanFull(),
                ]),

        ]);
    }
}
