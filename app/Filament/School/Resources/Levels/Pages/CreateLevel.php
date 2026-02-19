<?php

namespace App\Filament\School\Resources\Levels\Pages;

use App\Filament\School\Resources\Levels\LevelResource;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use RuntimeException;

class CreateLevel extends CreateRecord
{
    protected static string $resource = LevelResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $tenantId = Filament::getTenant()?->id;

        if (! $tenantId) {
            throw new RuntimeException('Tenant context is not available.');
        }

        $data['school_id'] = $tenantId;
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
