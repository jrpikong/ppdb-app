<?php

namespace App\Filament\School\Resources\Users\Pages;

use App\Filament\School\Resources\Users\UserResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [

            // Edit
            EditAction::make(),

            // Toggle active/inactive
            Action::make('toggleActive')
                ->label(fn (): string => $this->getRecord()->is_active ? 'Deactivate Account' : 'Activate Account')
                ->icon(fn (): string => $this->getRecord()->is_active
                    ? 'heroicon-o-x-circle'
                    : 'heroicon-o-check-circle'
                )
                ->color(fn (): string => $this->getRecord()->is_active ? 'danger' : 'success')
                ->requiresConfirmation()
                ->modalHeading(fn (): string =>
                    ($this->getRecord()->is_active ? 'Deactivate ' : 'Activate ') . $this->getRecord()->name
                )
                ->modalDescription(fn (): string =>
                $this->getRecord()->is_active
                    ? 'This staff member will immediately lose access to the system.'
                    : 'This staff member will regain access to the system.'
                )
                ->action(function (): void {
                    $record = $this->getRecord();
                    $record->update(['is_active' => !$record->is_active]);

                    Notification::make()
                        ->title($record->is_active ? 'Account Activated' : 'Account Deactivated')
                        ->body("Access for {$record->name} has been updated.")
                        ->success()
                        ->send();

                    $this->refreshFormData(['is_active']);
                }),

            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
