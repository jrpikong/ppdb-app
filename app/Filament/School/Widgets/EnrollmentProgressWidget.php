<?php

declare(strict_types=1);

namespace App\Filament\School\Widgets;

use App\Models\Enrollment;
use App\Models\Level;
use Filament\Facades\Filament;
use Filament\Support\Enums\TextSize;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class EnrollmentProgressWidget extends BaseWidget
{
    protected static ?int $sort = 5;

    protected int | string | array $columnSpan = 2;

    protected static ?string $heading = 'Enrollment Progress by Level';

    public function table(Table $table): Table
    {
        $schoolId = Filament::getTenant()?->id;

        return $table
            ->query(
                Level::query()
                    ->where('school_id', $schoolId)
                    ->where('is_active', true)
                    ->orderBy('sort_order')
            )
            ->columns([
                TextColumn::make('code')
                    ->label('Level')
                    ->badge()
                    ->color('primary')
                    ->size(TextSize::Large)
                    ->weight('bold'),

                TextColumn::make('name')
                    ->label('Grade')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                TextColumn::make('enrolled_count')
                    ->label('Enrollment')
                    ->state(function (Level $record) use ($schoolId): string {
                        $enrolled = Enrollment::whereHas('application', function (Builder $q) use ($schoolId, $record) {
                            $q->where('school_id', $schoolId)
                                ->where('level_id', $record->id);
                        })
                            ->whereIn('status', ['enrolled', 'active'])
                            ->count();

                        return "{$enrolled} / {$record->quota}";
                    })
                    ->badge()
                    ->color(function (Level $record) use ($schoolId): string {
                        $enrolled = Enrollment::whereHas('application', function (Builder $q) use ($schoolId, $record) {
                            $q->where('school_id', $schoolId)
                                ->where('level_id', $record->id);
                        })
                            ->whereIn('status', ['enrolled', 'active'])
                            ->count();

                        $percentage = $record->quota > 0 ? ($enrolled / $record->quota) * 100 : 0;

                        return match (true) {
                            $percentage >= 100 => 'danger',
                            $percentage >= 90, $percentage >= 75 => 'warning',
                            $percentage >= 50  => 'info',
                            default            => 'success',
                        };
                    }),

                TextColumn::make('progress')
                    ->label('Progress')
                    ->html()
                    ->state(function (Level $record) use ($schoolId): HtmlString {
                        $enrolled = Enrollment::whereHas('application', function (Builder $q) use ($schoolId, $record) {
                            $q->where('school_id', $schoolId)
                                ->where('level_id', $record->id);
                        })
                            ->whereIn('status', ['enrolled', 'active'])
                            ->count();

                        $percentage = $record->quota > 0 ? round(($enrolled / $record->quota) * 100, 1) : 0;
                        $width = min($percentage, 100);

                        $color = match (true) {
                            $percentage >= 100 => '#ef4444',
                            $percentage >= 90  => '#f97316',
                            $percentage >= 75  => '#f59e0b',
                            $percentage >= 50  => '#3b82f6',
                            default            => '#10b981',
                        };

                        return new HtmlString("
                            <div class='w-full'>
                                <div class='flex items-center justify-between mb-1'>
                                    <span class='text-xs font-semibold'>{$percentage}%</span>
                                </div>
                                <div class='w-full bg-gray-200 rounded-full h-2'>
                                    <div class='h-2 rounded-full transition-all' style='width: {$width}%; background-color: {$color}'></div>
                                </div>
                            </div>
                        ");
                    }),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->state(function (Level $record) use ($schoolId): string {
                        $enrolled = Enrollment::whereHas('application', function (Builder $q) use ($schoolId, $record) {
                            $q->where('school_id', $schoolId)
                                ->where('level_id', $record->id);
                        })
                            ->whereIn('status', ['enrolled', 'active'])
                            ->count();

                        $available = max(0, $record->quota - $enrolled);
                        $percentage = $record->quota > 0 ? ($enrolled / $record->quota) * 100 : 0;

                        return match (true) {
                            $percentage >= 100 => '🚫 Full',
                            $percentage >= 90  => "⚠️ {$available} left",
                            $percentage >= 75  => "📊 {$available} slots",
                            $percentage >= 50  => "📈 {$available} open",
                            default            => "✅ {$available} available",
                        };
                    })
                    ->color(function (Level $record) use ($schoolId): string {
                        $enrolled = Enrollment::whereHas('application', function (Builder $q) use ($schoolId, $record) {
                            $q->where('school_id', $schoolId)
                                ->where('level_id', $record->id);
                        })
                            ->whereIn('status', ['enrolled', 'active'])
                            ->count();

                        $percentage = $record->quota > 0 ? ($enrolled / $record->quota) * 100 : 0;

                        return match (true) {
                            $percentage >= 100 => 'danger',
                            $percentage >= 90, $percentage >= 75 => 'warning',
                            $percentage >= 50  => 'info',
                            default            => 'success',
                        };
                    }),
            ])
            ->paginated(false);
    }
}
