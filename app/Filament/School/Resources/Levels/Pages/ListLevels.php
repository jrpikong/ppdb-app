<?php

namespace App\Filament\School\Resources\Levels\Pages;

use App\Filament\School\Resources\Levels\LevelResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLevels extends ListRecords
{
    protected static string $resource = LevelResource::class;

    protected function getHeaderActions(): array
    {

        return [
            CreateAction::make()
                ->label('New Level')
                ->icon('heroicon-o-plus'),
        ];
    }
}
