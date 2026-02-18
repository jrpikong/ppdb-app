<?php

declare(strict_types=1);

namespace App\Filament\School\Resources\Levels\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class LevelsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                // Code badge
                TextColumn::make('code')
                    ->label('Code')
                    ->badge()
                    ->color('primary')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                // Level name + description
                TextColumn::make('name')
                    ->label('Level Name')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->description(fn ($record): ?string =>
                    $record->description
                        ? \Illuminate\Support\Str::limit(strip_tags($record->description), 50)
                        : null
                    ),

                // Program category badge
                TextColumn::make('program_category')
                    ->label('Program')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'early_years'   => 'Early Years',
                        'primary_years' => 'Primary',
                        'middle_years'  => 'Middle Years',
                        default         => ucwords(str_replace('_', ' ', $state)),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'early_years'   => 'info',
                        'primary_years' => 'success',
                        'middle_years'  => 'warning',
                        default         => 'gray',
                    })
                    ->sortable(),

                // Age range
                TextColumn::make('age_min')
                    ->label('Age Range')
                    ->formatStateUsing(fn ($state, $record): string =>
                        number_format((int)$record->age_min, 1) . ' - ' .
                        number_format((int)$record->age_max, 1) . ' years'
                    )
                    ->icon('heroicon-o-calendar')
                    ->iconColor('gray'),

                // Capacity progress
                TextColumn::make('quota')
                    ->label('Capacity')
                    ->formatStateUsing(fn ($state, $record): string =>
                        $record->current_enrollment . ' / ' . $record->quota
                    )
                    ->description(fn ($record): string =>
                    $record->quota > 0
                        ? round(($record->current_enrollment / $record->quota) * 100) . '% full'
                        : 'No quota set'
                    )
                    ->color(fn ($record): string => match (true) {
                        $record->quota === 0                                    => 'gray',
                        ($record->current_enrollment / $record->quota) >= 0.9   => 'danger',
                        ($record->current_enrollment / $record->quota) >= 0.7   => 'warning',
                        default                                                  => 'success',
                    })
                    ->sortable(),

                // Tuition fee
                TextColumn::make('annual_tuition_fee')
                    ->label('Annual Fee')
                    ->formatStateUsing(fn ($state): string =>
                        'IDR ' . number_format($state, 0, ',', '.')
                    )
                    ->toggleable(isToggledHiddenByDefault: true),

                // Sort order
                TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                // Active status
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->sortable(),

                // Accepting applications
                ToggleColumn::make('is_accepting_applications')
                    ->label('Accepting Apps')
                    ->afterStateUpdated(fn ($record, bool $state) =>
                    Notification::make()
                        ->title($state ? 'Now Accepting Applications' : 'Closed for Applications')
                        ->{$state ? 'success' : 'warning'}()
                        ->send()
                    ),

            ])

            ->defaultSort('sort_order')

            // ✅ Drag & drop reordering
            ->reorderable('sort_order')
            ->reorderRecordsTriggerAction(
                fn ($action) => $action
                    ->button()
                    ->label('Reorder Levels')
                    ->icon('heroicon-o-arrows-up-down')
                    ->color('gray')
            )

            ->filters([

                SelectFilter::make('program_category')
                    ->label('Program Category')
                    ->options([
                        'early_years'   => 'Early Years',
                        'primary_years' => 'Primary Years',
                        'middle_years'  => 'Middle Years',
                    ])
                    ->native(false),

                TernaryFilter::make('is_active')
                    ->label('Active Status')
                    ->native(false),

                TernaryFilter::make('is_accepting_applications')
                    ->label('Accepting Applications')
                    ->native(false),

            ])

            ->recordActions([
                EditAction::make()->label(''),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])

            ->emptyStateIcon('heroicon-o-academic-cap')
            ->emptyStateHeading('No Education Levels Yet')
            ->emptyStateDescription('Create education levels for your school to start accepting applications.')
            ->striped();
    }
}
