<?php

declare(strict_types=1);

namespace App\Filament\School\Resources\Applications\RelationManagers;

use App\Support\ParentNotifier;
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
use Illuminate\Database\Eloquent\Builder;

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
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        Forms\Components\Select::make('type')
                            ->label('Schedule Type')
                            ->options([
                                'observation' => 'Observation',
                                'test' => 'Test/Assessment',
                                'interview' => 'Parent Interview',
                            ])
                            ->required()
                            ->native(false),

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
                            ->live(),

                        Forms\Components\DatePicker::make('scheduled_date')
                            ->label('Scheduled Date')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->minDate(today()),

                        Forms\Components\TimePicker::make('scheduled_time')
                            ->label('Scheduled Time')
                            ->required()
                            ->seconds(false)
                            ->native(false)
                            ->displayFormat('H:i'),

                        Forms\Components\TextInput::make('duration_minutes')
                            ->label('Duration (minutes)')
                            ->numeric()
                            ->minValue(15)
                            ->maxValue(480)
                            ->default(60)
                            ->suffix('minutes'),

                        Forms\Components\Select::make('interviewer_id')
                            ->label('Interviewer/Assessor')
                            ->relationship(
                                name: 'interviewer',
                                titleAttribute: 'name',
                                modifyQueryUsing: function (Builder $query): Builder {
                                    $tenantId = Filament::getTenant()?->id;

                                    if (! $tenantId) {
                                        return $query->whereRaw('1 = 0');
                                    }

                                    return $query
                                        ->where('school_id', $tenantId)
                                        ->where('is_active', true)
                                        ->whereHas('roles', function ($q): void {
                                            $q->whereIn('name', ['school_admin', 'admission_admin']);
                                        });
                                }
                            )
                            ->searchable()
                            ->preload(),

                        Forms\Components\Toggle::make('is_online')
                            ->label('Online Meeting')
                            ->default(false)
                            ->inline(false)
                            ->live(),

                        Forms\Components\TextInput::make('location')
                            ->label('Location')
                            ->maxLength(255)
                            ->placeholder(fn (Get $get): string => $get('is_online') ? 'Online meeting platform' : 'Room or location')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('online_meeting_link')
                            ->label('Meeting Link')
                            ->maxLength(255)
                            ->url()
                            ->visible(fn (Get $get): bool => (bool) $get('is_online'))
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('notes')
                            ->label('Notes/Instructions')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),

                Section::make('Assessment Results')
                    ->columns(2)
                    ->collapsible()
                    ->visible(fn (Get $get): bool => $get('status') === 'completed')
                    ->schema([
                        Forms\Components\TextInput::make('score')
                            ->label('Score')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('/ 100'),

                        Forms\Components\Select::make('recommendation')
                            ->label('Recommendation')
                            ->options([
                                'recommended' => 'Recommended',
                                'not_recommended' => 'Not Recommended',
                                'pending' => 'Pending',
                            ])
                            ->native(false),

                        Forms\Components\DateTimePicker::make('completed_at')
                            ->label('Completed At')
                            ->native(false)
                            ->displayFormat('d/m/Y H:i'),

                        Forms\Components\Textarea::make('result')
                            ->label('Assessment Result/Summary')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
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
                        'interview' => 'primary',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => (string) str($state)->replace('_', ' ')->title()),

                Tables\Columns\TextColumn::make('scheduled_date')
                    ->label('Date & Time')
                    ->date('d M Y')
                    ->description(fn ($record): string => substr((string) $record->scheduled_time, 0, 5))
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->color(function ($record): string {
                        if ($record->status === 'completed') {
                            return 'success';
                        }
                        if ($record->scheduled_date->isPast() && $record->status === 'scheduled') {
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
                    ->formatStateUsing(fn (string $state): string => (string) str($state)->replace('_', ' ')->title()),

                Tables\Columns\TextColumn::make('score')
                    ->label('Score')
                    ->badge()
                    ->suffix(' / 100')
                    ->color(fn ($state): string => match (true) {
                        $state === null => 'gray',
                        $state >= 80 => 'success',
                        $state >= 60 => 'warning',
                        default => 'danger',
                    })
                    ->placeholder('N/A')
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'observation' => 'Observation',
                        'test' => 'Test/Assessment',
                        'interview' => 'Interview',
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
                        modifyQueryUsing: function (Builder $query): Builder {
                            $tenantId = Filament::getTenant()?->id;

                            return $tenantId
                                ? $query->where('school_id', $tenantId)
                                : $query->whereRaw('1 = 0');
                        }
                    )
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('scheduled_date')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('From Date')->native(false),
                        Forms\Components\DatePicker::make('until')->label('Until Date')->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $q, $date): Builder => $q->whereDate('scheduled_date', '>=', $date))
                            ->when($data['until'] ?? null, fn (Builder $q, $date): Builder => $q->whereDate('scheduled_date', '<=', $date));
                    }),
            ])
            ->headerActions([
                CreateAction::make()
                    ->icon('heroicon-o-plus')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['created_by'] = auth()->id();

                        return $data;
                    })
                    ->after(function ($record): void {
                        ParentNotifier::scheduleUpdated($record->refresh(), 'created');
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
                        Forms\Components\Select::make('recommendation')
                            ->label('Recommendation')
                            ->options([
                                'recommended' => 'Recommended',
                                'not_recommended' => 'Not Recommended',
                                'pending' => 'Pending',
                            ])
                            ->default('pending')
                            ->native(false),
                        Forms\Components\Textarea::make('result')
                            ->label('Assessment Result')
                            ->rows(3),
                    ])
                    ->action(function ($record, array $data): void {
                        $record->update([
                            'status' => 'completed',
                            'completed_at' => now(),
                            'completed_by' => auth()->id(),
                            'score' => $data['score'] ?? null,
                            'recommendation' => $data['recommendation'] ?? 'pending',
                            'result' => $data['result'] ?? null,
                        ]);

                        ParentNotifier::scheduleUpdated($record->refresh(), 'completed');
                    })
                    ->successNotificationTitle('Schedule completed successfully')
                    ->visible(fn ($record): bool => in_array($record->status, ['scheduled', 'confirmed'], true)),

                Action::make('cancel')
                    ->label('Cancel')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function ($record): void {
                        $record->update(['status' => 'cancelled']);

                        ParentNotifier::scheduleUpdated($record->refresh(), 'cancelled');
                    })
                    ->successNotificationTitle('Schedule cancelled')
                    ->visible(fn ($record): bool => in_array($record->status, ['scheduled', 'confirmed'], true)),

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
