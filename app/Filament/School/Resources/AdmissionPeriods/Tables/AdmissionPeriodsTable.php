<?php

namespace App\Filament\School\Resources\AdmissionPeriods\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
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
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class AdmissionPeriodsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('academicYear.name')->label('Academic Year')->sortable(),
                TextColumn::make('name')->label('Period')->searchable()->sortable(),
                TextColumn::make('start_date')->label('Start')->date()->sortable(),
                TextColumn::make('end_date')->label('End')->date()->sortable(),
                ToggleColumn::make('is_active')->label('Active')
                    ->afterStateUpdated(function ($record, $state) {
                        ($state) ? Notification::make()->success()->title('Admission Is Active')->send() : Notification::make()->danger()->title('Admission Is Inactive')->send() ;
                    })
                    ->sortable(),
                IconColumn::make('allow_applications')->label('Allow Application')->boolean(),
                IconColumn::make('is_rolling')->label('Rolling')->boolean(),
                TextColumn::make('totalApplications')->label('Total Application')->counts('applications')->sortable(),
            ])
            ->filters([
                SelectFilter::make('school_id')->relationship('school', 'name')->label('Sekolah'),
                TernaryFilter::make('is_active')->label('Status'),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('start_date', 'desc')
            ->striped();
    }
}
