<?php
declare(strict_types=1);
namespace App\Filament\School\Resources\MedicalRecords\Pages;

use App\Filament\School\Resources\MedicalRecords\MedicalRecordResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMedicalRecord extends CreateRecord
{
    protected static string $resource = MedicalRecordResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Medical record created successfully';
    }
}
