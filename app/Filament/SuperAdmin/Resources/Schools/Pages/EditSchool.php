<?php

declare(strict_types=1);

namespace App\Filament\SuperAdmin\Resources\Schools\Pages;

use App\Filament\SuperAdmin\Resources\Schools\SchoolResource;
use Filament\Resources\Pages\EditRecord;

class EditSchool extends EditRecord
{
    protected static string $resource = SchoolResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['code'] = strtoupper(trim((string) ($data['code'] ?? '')));

        unset(
            $data['admin_name'],
            $data['admin_email'],
            $data['admin_password'],
            $data['admin_password_confirmation'],
        );

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

