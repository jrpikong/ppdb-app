<?php

namespace App\Filament\School\Resources\Applications\Pages;

use App\Filament\School\Resources\Applications\ApplicationResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewApplication extends ViewRecord
{
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
            EditAction::make(),
            DeleteAction::make(),
        ];
    }
}
