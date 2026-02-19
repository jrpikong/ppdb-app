<?php

declare(strict_types=1);

namespace App\Filament\My\Resources\Payments\Pages;

use App\Filament\My\Resources\Payments\PaymentResource;
use Filament\Resources\Pages\ListRecords;

class ListPayments extends ListRecords
{
    protected static string $resource = PaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
