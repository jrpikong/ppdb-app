<?php

declare(strict_types=1);

namespace App\Filament\My\Resources\Applications\Pages;

use App\Filament\My\Resources\Applications\ApplicationResource;
use App\Filament\My\Resources\Applications\Pages\Concerns\CanSubmitApplication;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewApplication extends ViewRecord
{
    use CanSubmitApplication;

    protected static string $resource = ApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadAcceptanceLetter')
                ->label('Download Acceptance Letter')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->url(fn () => route('secure-files.applications.acceptance-letter', $this->getRecord()))
                ->openUrlInNewTab()
                ->visible(fn () => in_array($this->getRecord()->status, ['accepted', 'enrolled'], true)),
            EditAction::make()
                ->visible(fn () => $this->getRecord()->status === 'draft'),
            Action::make('submitApplication')
                ->label('Submit Application')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn () => $this->getRecord()->status === 'draft')
                ->action(function (): void {
                    $record = $this->getRecord();
                    $this->submitApplication($record);

                    $record->refresh();
                    $this->refreshFormData(['status', 'submitted_at']);
                }),
        ];
    }
}
