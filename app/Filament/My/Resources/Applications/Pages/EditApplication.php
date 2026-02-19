<?php

declare(strict_types=1);

namespace App\Filament\My\Resources\Applications\Pages;

use App\Filament\My\Resources\Applications\ApplicationResource;
use App\Filament\My\Resources\Applications\Pages\Concerns\CanSubmitApplication;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditApplication extends EditRecord
{
    use CanSubmitApplication;

    protected static string $resource = ApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('submitApplication')
                ->label('Submit Application')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn (): bool => $this->getRecord()->status === 'draft')
                ->action(function (): void {
                    $record = $this->getRecord();
                    $this->submitApplication($record);

                    $record->refresh();

                    if ($record->status === 'submitted') {
                        $this->redirect($this->getResource()::getUrl('view', ['record' => $record]));
                    }
                }),
            DeleteAction::make()
                ->visible(fn (): bool => $this->getRecord()->status === 'draft'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}
