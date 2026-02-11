<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{User, School};
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('👥 Creating Users...');

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        DB::beginTransaction();

        try {

            /*
            |--------------------------------------------------------------------------
            | Load roles once (safe)
            |--------------------------------------------------------------------------
            */

            $roles = Role::where('guard_name', 'web')
                ->get()
                ->keyBy('name');

            foreach (['super_admin','school_admin','admission_admin','finance_admin','parent'] as $role) {
                if (!isset($roles[$role])) {
                    throw new \Exception("Role missing: {$role}");
                }
            }

            /*
            |--------------------------------------------------------------------------
            | 1. SUPER ADMIN (GLOBAL TENANT = 0)
            |--------------------------------------------------------------------------
            */

            $this->command->info('  Creating Super SuperAdmin...');

            app(PermissionRegistrar::class)->setPermissionsTeamId(0);

            $superAdmin = User::create([
                'name' => 'Dr. John Anderson',
                'email' => 'superadmin@vis.sch.id',
                'password' => Hash::make('password'),
                'phone' => '+62-812-3456-7890',
                'is_active' => true,
                'email_verified_at' => now(),
            ]);

            $superAdmin->assignRole($roles['super_admin']);

            $this->command->info('    ✓ Dr. John Anderson');

            /*
            |--------------------------------------------------------------------------
            | 2. SCHOOL STAFF (TENANT = school_id)
            |--------------------------------------------------------------------------
            */

            $staffData = [
                'VIS-BIN' => [
                    ['name' => 'Sarah Johnson', 'role' => 'school_admin'],
                    ['name' => 'Michael Chen', 'role' => 'admission_admin'],
                    ['name' => 'Lisa Wong', 'role' => 'finance_admin'],
                ],
                'VIS-KG' => [
                    ['name' => 'David Kumar', 'role' => 'school_admin'],
                    ['name' => 'Emma Wilson', 'role' => 'admission_admin'],
                    ['name' => 'Robert Lee', 'role' => 'finance_admin'],
                ],
                'VIS-BALI' => [
                    ['name' => 'Amanda Martinez', 'role' => 'school_admin'],
                    ['name' => 'James Taylor', 'role' => 'admission_admin'],
                    ['name' => 'Michelle Tan', 'role' => 'finance_admin'],
                ],
            ];

            $this->command->info('  Creating School Staff...');

            foreach (School::all() as $school) {

                if (!isset($staffData[$school->code])) {
                    continue;
                }

                app(PermissionRegistrar::class)
                    ->setPermissionsTeamId($school->id);

                $this->command->info("    {$school->name}:");

                foreach ($staffData[$school->code] as $staff) {

                    $email = strtolower(str_replace(' ', '.', $staff['name']))
                        . '@' . strtolower($school->code) . '.sch.id';

                    $user = User::create([
                        'name' => $staff['name'],
                        'email' => $email,
                        'password' => Hash::make('password'),
                        'phone' => '+62-812-' . rand(1000, 9999) . '-' . rand(1000, 9999),
                        'school_id' => $school->id,
                        'is_active' => true,
                        'email_verified_at' => now(),
                    ]);

                    $user->assignRole($roles[$staff['role']]);

                    $this->command->info("      ✓ {$staff['name']} ({$staff['role']})");
                }
            }

            /*
            |--------------------------------------------------------------------------
            | 3. PARENTS (GLOBAL TENANT = 0)
            |--------------------------------------------------------------------------
            */

            $this->command->info('  Creating Parent Users...');

            app(PermissionRegistrar::class)->setPermissionsTeamId(0);

            $parents = [
                ['William Thompson','william.thompson@email.com'],
                ['Jennifer Martinez','jennifer.martinez@email.com'],
                ['Alexander Brown','alexander.brown@email.com'],
                ['Sophia Anderson','sophia.anderson@email.com'],
                ['Benjamin Davis','benjamin.davis@email.com'],
                ['Olivia Wilson','olivia.wilson@email.com'],
                ['Daniel Garcia','daniel.garcia@email.com'],
                ['Emma Rodriguez','emma.rodriguez@email.com'],
                ['Matthew Lee','matthew.lee@email.com'],
                ['Isabella Kim','isabella.kim@email.com'],
            ];

            foreach ($parents as [$name, $email]) {

                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'password' => Hash::make('password'),
                    'phone' => '+62-813-' . rand(1000, 9999) . '-' . rand(1000, 9999),
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]);

                $user->assignRole($roles['parent']);
            }

            DB::commit();

            $this->command->newLine();
            $this->command->info('✅ USERS SEEDING COMPLETE');

        } catch (\Throwable $e) {

            DB::rollBack();

            $this->command->error("✗ Error: {$e->getMessage()}");

            throw $e;
        }
    }
}
