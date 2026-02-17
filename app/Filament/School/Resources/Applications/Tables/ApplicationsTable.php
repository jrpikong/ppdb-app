<?php

namespace App\Filament\School\Resources\Applications\Tables;

use App\Models\Application;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ApplicationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('application_number')
                ->label('App #')
                ->searchable()
                ->sortable()
                ->weight(FontWeight::Bold)
                ->copyable()
                ->copyMessage('Application number copied!')
                ->tooltip('Click to copy'),

                TextColumn::make('student_full_name')
                    ->label('Student Name')
                    ->searchable(['student_first_name', 'student_last_name'])
                    ->sortable()
                    ->limit(30)
                    ->tooltip(function (Application $record): string {
                        return $record->student_full_name;
                    }),

                TextColumn::make('user.name')
                    ->label('Parent')
                    ->searchable()
                    ->toggleable()
                    ->limit(25),

                TextColumn::make('admissionPeriod.name')
                    ->label('Period')
                    ->sortable()
                    ->searchable()
                    ->toggleable()
                    ->limit(25),

                TextColumn::make('level.name')
                    ->label('Level/Grade')
                    ->sortable()
                    ->badge()
                    ->color('info'),

                TextColumn::make('status')
                    ->badge()
                    ->sortable()
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
                    })
                    ->formatStateUsing(fn (string $state): string => str($state)->replace('_', ' ')->title()),

                TextColumn::make('assignedTo.name')
                    ->label('Assigned To')
                    ->toggleable()
                    ->limit(25)
                    ->default('Unassigned')
                    ->icon(fn ($state) => $state ? 'heroicon-o-user' : 'heroicon-o-user-minus')
                    ->color(fn ($state) => $state ? 'success' : 'gray'),

                TextColumn::make('priority_score')
                    ->label('Score')
                    ->sortable()
                    ->toggleable()
                    ->suffix(' / 100')
                    ->color(fn ($state) => match (true) {
                        $state >= 80 => 'success',
                        $state >= 60 => 'warning',
                        $state < 60 => 'danger',
                        default => 'gray',
                    })
                    ->default('N/A'),

                TextColumn::make('submitted_at')
                    ->label('Submitted')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable()
                    ->placeholder('Not submitted'),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Status Filter
                SelectFilter::make('status')
                    ->multiple()
                    ->options([
                        'draft' => 'Draft',
                        'submitted' => 'Submitted',
                        'under_review' => 'Under Review',
                        'documents_verified' => 'Documents Verified',
                        'interview_scheduled' => 'Interview Scheduled',
                        'interview_completed' => 'Interview Completed',
                        'payment_pending' => 'Payment Pending',
                        'payment_verified' => 'Payment Verified',
                        'accepted' => 'Accepted',
                        'rejected' => 'Rejected',
                        'waitlisted' => 'Waitlisted',
                        'enrolled' => 'Enrolled',
                        'withdrawn' => 'Withdrawn',
                    ])
                    ->label('Status'),

                // Admission Period Filter
                SelectFilter::make('admission_period_id')
                    ->relationship(
                        name: 'admissionPeriod',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query) => $query
                            ->where('school_id', Filament::getTenant()->id)
                    )
                    ->searchable()
                    ->preload()
                    ->label('Admission Period'),

                // Level Filter
                SelectFilter::make('level_id')
                    ->relationship(
                        name: 'level',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query) => $query
                            ->where('school_id', Filament::getTenant()->id)
                            // ->orderBy('sequence')
                    )
                    ->searchable()
                    ->preload()
                    ->label('Level/Grade'),

                // Assigned Reviewer Filter
                SelectFilter::make('assigned_to')
                    ->relationship(
                        name: 'assignedTo',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query) => $query
                            ->where('school_id', Filament::getTenant()->id)
                    )
                    ->searchable()
                    ->preload()
                    ->label('Assigned To'),

                // Date Range Filter
                Filter::make('submitted_at')
                    ->schema([
                        DatePicker::make('submitted_from')
                            ->label('Submitted From')
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                        DatePicker::make('submitted_until')
                            ->label('Submitted Until')
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['submitted_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('submitted_at', '>=', $date),
                            )
                            ->when(
                                $data['submitted_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('submitted_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['submitted_from'] ?? null) {
                            $indicators['submitted_from'] = 'Submitted from ' . date('d M Y', strtotime($data['submitted_from']));
                        }

                        if ($data['submitted_until'] ?? null) {
                            $indicators['submitted_until'] = 'Submitted until ' . date('d M Y', strtotime($data['submitted_until']));
                        }

                        return $indicators;
                    }),

                // Priority Score Filter
                Filter::make('priority_score')
                    ->schema([
                        Select::make('score_range')
                            ->label('Score Range')
                            ->options([
                                'high' => 'High (80-100)',
                                'medium' => 'Medium (60-79)',
                                'low' => 'Low (0-59)',
                            ])
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['score_range'] ?? null,
                            fn (Builder $query, $range): Builder => match ($range) {
                                'high' => $query->whereBetween('priority_score', [80, 100]),
                                'medium' => $query->whereBetween('priority_score', [60, 79]),
                                'low' => $query->whereBetween('priority_score', [0, 59]),
                                default => $query,
                            }
                        );
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (!$data['score_range']) {
                            return null;
                        }

                        return match ($data['score_range']) {
                            'high' => 'Score: High (80-100)',
                            'medium' => 'Score: Medium (60-79)',
                            'low' => 'Score: Low (0-59)',
                            default => null,
                        };
                    }),

                // Trashed Filter
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('assign')
                    ->label('Assign')
                    ->icon('heroicon-o-user-plus')
                    ->color('info')
                    ->schema([
                        Select::make('assigned_to')
                            ->label('Assign to Reviewer')
                            ->options(function () {
                                return User::where('school_id', Filament::getTenant()->id)
                                    ->where('is_active', true)
                                    ->whereHas('roles', function ($query) {
                                        $query->whereIn('name', ['school_admin', 'admission_admin']);
                                    })
                                    ->pluck('name', 'id');
                            })
                            ->required()
                            ->searchable(),
                    ])
                    ->action(function (Application $record, array $data): void {
                        $record->update([
                            'assigned_to' => $data['assigned_to'],
                        ]);
                    })
                    ->successNotificationTitle('Reviewer assigned successfully')
                    ->visible(fn (Application $record) => !$record->assigned_to),

                Action::make('changeStatus')
                    ->label('Update Status')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->schema([
                        Select::make('status')
                            ->label('New Status')
                            ->options([
                                'draft' => 'Draft',
                                'submitted' => 'Submitted',
                                'under_review' => 'Under Review',
                                'documents_verified' => 'Documents Verified',
                                'interview_scheduled' => 'Interview Scheduled',
                                'interview_completed' => 'Interview Completed',
                                'payment_pending' => 'Payment Pending',
                                'payment_verified' => 'Payment Verified',
                                'accepted' => 'Accepted',
                                'rejected' => 'Rejected',
                                'waitlisted' => 'Waitlisted',
                                'enrolled' => 'Enrolled',
                                'withdrawn' => 'Withdrawn',
                            ])
                            ->required()
                            ->native(false),
                        Textarea::make('status_notes')
                            ->label('Status Notes')
                            ->rows(3),
                    ])
                    ->action(function (Application $record, array $data): void {
                        $record->update([
                            'status' => $data['status'],
                            'status_notes' => $data['status_notes'],
                        ]);
                    })
                    ->successNotificationTitle('Status updated successfully'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('assignBulk')
                        ->label('Assign Reviewer')
                        ->icon('heroicon-o-user-plus')
                        ->color('info')
                        ->schema([
                            Select::make('assigned_to')
                                ->label('Assign to Reviewer')
                                ->options(function () {
                                    return User::where('school_id', Filament::getTenant()->id)
                                        ->where('is_active', true)
                                        ->whereHas('roles', function ($query) {
                                            $query->whereIn('name', ['school_admin', 'admission_admin']);
                                        })
                                        ->pluck('name', 'id');
                                })
                                ->required()
                                ->searchable(),
                        ])
                        ->action(function ($records, array $data): void {
                            $records->each->update([
                                'assigned_to' => $data['assigned_to'],
                            ]);
                        })
                        ->deselectRecordsAfterCompletion()
                        ->successNotificationTitle('Applications assigned successfully'),

                    BulkAction::make('updateStatusBulk')
                        ->label('Update Status')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->schema([
                            Select::make('status')
                                ->label('New Status')
                                ->options([
                                    'under_review' => 'Under Review',
                                    'documents_verified' => 'Documents Verified',
                                    'interview_scheduled' => 'Interview Scheduled',
                                    'interview_completed' => 'Interview Completed',
                                    'payment_pending' => 'Payment Pending',
                                    'accepted' => 'Accepted',
                                    'rejected' => 'Rejected',
                                    'waitlisted' => 'Waitlisted',
                                ])
                                ->required()
                                ->native(false),
                        ])
                        ->action(function ($records, array $data): void {
                            $records->each->update([
                                'status' => $data['status'],
                            ]);
                        })
                        ->deselectRecordsAfterCompletion()
                        ->successNotificationTitle('Status updated successfully'),

                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->persistSortInSession()
            ->persistSearchInSession()
            ->persistFiltersInSession()
            ->striped()
            ->emptyStateHeading('No applications found')
            ->emptyStateDescription('Applications will appear here once parents submit their registration.')
            ->emptyStateIcon('heroicon-o-document-text');
    }
}
