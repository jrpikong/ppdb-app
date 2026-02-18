<?php
declare(strict_types=1);
namespace App\Filament\School\Resources\Schedules\Pages;

use App\Filament\School\Resources\Schedules\ScheduleResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSchedule extends CreateRecord
{
    protected static string $resource = ScheduleResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Schedule created successfully';
    }
}
