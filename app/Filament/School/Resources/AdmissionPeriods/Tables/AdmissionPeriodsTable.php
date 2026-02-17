<?php

declare(strict_types=1);

namespace App\Filament\School\Resources\AdmissionPeriods\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class AdmissionPeriodsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                // Period name + academic year stacked
                TextColumn::make('name')
                    ->label('Period')
                    ->description(fn ($record): string =>
                        $record->academicYear?->name ?? '—'
                    )
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                // Date range
                TextColumn::make('start_date')
                    ->label('Opens')
                    ->date('d M Y')
                    ->sortable()
                    ->icon('heroicon-o-calendar'),

                TextColumn::make('end_date')
                    ->label('Closes')
                    ->date('d M Y')
                    ->sortable()
                    ->icon('heroicon-o-calendar')
                    ->color(fn ($record): string =>
                    $record->end_date < now() ? 'danger' : 'gray'
                    ),

                // Decision date
                TextColumn::make('decision_date')
                    ->label('Decision')
                    ->date('d M Y')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                // Total applications count
                TextColumn::make('applications_count')
                    ->label('Applications')
                    ->counts('applications')
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                // Active toggle (inline)
                ToggleColumn::make('is_active')
                    ->label('Active')
                    ->afterStateUpdated(function ($record, bool $state): void {
                        $state
                            ? Notification::make()
                            ->success()
                            ->title('Period Activated')
                            ->body("'{$record->name}' is now the active admission period.")
                            ->send()
                            : Notification::make()
                            ->warning()
                            ->title('Period Deactivated')
                            ->body("'{$record->name}' has been deactivated.")
                            ->send();
                    })
                    ->sortable(),

                // Allow applications
                IconColumn::make('allow_applications')
                    ->label('Accepting')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                // Rolling
                IconColumn::make('is_rolling')
                    ->label('Rolling')
                    ->boolean()
                    ->trueIcon('heroicon-o-arrow-path')
                    ->falseIcon('heroicon-o-minus-circle')
                    ->trueColor('info')
                    ->falseColor('gray')
                    ->toggleable(isToggledHiddenByDefault: true),

            ])

            ->defaultSort('start_date', 'desc')

            ->filters([

                // ✅ REMOVED: school_id filter (sudah di-scope via getEloquentQuery)

                TernaryFilter::make('is_active')
                    ->label('Active Status')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only')
                    ->native(false),

                TernaryFilter::make('allow_applications')
                    ->label('Accepting Applications')
                    ->trueLabel('Accepting')
                    ->falseLabel('Closed')
                    ->native(false),

                TernaryFilter::make('is_rolling')
                    ->label('Rolling Admission')
                    ->native(false),

                TrashedFilter::make(),

            ])

            ->recordActions([
                ViewAction::make()->label(''),
                EditAction::make()->label(''),

                // Quick toggle: open/close applications
                \Filament\Actions\Action::make('toggleApplications')
                    ->label(fn ($record): string =>
                    $record->allow_applications ? 'Close Applications' : 'Open Applications'
                    )
                    ->icon(fn ($record): string =>
                    $record->allow_applications
                        ? 'heroicon-o-lock-closed'
                        : 'heroicon-o-lock-open'
                    )
                    ->color(fn ($record): string =>
                    $record->allow_applications ? 'danger' : 'success'
                    )
                    ->requiresConfirmation()
                    ->action(function ($record): void {
                        $record->update(['allow_applications' => !$record->allow_applications]);

                        $record->allow_applications
                            ? Notification::make()->success()->title('Applications Opened')->send()
                            : Notification::make()->warning()->title('Applications Closed')->send();
                    }),

                DeleteAction::make()->label(''),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])

            ->emptyStateIcon('heroicon-o-calendar-days')
            ->emptyStateHeading('No Admission Periods Yet')
            ->emptyStateDescription('Create your first admission period to start accepting applications.')
            ->emptyStateActions([
                \Filament\Actions\CreateAction::make()
                    ->label('Create Admission Period')
                    ->icon('heroicon-o-plus'),
            ])

            ->striped()
            ->paginated([10, 25, 50]);
    }
}
