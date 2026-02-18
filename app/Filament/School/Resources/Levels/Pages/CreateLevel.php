<?php

namespace App\Filament\School\Resources\Levels\Pages;

use App\Filament\School\Resources\Levels\LevelResource;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateLevel extends CreateRecord
{
    protected static string $resource = LevelResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['school_id'] = Filament::getTenant()->id;
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Level created successfully';
    }
}
