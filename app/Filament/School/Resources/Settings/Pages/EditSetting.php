<?php

namespace App\Filament\School\Resources\Settings\Pages;

use App\Filament\School\Resources\Settings\SettingResource;
use Filament\Facades\Filament;
use Filament\Resources\Pages\EditRecord;
use RuntimeException;

class EditSetting extends EditRecord
{
    protected static string $resource = SettingResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $tenantId = Filament::getTenant()?->id;

        if (! $tenantId) {
            throw new RuntimeException('Tenant context is not available.');
        }

        $data['default_school_id'] = $tenantId;

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
