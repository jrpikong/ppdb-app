<?php

namespace App\Filament\School\Resources\PaymentTypes\Pages;

use App\Filament\School\Resources\PaymentTypes\PaymentTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPaymentTypes extends ListRecords
{
    protected static string $resource = PaymentTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Add Payment Type')->icon('heroicon-o-plus')];
    }
}
