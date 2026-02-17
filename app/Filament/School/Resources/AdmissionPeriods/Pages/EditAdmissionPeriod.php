<?php

namespace App\Filament\School\Resources\AdmissionPeriods\Pages;

use App\Filament\School\Resources\AdmissionPeriods\AdmissionPeriodResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditAdmissionPeriod extends EditRecord
{
    protected static string $resource = AdmissionPeriodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
