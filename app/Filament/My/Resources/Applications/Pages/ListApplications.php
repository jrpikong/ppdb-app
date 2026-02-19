<?php

declare(strict_types=1);

namespace App\Filament\My\Resources\Applications\Pages;

use App\Filament\My\Resources\Applications\ApplicationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListApplications extends ListRecords
{
    protected static string $resource = ApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Start New Application')
                ->icon('heroicon-o-plus'),
        ];
    }
}
