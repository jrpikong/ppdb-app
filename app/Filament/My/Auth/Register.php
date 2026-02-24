<?php

declare(strict_types=1);

namespace App\Filament\My\Auth;

use App\Models\Role;
use Filament\Auth\Pages\Register as BaseRegister;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\PermissionRegistrar;

class Register extends BaseRegister
{
    protected static string $layout = 'filament-panels::components.layout.base';

    protected string $view = 'filament.my.auth.register';

    protected static int $maxAttempts = 5;

    public function getTitle(): string | Htmlable
    {
        return 'Admission Portal Register';
    }

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
                    ->placeholder('+62-812-xxxx-xxxx'),

                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),

                Checkbox::make('terms')
                    ->label(new HtmlString(
                        'I agree to the <a href="/terms" target="_blank" class="text-primary-600 underline">Terms & Conditions</a> ' .
                        'and <a href="/privacy" target="_blank" class="fi-color fi-color-primary fi-text-color-600 dark:fi-text-color-300 fi-link fi-size-md fi-ac-link-action underline">Privacy Policy</a>'
                    ))
                    ->accepted()
                    ->validationMessages([
                        'accepted' => 'You must accept the Terms & Conditions to register.',
                    ])
                    ->dehydrated(false),
            ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function mutateFormDataBeforeRegister(array $data): array
    {
        $data['school_id'] = 0;
        $data['is_active'] = true;

        unset($data['terms']);

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRegistration(array $data): Model
    {
        $user = parent::handleRegistration($data);

        app(PermissionRegistrar::class)->setPermissionsTeamId(0);

        $parentRole = Role::query()
            ->where('name', 'parent')
            ->where('school_id', 0)
            ->where('guard_name', 'web')
            ->first();

        if (! $parentRole) {
            $parentRole = Role::create([
                'name' => 'parent',
                'school_id' => 0,
                'guard_name' => 'web',
            ]);

            report(new \RuntimeException(
                'Parent role was missing, please run RolePermissionSeeder to assign correct permissions.'
            ));
        }

        $user->assignRole($parentRole);

        return $user;
    }

    /**
     * @return array<int, mixed>
     */
    protected function getPasswordValidationRules(): array
    {
        return [
            'required',
            'string',
            Password::min(8)
                ->letters()
                ->numbers(),
            'confirmed',
        ];
    }
}

