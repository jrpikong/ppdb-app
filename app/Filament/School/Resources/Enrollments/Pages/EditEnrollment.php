<?php
declare(strict_types=1);
namespace App\Filament\School\Resources\Enrollments\Pages;

use App\Filament\School\Resources\Enrollments\EnrollmentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEnrollment extends EditRecord
{
    protected static string $resource = EnrollmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Auto-update payment status based on amounts
        $due = $data['total_amount_due'] ?? 0;
        $paid = $data['total_amount_paid'] ?? 0;

        $data['balance'] = max(0, $due - $paid);

        if ($paid >= $due && $due > 0) {
            $data['payment_status'] = 'paid';
        } elseif ($paid > 0) {
            $data['payment_status'] = 'partial';
        } else {
            $data['payment_status'] = 'pending';
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Enrollment updated successfully';
    }
}
