<?php

declare(strict_types=1);

namespace App\Filament\My\Auth;

use App\Models\Role;
use Filament\Auth\Pages\Register as BaseRegister;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\PermissionRegistrar;

class Register extends BaseRegister
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getNameFormComponent(),
                $this->getEmailFormComponent(),
                TextInput::make('phone')
                    ->label('Phone Number')
                    ->tel()
                    ->maxLength(20)
                    ->nullable()
                    ->placeholder('+62-812-xxxx-xxxx'),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
            ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function mutateFormDataBeforeRegister(array $data): array
    {
        $data['school_id'] = 0;
        $data['is_active'] = true;

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRegistration(array $data): Model
    {
        $user = parent::handleRegistration($data);

        $registrar = app(PermissionRegistrar::class);
        $registrar->setPermissionsTeamId(0);

        $parentRole = Role::query()->firstOrCreate([
            'name' => 'parent',
            'school_id' => 0,
            'guard_name' => 'web',
        ]);

        $user->assignRole($parentRole);

        return $user;
    }
}
