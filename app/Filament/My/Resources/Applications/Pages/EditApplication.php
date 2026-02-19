<?php

declare(strict_types=1);

namespace App\Filament\My\Resources\Applications\Pages;

use App\Filament\My\Resources\Applications\ApplicationResource;
use App\Filament\My\Resources\Applications\Pages\Concerns\CanSubmitApplication;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

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
