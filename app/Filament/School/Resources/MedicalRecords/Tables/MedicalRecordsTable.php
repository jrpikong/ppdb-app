<?php

declare(strict_types=1);

namespace App\Filament\School\Resources\MedicalRecords\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class MedicalRecordsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                // Student name from application
                TextColumn::make('application.student_first_name')
                    ->label('Student Name')
                    ->formatStateUsing(fn ($state, $record): string =>
                    trim($record->application?->student_first_name . ' ' .
                        $record->application?->student_last_name)
                    )
                    ->description(fn ($record): string =>
                        $record->application?->application_number ?? '—'
                    )
                    ->searchable(['application.student_first_name', 'application.student_last_name'])
                    ->sortable()
                    ->weight('semibold'),

                // Blood type
                TextColumn::make('blood_type')
                    ->label('Blood Type')
                    ->badge()
                    ->color('danger')
                    ->formatStateUsing(fn (?string $state): string =>
                        $state ?? 'Unknown'
                    ),

                // Physical stats
                TextColumn::make('height')
                    ->label('Height / Weight')
                    ->formatStateUsing(fn ($state, $record): string =>
                        ($record->height ? $record->height . ' cm' : '—') .
                        ' / ' .
                        ($record->weight ? $record->weight . ' kg' : '—')
                    )
                    ->description(fn ($record): ?string =>
                    $record->bmi
                        ? 'BMI: ' . $record->bmi . ' (' . $record->bmi_category . ')'
                        : null
                    ),

                // Health indicators (icons)
                IconColumn::make('has_food_allergies')
                    ->label('🍎 Allergies')
                    ->boolean()
                    ->trueColor('warning')
                    ->falseColor('gray')
                    ->tooltip(fn ($record): string =>
                    $record->has_food_allergies
                        ? 'Has food allergies: ' . ($record->food_allergies_details ?? 'See details')
                        : 'No food allergies'
                    ),

                IconColumn::make('has_medical_conditions')
                    ->label('💊 Medical')
                    ->boolean()
                    ->trueColor('danger')
                    ->falseColor('gray')
                    ->tooltip(fn ($record): string =>
                    $record->has_medical_conditions
                        ? 'Has medical conditions'
                        : 'No medical conditions'
                    ),

                IconColumn::make('has_special_needs')
                    ->label('🌟 Special')
                    ->boolean()
                    ->trueColor('info')
                    ->falseColor('gray')
                    ->tooltip(fn ($record): string =>
                    $record->has_special_needs
                        ? 'Has special needs'
                        : 'No special needs'
                    ),

                IconColumn::make('requires_learning_support')
                    ->label('📚 Learning')
                    ->boolean()
                    ->trueColor('warning')
                    ->falseColor('gray')
                    ->tooltip(fn ($record): string =>
                    $record->requires_learning_support
                        ? 'Requires learning support'
                        : 'No learning support needed'
                    ),

                IconColumn::make('immunizations_up_to_date')
                    ->label('💉 Immunized')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),

                // Emergency contact
                TextColumn::make('emergency_contact_name')
                    ->label('Emergency Contact')
                    ->description(fn ($record): string =>
                        ($record->emergency_contact_relationship ?? '—') .
                        ' • ' .
                        ($record->emergency_contact_phone ?? '—')
                    )
                    ->toggleable(),

                // Completion percentage
                TextColumn::make('completion_percentage')
                    ->label('Completion')
                    ->state(fn ($record): string =>
                        $record->getCompletionPercentage() . '%'
                    )
                    ->badge()
                    ->color(fn ($record): string => match (true) {
                        $record->getCompletionPercentage() >= 80 => 'success',
                        $record->getCompletionPercentage() >= 50 => 'warning',
                        default                                   => 'danger',
                    })
                    ->sortable(query: function ($query, string $direction) {
                        // This is just visual, actual sorting would need a computed column
                        return $query;
                    }),

            ])

            ->defaultSort('created_at', 'desc')

            ->filters([

                TernaryFilter::make('has_food_allergies')
                    ->label('Has Food Allergies')
                    ->native(false),

                TernaryFilter::make('has_medical_conditions')
                    ->label('Has Medical Conditions')
                    ->native(false),

                TernaryFilter::make('has_special_needs')
                    ->label('Has Special Needs')
                    ->native(false),

                TernaryFilter::make('requires_learning_support')
                    ->label('Requires Learning Support')
                    ->native(false),

                TernaryFilter::make('immunizations_up_to_date')
                    ->label('Immunizations Up-to-Date')
                    ->native(false),

            ])

            ->recordActions([
                ViewAction::make()->label(''),
                EditAction::make()->label(''),
                DeleteAction::make()->label(''),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])

            ->emptyStateIcon('heroicon-o-heart')
            ->emptyStateHeading('No Medical Records Yet')
            ->emptyStateDescription('Medical records for enrolled students will appear here.')
            ->striped()
            ->paginated([15, 25, 50]);
    }
}
