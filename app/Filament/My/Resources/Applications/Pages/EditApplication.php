<?php

declare(strict_types=1);

namespace App\Filament\My\Resources\Applications\Pages;

use App\Filament\My\Resources\Applications\ApplicationResource;
use App\Filament\My\Resources\Applications\Pages\Concerns\CanSubmitApplication;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditApplication extends EditRecord
{
    use CanSubmitApplication;

    protected static string $resource = ApplicationResource::class;

    // ── Redirect tetap di edit page setelah save ──────────────────────────────
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->getRecord()]);
    }

    // ── Notifikasi save final (tombol Save di footer) ─────────────────────────
    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->title('Application saved')
            ->body('All your changes have been saved.')
            ->success();
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if ($this->getRecord()->status !== 'draft') {
            throw ValidationException::withMessages([
                'data.status' => 'Submitted applications are read-only.',
            ]);
        }

        unset(
            $data['id'],
            $data['user_id'],
            $data['application_number'],
            $data['status'],
            $data['status_notes'],
            $data['submitted_at'],
            $data['reviewed_at'],
            $data['decision_made_at'],
            $data['enrolled_at'],
            $data['decision_letter'],
            $data['decision_letter_file'],
            $data['assigned_to'],
            $data['reviewed_by'],
            $data['requires_observation'],
            $data['requires_test'],
            $data['requires_interview'],
            $data['deleted_at'],
            $data['created_at'],
            $data['updated_at'],
        );

        return $data;
    }

    // ── Header actions ────────────────────────────────────────────────────────
    protected function getHeaderActions(): array
    {
        return [
            Action::make('submitApplication')
                ->label('Submit Application')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Submit Application')
                ->modalDescription(
                    'Once submitted, your application will be sent to the school for review ' .
                    'and you will no longer be able to edit it. Are you sure everything is correct?'
                )
                ->modalSubmitActionLabel('Yes, Submit Now')
                ->visible(fn (): bool => $this->getRecord()->status === 'draft')
                ->action(fn () => $this->submitApplication($this->getRecord())),

            ViewAction::make()
                ->label('View Application')
                ->icon('heroicon-o-eye'),
        ];
    }
}
