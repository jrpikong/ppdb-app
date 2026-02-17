<?php

namespace App\Filament\School\Resources\Applications\Pages;

use App\Filament\School\Resources\Applications\ApplicationResource;
use App\Filament\School\Resources\Applications\Widgets\ApplicationsByStatusWidget;
use App\Filament\School\Resources\Applications\Widgets\ApplicationsChartWidget;
use App\Filament\School\Resources\Applications\Widgets\ApplicationStatsWidget;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Pages\ListRecords;

class ListApplications extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = ApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->icon('heroicon-o-plus')
                ->label('New Application'),
//            Action::make('export')
//                ->label('Export Applications')
//                ->icon('heroicon-o-arrow-down-tray')
//                ->color('success')
//                ->url(fn () => route('filament.school.resources.applications.export'))
//                ->openUrlInNewTab(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ApplicationStatsWidget::class,
            ApplicationsByStatusWidget::class,
            ApplicationsChartWidget::class,
        ];
    }

    public function getTitle(): string
    {
        return 'Applications';
    }
}
