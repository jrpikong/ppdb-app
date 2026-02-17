<?php

declare(strict_types=1);

namespace App\Filament\School\Resources\Applications\RelationManagers;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Support\Enums\FontWeight;
use Filament\Facades\Filament;

class SchedulesRelationManager extends RelationManager
{
    protected static string $relationship = 'schedules';

    protected static ?string $title = 'Schedules (Interviews/Tests)';

    protected static string|null|\BackedEnum $icon = 'heroicon-o-calendar-days';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Schedule Details')
                    ->schema([
                        Forms\Components\Select::make('type')
                            ->label('Schedule Type')
                            ->options([
                                'observation' => 'Observation',
                                'test' => 'Test/Assessment',
                                'interview' => 'Parent Interview',
                                'school_tour' => 'School Tour',
                                'other' => 'Other',
                            ])
                            ->required()
                            ->native(false)
                            ->columnSpan(1),

                        Forms\Components\DateTimePicker::make('scheduled_date')
                            ->label('Scheduled Date & Time')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y H:i')
                            ->minDate(now())
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('duration_minutes')
                            ->label('Duration (minutes)')
                            ->numeric()
                            ->minValue(15)
                            ->maxValue(480)
                            ->default(60)
                            ->suffix('minutes')
                            ->columnSpan(1),

                        Forms\Components\Select::make('interviewer_id')
                            ->label('Interviewer/Assessor')
                            ->relationship(
                                name: 'interviewer',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn ($query) => $query
                                    ->where('school_id', Filament::getTenant()->id)
                                    ->where('is_active', true)
                                    ->whereHas('roles', function ($q) {
                                        $q->whereIn('name', ['school_admin', 'admission_admin']);
                                    })
                            )
                            ->searchable()
                            ->preload()
                            ->columnSpan(1),

                        Forms\Components\Toggle::make('is_online')
                            ->label('Online Meeting')
                            ->default(false)
                            ->inline(false)
                            ->live()
                            ->columnSpan(2),

                        Forms\Components\TextInput::make('location')
                            ->label('Location/Meeting Link')
                            ->maxLength(255)
                            ->placeholder(fn (Get $get) => $get('is_online') ? 'Zoom/Google Meet link' : 'Room number or address')
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('notes')
                            ->label('Notes/Instructions')
                            ->rows(2)
                            ->columnSpanFull(),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'scheduled' => 'Scheduled',
                                'confirmed' => 'Confirmed',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                                'no_show' => 'No Show',
                                'rescheduled' => 'Rescheduled',
                            ])
                            ->default('scheduled')
                            ->required()
                            ->native(false)
                            ->live()
                            ->columnSpan(1),
                    ])
                    ->columns(2),

                Section::make('Assessment Results')
                    ->schema([
                        Forms\Components\TextInput::make('score')
                            ->label('Score')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('/ 100')
                            ->columnSpan(1),

                        Forms\Components\DateTimePicker::make('completed_at')
                            ->label('Completed At')
                            ->native(false)
                            ->displayFormat('d/m/Y H:i')
                            ->columnSpan(1),

                        Forms\Components\Textarea::make('result')
                            ->label('Assessment Result/Summary')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('feedback')
                            ->label('Detailed Feedback')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->visible(fn (Get $get) => $get('status') === 'completed')
                    ->collapsible(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('type')
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->sortable()
                    ->color(fn (string $state): string => match ($state) {
                        'observation' => 'info',
                        'test' => 'warning',
                        'interview' => 'purple',
                        'school_tour' => 'success',
                        'other' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => (string)str($state)->replace('_', ' ')->title()),

                Tables\Columns\TextColumn::make('scheduled_date')
                    ->label('Date & Time')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->color(function ($record) {
                        if ($record->status === 'completed') {
                            return 'success';
                        }
                        if ($record->scheduled_date < now() && $record->status === 'scheduled') {
                            return 'danger';
                        }
                        return 'primary';
                    }),

                Tables\Columns\TextColumn::make('duration_minutes')
                    ->label('Duration')
                    ->suffix(' min')
                    ->sortable(),

                Tables\Columns\TextColumn::make('interviewer.name')
                    ->label('Interviewer')
                    ->placeholder('Not assigned')
                    ->limit(25),

                Tables\Columns\IconColumn::make('is_online')
                    ->label('Online')
                    ->boolean()
                    ->trueIcon('heroicon-o-video-camera')
                    ->falseIcon('heroicon-o-building-office')
                    ->trueColor('info')
                    ->falseColor('gray'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->sortable()
                    ->color(fn (string $state): string => match ($state) {
                        'scheduled' => 'info',
                        'confirmed' => 'primary',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        'no_show' => 'warning',
                        'rescheduled' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => (string)str($state)->replace('_', ' ')->title()),

                Tables\Columns\TextColumn::make('score')
                    ->label('Score')
                    ->badge()
                    ->suffix(' / 100')
                    ->color(fn ($state) => match (true) {
                        $state >= 80 => 'success',
                        $state >= 60 => 'warning',
                        $state < 60 => 'danger',
                        default => 'gray',
                    })
                    ->placeholder('N/A')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'observation' => 'Observation',
                        'test' => 'Test/Assessment',
                        'interview' => 'Interview',
                        'school_tour' => 'School Tour',
                        'other' => 'Other',
                    ])
                    ->multiple(),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'scheduled' => 'Scheduled',
                        'confirmed' => 'Confirmed',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                        'no_show' => 'No Show',
                        'rescheduled' => 'Rescheduled',
                    ])
                    ->multiple(),

                Tables\Filters\SelectFilter::make('interviewer_id')
                    ->label('Interviewer')
                    ->relationship(
                        name: 'interviewer',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn ($query) => $query
                            ->where('school_id', Filament::getTenant()->id)
                    )
                    ->searchable()
                    ->preload(),

                Tables\Filters\TernaryFilter::make('is_online')
                    ->label('Online Meeting'),

                Tables\Filters\Filter::make('scheduled_date')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('From Date')
                            ->native(false),
                        Forms\Components\DatePicker::make('until')
                            ->label('Until Date')
                            ->native(false),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn ($q, $date) => $q->whereDate('scheduled_date', '>=', $date))
                            ->when($data['until'], fn ($q, $date) => $q->whereDate('scheduled_date', '<=', $date));
                    }),
            ])
            ->headerActions([
                CreateAction::make()
                    ->icon('heroicon-o-plus')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['created_by'] = auth()->id();
                        return $data;
                    }),
            ])
            ->recordActions([
                Action::make('complete')
                    ->label('Mark Complete')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->schema([
                        Forms\Components\TextInput::make('score')
                            ->label('Score')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('/ 100'),

                        Forms\Components\Textarea::make('result')
                            ->label('Assessment Result')
                            ->rows(3),

                        Forms\Components\Textarea::make('feedback')
                            ->label('Feedback')
                            ->rows(4),
                    ])
                    ->action(function ($record, array $data): void {
                        $record->update([
                            'status' => 'completed',
                            'completed_at' => now(),
                            'completed_by' => auth()->id(),
                            'score' => $data['score'],
                            'result' => $data['result'],
                            'feedback' => $data['feedback'],
                        ]);
                    })
                    ->successNotificationTitle('Schedule completed successfully')
                    ->visible(fn ($record) => in_array($record->status, ['scheduled', 'confirmed'])),

                Action::make('cancel')
                    ->label('Cancel')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function ($record): void {
                        $record->update(['status' => 'cancelled']);
                    })
                    ->successNotificationTitle('Schedule cancelled')
                    ->visible(fn ($record) => in_array($record->status, ['scheduled', 'confirmed'])),

                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('scheduled_date', 'asc')
            ->emptyStateHeading('No schedules yet')
            ->emptyStateDescription('Create interview or test schedules for this application.')
            ->emptyStateIcon('heroicon-o-calendar-days');
    }
}
