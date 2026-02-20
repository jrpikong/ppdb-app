<?php

namespace App\Filament\School\Resources\Applications\Pages;

use App\Filament\School\Resources\Applications\ApplicationResource;
use App\Models\ActivityLog;
use App\Models\Application;
use App\Support\ParentNotifier;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditApplication extends EditRecord
{
    protected static string $resource = ApplicationResource::class;

    protected ?string $oldStatusBeforeSave = null;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Application updated successfully';
    }

    protected function beforeSave(): void
    {
        $this->oldStatusBeforeSave = (string) $this->getRecord()->status;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $record = $this->getRecord();
        $currentStatus = (string) $record->status;
        $newStatus = (string) ($data['status'] ?? $currentStatus);

        if ($newStatus !== $currentStatus && ! $record->canTransitionTo($newStatus)) {
            throw ValidationException::withMessages([
                'data.status' => sprintf(
                    'Invalid status transition: %s -> %s.',
                    Application::statusLabelFor($currentStatus),
                    Application::statusLabelFor($newStatus),
                ),
            ]);
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $record = $this->getRecord();

        if (! $record->wasChanged('status')) {
            return;
        }

        ParentNotifier::applicationStatusChanged(
            application: $record->refresh(),
            fromStatus: $this->oldStatusBeforeSave ?? (string) $record->status,
            toStatus: (string) $record->status,
            notes: $record->status_notes,
        );

        ActivityLog::logActivity(
            description: sprintf(
                'Application status changed: %s -> %s',
                Application::statusLabelFor($this->oldStatusBeforeSave ?? (string) $record->status),
                Application::statusLabelFor((string) $record->status)
            ),
            subject: $record,
            logName: 'application',
            event: 'status_changed',
            properties: [
                'from_status' => $this->oldStatusBeforeSave,
                'to_status' => (string) $record->status,
                'notes' => $record->status_notes,
            ],
            userId: auth()->id()
        );
    }
}
