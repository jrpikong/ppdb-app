<?php

namespace App\Filament\School\Resources\Users\Pages;

use App\Filament\School\Resources\Users\UserResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Add Staff Member')
                ->icon('heroicon-o-user-plus'),
        ];
    }
}
