<?php

namespace App\Filament\School\Resources\Applications\Pages;

use App\Filament\School\Resources\Applications\ApplicationResource;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateApplication extends CreateRecord
{
    protected static string $resource = ApplicationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Auto-set school_id from current tenant
        $data['school_id'] = Filament::getTenant()->id;

        // Generate application number
        $data['application_number'] = $this->generateApplicationNumber();

        // Set default status
        $data['status'] = $data['status'] ?? 'draft';

        return $data;
    }

    protected function generateApplicationNumber(): string
    {
        $school = Filament::getTenant();
        $year = date('y');
        $month = date('m');

        // Format: [SCHOOL_CODE]-[YY][MM]-[SEQUENCE]
        // Example: VIS-BIN-2502-0001

        $lastApplication = \App\Models\Application::where('school_id', $school->id)
            ->whereYear('created_at', date('Y'))
            ->whereMonth('created_at', date('m'))
            ->latest('id')
            ->first();

        $sequence = $lastApplication ? (int) substr($lastApplication->application_number, -4) + 1 : 1;

        return sprintf(
            '%s-%s%s-%04d',
            $school->code,
            $year,
            $month,
            $sequence
        );
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Application created successfully';
    }
}
