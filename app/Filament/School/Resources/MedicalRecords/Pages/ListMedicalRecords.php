<?php
declare(strict_types=1);
namespace App\Filament\School\Resources\MedicalRecords\Pages;

use App\Filament\School\Resources\MedicalRecords\MedicalRecordResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMedicalRecords extends ListRecords
{
    protected static string $resource = MedicalRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('New Medical Record')
                ->icon('heroicon-o-plus'),
        ];
    }
}
