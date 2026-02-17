<?php

namespace App\Filament\School\Resources\AcademicYears\Pages;

use App\Filament\School\Resources\AcademicYears\AcademicYearResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateAcademicYear extends CreateRecord
{
    protected static string $resource = AcademicYearResource::class;

    public function getTitle(): string
    {
        return 'Create Academic Year';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Academic Year created!')
            ->body('Academic Year created!');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Auto-generate name if not set
        if (empty($data['name']) && !empty($data['start_year']) && !empty($data['end_year'])) {
            $data['name'] = $data['start_year'] . '/' . $data['end_year'];
        }

        return $data;
    }
}
