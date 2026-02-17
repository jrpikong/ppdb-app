<?php

namespace App\Filament\School\Resources\Users\Pages;

use App\Filament\School\Resources\Users\UserResource;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    // ── Auto-assign school_id dari tenant aktif ───────────────────────────
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['school_id']          = Filament::getTenant()->id;
        $data['email_verified_at']  = now(); // Auto-verify staff emails
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Staff member created successfully';
    }
}
