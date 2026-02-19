<?php

declare(strict_types=1);

namespace App\Filament\My\Resources\Applications\Tables;

use App\Models\Application;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\Layout\Panel;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ApplicationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                // ── Left block: status icon + app number ──────────────────
                Split::make([
                    Stack::make([
                        TextColumn::make('application_number')
                            ->label('Application #')
                            ->searchable()
                            ->sortable()
                            ->weight('bold')
                            ->size('sm')
                            ->copyable()
                            ->copyMessage('Copied!')
                            ->placeholder('— draft —'),

                        TextColumn::make('created_at')
                            ->label('Applied')
                            ->since()
                            ->color('gray')
                            ->size('xs'),
                    ])->space(1),

                    // ── Centre: student name + school ─────────────────────
                    Stack::make([
                        TextColumn::make('student_full_name')
                            ->label('Student')
                            ->getStateUsing(fn (Application $record): string =>
                            trim("{$record->student_first_name} {$record->student_last_name}")
                            )
                            ->searchable(query: function ($query, string $search): void {
                                $query->where(function ($q) use ($search): void {
                                    $q->where('student_first_name', 'like', "%{$search}%")
                                        ->orWhere('student_last_name', 'like', "%{$search}%");
                                });
                            })
                            ->weight('semibold'),

                        TextColumn::make('school.name')
                            ->label('School')
                            ->color('gray')
                            ->size('xs'),
                    ])->space(1),

                    // ── Right: level + status badges ──────────────────────
                    Stack::make([
                        TextColumn::make('level.name')
                            ->label('Level')
                            ->badge()
                            ->color('info')
                            ->size('xs'),

                        TextColumn::make('status')
                            ->badge()
                            ->size('xs')
                            ->formatStateUsing(fn (string $state): string =>
                            (string) str($state)->replace('_', ' ')->title()
                            )
                            ->color(fn (string $state): string => match ($state) {
                                'draft'                              => 'gray',
                                'submitted'                          => 'blue',
                                'under_review', 'documents_verified' => 'warning',
                                'interview_scheduled'                => 'purple',
                                'interview_completed'                => 'violet',
                                'payment_pending'                    => 'orange',
                                'payment_verified'                   => 'lime',
                                'accepted', 'enrolled'               => 'success',
                                'rejected'                           => 'danger',
                                'waitlisted'                         => 'amber',
                                'withdrawn'                          => 'gray',
                                default                              => 'gray',
                            }),
                    ])->space(1)->grow(false),
                ]),

                // ── Expandable panel: extra detail ────────────────────────
                Panel::make([
                    Split::make([
                        TextColumn::make('admissionPeriod.name')
                            ->label('Admission Period')
                            ->icon('heroicon-m-calendar-days')
                            ->color('gray')
                            ->size('xs'),

                        TextColumn::make('documents_count')
                            ->label('Documents')
                            ->counts('documents')
                            ->icon('heroicon-m-paper-clip')
                            ->color('gray')
                            ->size('xs'),

                        TextColumn::make('payments_count')
                            ->label('Payments')
                            ->counts('payments')
                            ->icon('heroicon-m-credit-card')
                            ->color('gray')
                            ->size('xs'),

                        TextColumn::make('submitted_at')
                            ->label('Submitted')
                            ->dateTime('d M Y')
                            ->placeholder('Not submitted')
                            ->icon('heroicon-m-paper-airplane')
                            ->color('gray')
                            ->size('xs'),
                    ]),
                ])->collapsible(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(Application::statusOptions())
                    ->native(false),

                SelectFilter::make('school_id')
                    ->label('School')
                    ->relationship('school', 'name')
                    ->searchable()
                    ->preload()
                    ->native(false),
            ])
            ->recordActions([
                ViewAction::make()->iconButton(),
                EditAction::make()
                    ->iconButton()
                    ->visible(fn (Application $record): bool => $record->status === 'draft'),
                DeleteAction::make()
                    ->iconButton()
                    ->visible(fn (Application $record): bool => $record->status === 'draft')
                    ->requiresConfirmation(),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->emptyStateIcon('heroicon-o-document-text')
            ->emptyStateHeading('No applications yet')
            ->emptyStateDescription('Start your child\'s admission journey by creating a new application.')
            ->emptyStateActions([
                \Filament\Actions\CreateAction::make()
                    ->label('New Application')
                    ->icon('heroicon-o-plus'),
            ]);
    }
}
