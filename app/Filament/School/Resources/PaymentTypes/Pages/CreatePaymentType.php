<?php

namespace App\Filament\School\Resources\PaymentTypes\Pages;

use App\Filament\School\Resources\PaymentTypes\PaymentTypeResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Components\Section;

class CreatePaymentType extends CreateRecord
{
    protected static string $resource = PaymentTypeResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['school_id'] = Section::getTenant()->id;
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
