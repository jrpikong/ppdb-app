<?php

declare(strict_types=1);

namespace App\Filament\My\Resources\Profiles\Pages;

use App\Filament\My\Resources\Profiles\ProfileResource;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;

class EditProfile extends EditRecord
{
    protected static string $resource = ProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('changePassword')
                ->label('Change Password')
                ->icon('heroicon-o-key')
                ->color('warning')
                ->schema([
                    TextInput::make('current_password')
                        ->label('Current Password')
                        ->password()
                        ->revealable()
                        ->required(),
                    TextInput::make('new_password')
                        ->label('New Password')
                        ->password()
                        ->revealable()
                        ->required()
                        ->minLength(8)
                        ->confirmed(),
                    TextInput::make('new_password_confirmation')
                        ->label('Confirm New Password')
                        ->password()
                        ->revealable()
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $record = $this->getRecord();

                    if (! Hash::check($data['current_password'], $record->password)) {
                        Notification::make()
                            ->title('Password update failed')
                            ->body('Current password is incorrect.')
                            ->danger()
                            ->send();

                        return;
                    }

                    $record->update([
                        'password' => $data['new_password'],
                    ]);

                    Notification::make()
                        ->title('Password updated')
                        ->success()
                        ->send();
                }),
            Action::make('resendVerificationEmail')
                ->label('Resend Verification Email')
                ->icon('heroicon-o-envelope')
                ->color('gray')
                ->visible(fn (): bool => $this->getRecord()->email_verified_at === null)
                ->action(function (): void {
                    $this->getRecord()->sendEmailVerificationNotification();

                    Notification::make()
                        ->title('Verification email sent')
                        ->body('Please check your inbox and spam folder.')
                        ->success()
                        ->send();
                }),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['password']);

        return $data;
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Profile updated successfully';
    }
}

