<?php

namespace App\Filament\School\Resources\Users\Pages;

use App\Filament\School\Resources\Users\UserResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [

            // ── Quick: Reset password ─────────────────────────────────────
            Action::make('resetPassword')
                ->label('Reset Password')
                ->icon('heroicon-o-key')
                ->color('warning')
                ->form([
                    TextInput::make('new_password')
                        ->label('New Password')
                        ->password()
                        ->revealable()
                        ->required()
                        ->minLength(8)
                        ->confirmed(),

                    TextInput::make('new_password_confirmation')
                        ->label('Confirm Password')
                        ->password()
                        ->revealable()
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $this->getRecord()->update([
                        'password' => Hash::make($data['new_password']),
                    ]);

                    Notification::make()
                        ->title('Password Reset')
                        ->body('Password has been reset successfully.')
                        ->success()
                        ->send();
                }),

            // ── Toggle active/inactive ────────────────────────────────────
            Action::make('toggleActive')
                ->label(fn (): string => $this->getRecord()->is_active ? 'Deactivate' : 'Activate')
                ->icon(fn (): string => $this->getRecord()->is_active
                    ? 'heroicon-o-x-circle'
                    : 'heroicon-o-check-circle'
                )
                ->color(fn (): string => $this->getRecord()->is_active ? 'danger' : 'success')
                ->requiresConfirmation()
                ->action(function (): void {
                    $record = $this->getRecord();
                    $record->update(['is_active' => !$record->is_active]);

                    Notification::make()
                        ->title($record->is_active ? 'Account Activated' : 'Account Deactivated')
                        ->success()
                        ->send();

                    $this->refreshFormData(['is_active']);
                }),

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
        return 'Staff member updated successfully';
    }
}
