<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\AdmissionPeriod;
use App\Models\Level;
use App\Models\Permission;
use App\Models\PaymentType;
use App\Models\Role;
use App\Models\School;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\TenantSuperAdminWelcomeNotification;
use App\Services\Tenancy\SchoolProvisioningService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class SuperAdminSchoolProvisioningTest extends TestCase
{
    use RefreshDatabase;

    public function test_school_provisioning_creates_tenant_super_admin_role_from_global_template(): void
    {
        Notification::fake();

        app(PermissionRegistrar::class)->setPermissionsTeamId(0);

        $permissions = collect([
            'view_any_school',
            'create_school',
            'update_school',
            'delete_school',
            'view_dashboard',
        ])->map(
            fn (string $name): Permission => Permission::query()->firstOrCreate([
                'name' => $name,
                'guard_name' => 'web',
            ])
        );

        $globalTemplateRole = Role::query()->firstOrCreate([
            'name' => 'super_admin',
            'guard_name' => 'web',
            'school_id' => 0,
        ]);
        $globalTemplateRole->syncPermissions($permissions);

        $school = School::query()->create([
            'code' => 'VIS-TEN',
            'name' => 'VIS Tenancy Test',
            'type' => 'branch',
            'city' => 'Jakarta',
            'country' => 'Indonesia',
            'is_active' => true,
            'allow_online_admission' => true,
        ]);

        $service = app(SchoolProvisioningService::class);
        $tenantSuperAdmin = $service->provisionSchoolTenant($school, [
            'name' => 'Tenant Principal',
            'email' => 'tenant.principal@test.local',
            'password' => 'password',
        ]);

        $tenantRole = Role::query()
            ->where('name', 'super_admin')
            ->where('guard_name', 'web')
            ->where('school_id', $school->id)
            ->first();

        $this->assertNotNull($tenantRole);
        $this->assertSame($school->id, $tenantSuperAdmin->school_id);
        $this->assertSame('tenant.principal@test.local', $tenantSuperAdmin->email);
        $this->assertTrue((bool) $tenantSuperAdmin->is_active);

        $this->assertSame(
            $globalTemplateRole->permissions->pluck('name')->sort()->values()->all(),
            $tenantRole->permissions->pluck('name')->sort()->values()->all()
        );

        app(PermissionRegistrar::class)->setPermissionsTeamId($school->id);
        $this->assertTrue($tenantSuperAdmin->hasRole('super_admin'));

        $pivotExists = DB::table(config('permission.table_names.model_has_roles'))
            ->where(config('permission.column_names.team_foreign_key'), $school->id)
            ->where('role_id', $tenantRole->id)
            ->where('model_type', User::class)
            ->where('model_id', $tenantSuperAdmin->id)
            ->exists();

        $this->assertTrue($pivotExists);

        $this->assertSame(1, AcademicYear::query()->where('school_id', $school->id)->count());
        $this->assertSame(12, Level::query()->where('school_id', $school->id)->count());
        $this->assertSame(1, AdmissionPeriod::query()->where('school_id', $school->id)->count());
        $this->assertSame(6, PaymentType::query()->where('school_id', $school->id)->count());
        $this->assertSame(1, Setting::query()->where('default_school_id', $school->id)->count());

        $this->assertTrue((bool) AcademicYear::query()->where('school_id', $school->id)->first()?->is_active);
        $this->assertTrue((bool) AdmissionPeriod::query()->where('school_id', $school->id)->first()?->allow_applications);
        $this->assertSame(
            12,
            Level::query()->where('school_id', $school->id)->where('is_accepting_applications', true)->count()
        );
        $this->assertSame(
            6,
            PaymentType::query()->where('school_id', $school->id)->where('is_active', true)->count()
        );

        $this->assertTrue(
            (bool) Setting::query()->where('default_school_id', $school->id)->first()?->online_admission_enabled
        );

        Notification::assertSentTo(
            $tenantSuperAdmin,
            TenantSuperAdminWelcomeNotification::class,
            function (TenantSuperAdminWelcomeNotification $notification, array $channels) use ($tenantSuperAdmin): bool {
                $mail = $notification->toMail($tenantSuperAdmin);
                $lines = implode(' ', [...$mail->introLines, ...$mail->outroLines]);

                return in_array('mail', $channels, true)
                    && str_contains($lines, 'Login email: tenant.principal@test.local')
                    && str_contains($lines, 'Temporary password: password')
                    && str_contains((string) $mail->actionUrl, '/school/s/VIS-TEN');
            }
        );
    }

    public function test_school_provisioning_reuses_existing_tenant_super_admin_role(): void
    {
        Notification::fake();

        app(PermissionRegistrar::class)->setPermissionsTeamId(0);

        Permission::query()->firstOrCreate([
            'name' => 'view_dashboard',
            'guard_name' => 'web',
        ]);

        $globalTemplateRole = Role::query()->firstOrCreate([
            'name' => 'super_admin',
            'guard_name' => 'web',
            'school_id' => 0,
        ]);
        $globalTemplateRole->syncPermissions(['view_dashboard']);

        $school = School::query()->create([
            'code' => 'VIS-RS',
            'name' => 'VIS Reuse School',
            'type' => 'branch',
            'city' => 'Bandung',
            'country' => 'Indonesia',
            'is_active' => true,
            'allow_online_admission' => true,
        ]);

        $service = app(SchoolProvisioningService::class);
        $service->provisionSchoolTenant($school, [
            'name' => 'First Admin',
            'email' => 'first.admin@test.local',
            'password' => 'password',
        ]);
        $service->provisionSchoolTenant($school, [
            'name' => 'Second Admin',
            'email' => 'second.admin@test.local',
            'password' => 'password',
        ]);

        $this->assertSame(
            1,
            Role::query()
                ->where('name', 'super_admin')
                ->where('guard_name', 'web')
                ->where('school_id', $school->id)
                ->count()
        );

        $this->assertSame(1, AcademicYear::query()->where('school_id', $school->id)->count());
        $this->assertSame(12, Level::query()->where('school_id', $school->id)->count());
        $this->assertSame(1, AdmissionPeriod::query()->where('school_id', $school->id)->count());
        $this->assertSame(6, PaymentType::query()->where('school_id', $school->id)->count());
        $this->assertSame(1, Setting::query()->where('default_school_id', $school->id)->count());

        $firstAdmin = User::query()->where('email', 'first.admin@test.local')->firstOrFail();
        $secondAdmin = User::query()->where('email', 'second.admin@test.local')->firstOrFail();

        Notification::assertSentTo($firstAdmin, TenantSuperAdminWelcomeNotification::class);
        Notification::assertSentTo($secondAdmin, TenantSuperAdminWelcomeNotification::class);
    }
}
