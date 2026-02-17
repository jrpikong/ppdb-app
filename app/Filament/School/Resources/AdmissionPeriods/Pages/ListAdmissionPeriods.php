<?php

namespace App\Filament\School\Resources\AdmissionPeriods\Pages;

use App\Filament\School\Resources\AdmissionPeriods\AdmissionPeriodResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAdmissionPeriods extends ListRecords
{
    protected static string $resource = AdmissionPeriodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Create Admission Period')
                ->icon('heroicon-m-plus'),
        ];
    }
}
