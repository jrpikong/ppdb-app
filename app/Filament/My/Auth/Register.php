<?php

declare(strict_types=1);

namespace App\Filament\My\Auth;

use App\Models\Role;
use Filament\Auth\Pages\Register as BaseRegister;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\PermissionRegistrar;

class Register extends BaseRegister
{
    // ── Rate limiting: max 5 attempts per IP per minute ───────────────────────
    protected static int $maxAttempts = 5;

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

                // Terms & Conditions — required before registering
                Checkbox::make('terms')
                    ->label(new HtmlString(
                        'I agree to the <a href="/terms" target="_blank" class="text-primary-600 underline">Terms & Conditions</a> ' .
                        'and <a href="/privacy" target="_blank" class="fi-color fi-color-primary fi-text-color-600 dark:fi-text-color-300 fi-link fi-size-md  fi-ac-link-action underline">Privacy Policy</a>'
                    ))
                    ->accepted()               // must be ticked
                    ->validationMessages([
                        'accepted' => 'You must accept the Terms & Conditions to register.',
                    ])
                    ->dehydrated(false),       // don't save to DB — only for validation
            ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function mutateFormDataBeforeRegister(array $data): array
    {
        $data['school_id'] = 0;
        $data['is_active']  = true;

        // Remove terms field — it's not a DB column
        unset($data['terms']);

        // Do NOT set email_verified_at here.
        // Let Filament handle email verification flow normally.
        // If you want to skip verification for parents, uncomment below:
        // $data['email_verified_at'] = now();

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRegistration(array $data): Model
    {
        $user = parent::handleRegistration($data);
//        event(new \Illuminate\Auth\Events\Registered($user));

        // Set team context to 0 (global) before assigning role
        app(PermissionRegistrar::class)->setPermissionsTeamId(0);

        // Look up the existing parent role — created by RolePermissionSeeder.
        // Using firstOrCreate as safety net in case seeder hasn't been run.
        $parentRole = Role::query()
            ->where('name', 'parent')
            ->where('school_id', 0)
            ->where('guard_name', 'web')
            ->first();

        if (! $parentRole) {
            // Seeder hasn't been run yet — create the role with minimal permissions
            // so registration doesn't hard-fail in development.
            $parentRole = Role::create([
                'name'       => 'parent',
                'school_id'  => 0,
                'guard_name' => 'web',
            ]);

            report(new \RuntimeException(
                'Parent role was missing — please run RolePermissionSeeder to assign correct permissions.'
            ));
        }

        $user->assignRole($parentRole);

        return $user;
    }

    /**
     * Override password rules to be stricter for public-facing registration.
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
