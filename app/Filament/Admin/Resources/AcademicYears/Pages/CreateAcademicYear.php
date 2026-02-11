<?php

namespace App\Filament\Admin\Resources\AcademicYears\Pages;

use App\Filament\Admin\Resources\AcademicYears\AcademicYearResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateAcademicYear extends CreateRecord
{
    protected static string $resource = AcademicYearResource::class;

    public function getTitle(): string
    {
        return 'Buat Tahun Ajaran Baru';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Tahun Ajaran Dibuat')
            ->body('Tahun ajaran berhasil ditambahkan.');
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
