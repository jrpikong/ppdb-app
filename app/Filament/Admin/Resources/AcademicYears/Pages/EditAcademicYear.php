<?php

namespace App\Filament\Admin\Resources\AcademicYears\Pages;

use App\Filament\Admin\Resources\AcademicYears\AcademicYearResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditAcademicYear extends EditRecord
{
    protected static string $resource = AcademicYearResource::class;

    public function getTitle(): string
    {
        return 'Edit Tahun Ajaran';
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Hapus'),
            ForceDeleteAction::make()
                ->label('Hapus Permanen'),
            RestoreAction::make()
                ->label('Pulihkan'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Tahun Ajaran Diperbarui')
            ->body('Perubahan berhasil disimpan.');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Auto-generate name if not set
        if (empty($data['name']) && !empty($data['start_year']) && !empty($data['end_year'])) {
            $data['name'] = $data['start_year'] . '/' . $data['end_year'];
        }

        return $data;
    }
}
