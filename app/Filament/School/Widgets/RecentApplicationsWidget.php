<?php

declare(strict_types=1);

namespace App\Filament\School\Widgets;

use App\Models\Application;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentApplicationsWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Recent Applications';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Application::query()
                    ->where('school_id', Filament::getTenant()?->id)
                    ->whereNotNull('submitted_at')
                    ->latest('submitted_at')
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('application_number')
                    ->label('Application #')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                TextColumn::make('student_first_name')
                    ->label('Student Name')
                    ->formatStateUsing(fn($state, $record): string => trim($record->student_first_name . ' ' . $record->student_last_name)
                    )
                    ->searchable(['student_first_name', 'student_last_name'])
                    ->sortable(),

                TextColumn::make('level.name')
                    ->label('Level')
                    ->badge()
                    ->color('info'),

                TextColumn::make('submitted_at')
                    ->label('Submitted')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->description(fn($record): string => $record->submitted_at?->diffForHumans() ?? '—'
                    ),

                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'submitted' => 'Submitted',
                        'under_review' => 'Under Review',
                        'accepted' => 'Accepted',
                        'rejected' => 'Rejected',
                        'waitlist' => 'Waitlist',
                        'enrolled' => 'Enrolled',
                        default => ucfirst($state),
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'submitted' => 'info',
                        'under_review', 'waitlist' => 'warning',
                        'accepted' => 'success',
                        'rejected' => 'danger',
                        'enrolled' => 'primary',
                        default => 'gray',
                    }),

                TextColumn::make('assigned_to')
                    ->label('Assigned To')
                    ->formatStateUsing(fn($state, $record): string => $record->assignee?->name ?? '—'
                    )
                    ->placeholder('Unassigned'),
            ])
            ->toolbarActions([
                Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->url(function ($record) {
                        if (! $record) {
                            return null;
                        }

                        return route('filament.school.resources.applications.view', [
                            'tenant' => Filament::getTenant(),
                            'record' => $record,
                        ]);
                    }),
            ])
            ->paginated(false);
    }
}
