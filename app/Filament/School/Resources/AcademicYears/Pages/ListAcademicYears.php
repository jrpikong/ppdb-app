<?php

namespace App\Filament\School\Resources\AcademicYears\Pages;

use App\Filament\School\Resources\AcademicYears\AcademicYearResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAcademicYears extends ListRecords
{
    protected static string $resource = AcademicYearResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Create Academic Year')
                ->icon('heroicon-m-plus'),
        ];
    }

    public function getTitle(): string
    {
        return 'Academic Years';
    }

    protected function getHeaderWidgets(): array
    {
        return [
            // Add widgets here if needed
        ];
    }
}
