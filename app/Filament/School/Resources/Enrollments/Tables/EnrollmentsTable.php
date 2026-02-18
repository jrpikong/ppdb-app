<?php

declare(strict_types=1);

namespace App\Filament\School\Resources\Enrollments\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class EnrollmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                // Student ID badge
                TextColumn::make('student_id')
                    ->label('Student ID')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->badge()
                    ->color('primary')
                    ->weight('semibold'),

                // Student name from application
                TextColumn::make('application.student_first_name')
                    ->label('Student Name')
                    ->formatStateUsing(fn ($state, $record): string =>
                    trim($record->application?->student_first_name . ' ' .
                        $record->application?->student_last_name)
                    )
                    ->description(fn ($record): string =>
                        $record->enrollment_number ?? '—'
                    )
                    ->searchable(['application.student_first_name', 'application.student_last_name'])
                    ->sortable(),

                // Class assignment
                TextColumn::make('class_name')
                    ->label('Class')
                    ->placeholder('Not assigned')
                    ->icon('heroicon-o-academic-cap')
                    ->iconColor('gray')
                    ->description(fn ($record): ?string => $record->homeroom_teacher),

                // Enrollment date
                TextColumn::make('enrollment_date')
                    ->label('Enrolled On')
                    ->date('d M Y')
                    ->sortable(),

                // Status badge
                TextColumn::make('status')
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
                        'enrolled'    => 'info',
                        'active', 'graduated' => 'success',
                        'completed'   => 'primary',
                        'transferred' => 'warning',
                        'withdrawn', 'expelled' => 'danger',
                        default       => 'gray',
                    })
                    ->sortable(),

                // Payment progress
                TextColumn::make('payment_status')
                    ->label('Payment')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Pending',
                        'partial' => 'Partial',
                        'paid'    => 'Paid',
                        default   => ucfirst($state),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'partial' => 'warning',
                        'paid'    => 'success',
                        default   => 'gray',
                    })
                    ->description(fn ($record): string =>
                        'IDR ' . number_format($record->total_amount_paid, 0, ',', '.') .
                        ' / ' . number_format($record->total_amount_due, 0, ',', '.')
                    ),

                // Balance
                TextColumn::make('balance')
                    ->label('Balance')
                    ->formatStateUsing(fn ($state): string =>
                        'IDR ' . number_format($state, 0, ',', '.')
                    )
                    ->color(fn ($state): string => $state > 0 ? 'danger' : 'success')
                    ->weight('semibold')
                    ->toggleable(),

                // Start date
                TextColumn::make('start_date')
                    ->label('Start Date')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

            ])

            ->defaultSort('enrollment_date', 'desc')

            ->filters([

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'enrolled'    => 'Enrolled',
                        'active'      => 'Active',
                        'completed'   => 'Completed',
                        'transferred' => 'Transferred',
                        'withdrawn'   => 'Withdrawn',
                        'graduated'   => 'Graduated',
                    ])
                    ->native(false),

                SelectFilter::make('payment_status')
                    ->label('Payment Status')
                    ->options([
                        'pending' => 'Pending',
                        'partial' => 'Partial',
                        'paid'    => 'Paid',
                    ])
                    ->native(false),

            ])

            ->recordActions([

                ViewAction::make()->label(''),
                EditAction::make()->label(''),

                // Activate enrollment
                Action::make('activate')
                    ->label('Activate')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function ($record): void {
                        $record->activate();
                        Notification::make()->title('Enrollment Activated')->success()->send();
                    })
                    ->visible(fn ($record): bool => $record->status === 'enrolled'),

                // Withdraw enrollment
                Action::make('withdraw')
                    ->label('Withdraw')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->form([
                        DatePicker::make('withdrawal_date')
                            ->label('Withdrawal Date')
                            ->required()
                            ->native(false)
                            ->default(today())
                            ->maxDate(today()),

                        Textarea::make('withdrawal_reason')
                            ->label('Reason for Withdrawal')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function ($record, array $data): void {
                        $record->update([
                            'status'            => 'withdrawn',
                            'withdrawal_date'   => $data['withdrawal_date'],
                            'withdrawal_reason' => $data['withdrawal_reason'],
                        ]);

                        Notification::make()
                            ->title('Enrollment Withdrawn')
                            ->warning()
                            ->send();
                    })
                    ->visible(fn ($record): bool =>
                    in_array($record->status, ['enrolled', 'active'])
                    ),

            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])

            ->emptyStateIcon('heroicon-o-user-group')
            ->emptyStateHeading('No Enrollments Yet')
            ->emptyStateDescription('Enrolled students will appear here.')
            ->striped()
            ->paginated([15, 25, 50]);
    }
}
