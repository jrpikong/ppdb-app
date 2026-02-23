<?php

namespace App\Filament\School\Resources\Settings\Pages;

use App\Filament\School\Resources\Settings\SettingResource;
use Filament\Facades\Filament;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSettings extends ListRecords
{
    protected static string $resource = SettingResource::class;

    protected function getHeaderActions(): array
    {
        $tenantId = Filament::getTenant()?->id;

        if (! $tenantId) {
            return [];
        }

        if ($this->getTableQuery()->where('default_school_id', $tenantId)->count() > 0) {
            return [];
        }

        return [
            CreateAction::make()->icon('heroicon-o-plus'),
        ];
    }
}
