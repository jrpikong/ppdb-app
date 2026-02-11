<?php

namespace App\Filament\SuperAdmin\Resources\AcademicYears\Pages;

use App\Filament\SuperAdmin\Resources\AcademicYears\AcademicYearResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAcademicYears extends ListRecords
{
    protected static string $resource = AcademicYearResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Buat Tahun Ajaran')
                ->icon('heroicon-m-plus'),
        ];
    }

    public function getTitle(): string
    {
        return 'Tahun Ajaran';
    }

    protected function getHeaderWidgets(): array
    {
        return [
            // Add widgets here if needed
        ];
    }
}
