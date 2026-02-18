<?php
declare(strict_types=1);
namespace App\Filament\School\Resources\Enrollments\Pages;

use App\Filament\School\Resources\Enrollments\EnrollmentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEnrollment extends CreateRecord
{
    protected static string $resource = EnrollmentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['enrolled_by'] = auth()->id();

        // Auto-calculate balance
        $due = $data['total_amount_due'] ?? 0;
        $paid = $data['total_amount_paid'] ?? 0;
        $data['balance'] = max(0, $due - $paid);

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Enrollment created successfully';
    }
}
