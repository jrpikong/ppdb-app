<?php

declare(strict_types=1);

namespace App\Filament\SuperAdmin\Resources\Schools\Pages;

use App\Filament\SuperAdmin\Resources\Schools\SchoolResource;
use App\Models\School;
use App\Models\User;
use App\Services\Tenancy\SchoolProvisioningService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CreateSchool extends CreateRecord
{
    protected static string $resource = SchoolResource::class;

    /**
     * @var array{name:string,email:string,password:string}
     */
    protected array $superAdminPayload = [];

    protected ?User $provisionedSuperAdmin = null;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['code'] = strtoupper(trim((string) ($data['code'] ?? '')));

        $this->superAdminPayload = [
            'name' => (string) ($data['admin_name'] ?? ''),
            'email' => (string) ($data['admin_email'] ?? ''),
            'password' => (string) ($data['admin_password'] ?? ''),
        ];

        unset(
            $data['admin_name'],
            $data['admin_email'],
            $data['admin_password'],
            $data['admin_password_confirmation'],
        );

        if (blank($data['principal_name'] ?? null)) {
            $data['principal_name'] = $this->superAdminPayload['name'];
        }

        if (blank($data['principal_email'] ?? null)) {
            $data['principal_email'] = $this->superAdminPayload['email'];
        }

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        if (
            blank($this->superAdminPayload['name'])
            || blank($this->superAdminPayload['email'])
            || blank($this->superAdminPayload['password'])
        ) {
            throw new RuntimeException('Super admin payload is incomplete.');
        }

        return DB::transaction(function () use ($data): Model {
            /** @var School $school */
            $school = School::query()->create($data);

            $employeeId = sprintf('%s-ADMIN-%03d', strtoupper($school->code), 1);

            $this->provisionedSuperAdmin = app(SchoolProvisioningService::class)
                ->provisionSchoolTenant($school, [
                    ...$this->superAdminPayload,
                    'employee_id' => $employeeId,
                    'occupation' => 'School Principal',
                    'department' => 'Leadership & Management',
                ]);

            return $school;
        }, 3);
    }

    protected function getCreatedNotification(): ?Notification
    {
        $adminEmail = $this->provisionedSuperAdmin?->email;

        return Notification::make()
            ->title('School created and tenant provisioned')
            ->body($adminEmail ? "Initial school super admin created: {$adminEmail}" : null)
            ->success();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

