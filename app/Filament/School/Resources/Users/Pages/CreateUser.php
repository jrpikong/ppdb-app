<?php

namespace App\Filament\School\Resources\Users\Pages;

use App\Filament\School\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
}
