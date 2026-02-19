<?php

declare(strict_types=1);

namespace App\Filament\My\Resources\Schedules\Pages;

use App\Filament\My\Resources\Schedules\ScheduleResource;
use Filament\Resources\Pages\ListRecords;

class ListSchedules extends ListRecords
{
    protected static string $resource = ScheduleResource::class;
}

