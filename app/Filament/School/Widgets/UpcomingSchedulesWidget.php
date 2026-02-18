<?php

declare(strict_types=1);

namespace App\Filament\School\Widgets;

use App\Models\Schedule;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class UpcomingSchedulesWidget extends BaseWidget
{
    protected static ?int $sort = 6;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Upcoming Schedules';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Schedule::query()
                    ->whereHas('application', fn (Builder $q) =>
                        $q->where('school_id', Filament::getTenant()?->id)
                    )
                    ->whereIn('status', ['scheduled', 'confirmed'])
                    ->where('scheduled_date', '>=', today())
                    ->orderBy('scheduled_date')
                    ->orderBy('scheduled_time')
                    ->limit(8)
            )
            ->columns([
                TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'observation' => '👀 Obs',
                        'test'        => '📝 Test',
                        'interview'   => '🗣️ Interview',
                        default       => ucfirst($state),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'observation' => 'info',
                        'test'        => 'warning',
                        'interview'   => 'primary',
                        default       => 'gray',
                    }),

                TextColumn::make('application.student_first_name')
                    ->label('Student')
                    ->formatStateUsing(fn ($state, $record): string =>
                        trim($record->application?->student_first_name . ' ' .
                             $record->application?->student_last_name)
                    )
                    ->limit(20)
                    ->weight('semibold'),

                TextColumn::make('scheduled_date')
                    ->label('Date & Time')
                    ->date('d M')
                    ->description(fn ($record): string =>
                        \Carbon\Carbon::parse($record->scheduled_time)->format('H:i')
                    )
                    ->color(fn ($record): string =>
                        $record->scheduled_date->isToday() ? 'warning' : 'gray'
                    ),

                TextColumn::make('interviewer.name')
                    ->label('Staff')
                    ->limit(15)
                    ->placeholder('—'),
            ])
            ->toolbarActions([
                Action::make('view')
                    ->label('')
                    ->icon('heroicon-o-eye')
                    ->url(function ($record) {
                        if (! $record) {
                            return null;
                        }

                        return route('filament.school.resources.schedules.view', [
                            'tenant' => Filament::getTenant(),
                            'record' => $record,
                        ]);
                    }),
            ])
            ->paginated(false);
    }
}
