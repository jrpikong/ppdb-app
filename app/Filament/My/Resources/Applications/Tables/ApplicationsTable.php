<?php

declare(strict_types=1);

namespace App\Filament\My\Resources\Applications\Tables;

use App\Models\Application;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ApplicationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('application_number')
                    ->label('Application #')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                TextColumn::make('school.name')
                    ->label('School')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('level.name')
                    ->label('Level')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => (string)str($state)->replace('_', ' ')->title())
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'submitted' => 'info',
                        'under_review', 'documents_verified' => 'warning',
                        'interview_scheduled', 'interview_completed' => 'purple',
                        'payment_pending', 'payment_verified' => 'indigo',
                        'accepted', 'enrolled' => 'success',
                        'rejected' => 'danger',
                        'waitlisted' => 'orange',
                        'withdrawn' => 'gray',
                        default => 'gray',
                    }),

                TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(Application::statusOptions())
                    ->native(false),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()
                    ->visible(fn (Application $record): bool => $record->status === 'draft'),
                DeleteAction::make()
                    ->visible(fn (Application $record): bool => $record->status === 'draft'),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No applications yet')
            ->emptyStateDescription('Create your first application to get started.');
    }
}
