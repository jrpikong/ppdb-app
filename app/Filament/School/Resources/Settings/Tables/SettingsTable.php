<?php

namespace App\Filament\School\Resources\Settings\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SettingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('app_name')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),
                TextColumn::make('app_version')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('default_currency')
                    ->badge()
                    ->color('info'),
                IconColumn::make('online_admission_enabled')
                    ->label('Online Admission')
                    ->boolean(),
                IconColumn::make('maintenance_mode')
                    ->label('Maintenance')
                    ->boolean(),
                TextColumn::make('updated_at')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
