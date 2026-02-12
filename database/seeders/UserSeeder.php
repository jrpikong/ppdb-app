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
            | 1. SUPER ADMIN (GLOBAL TENANT = 0)
            |--------------------------------------------------------------------------
            */

            $this->command->info('  Creating Super Admin...');

            // ✅ Set team context to 0 for super admin
            app(PermissionRegistrar::class)->setPermissionsTeamId(0);

            $superAdmin = User::create([
                'name' => 'Dr. John Anderson',
                'email' => 'superadmin@vis.sch.id',
                'password' => Hash::make('password'),
                'phone' => '+62-812-3456-7890',
                'school_id' => 0,
                'is_active' => true,
                'email_verified_at' => now(),
            ]);

            // ✅ Get role with school_id = 0
            $superAdminRole = Role::where('name', 'super_admin')
                ->where('guard_name', 'web')
                ->where('school_id', 0)
                ->firstOrFail();

            $superAdmin->assignRole($superAdminRole);

            $this->command->info('    ✓ Dr. John Anderson (super_admin)');

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

                // ✅ CRITICAL: Set team context to this school's ID
                app(PermissionRegistrar::class)->setPermissionsTeamId($school->id);

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

                    // ✅ Get or create role for THIS school
                    $role = Role::where('name', $staff['role'])
                        ->where('guard_name', 'web')
                        ->where('school_id', $school->id)
                        ->first();

                    if (!$role) {
                        // ✅ Role doesn't exist for this school - create it
                        $role = Role::create([
                            'name' => $staff['role'],
                            'guard_name' => 'web',
                            'school_id' => $school->id,
                        ]);

                        // ✅ Copy ALL permissions from global role (including Shield permissions!)
                        $globalRole = Role::where('name', $staff['role'])
                            ->where('guard_name', 'web')
                            ->where('school_id', 0)
                            ->first();

                        if ($globalRole) {
                            $role->syncPermissions($globalRole->permissions);
                            $this->command->info("      → Created {$staff['role']} role for {$school->code} ({$role->permissions->count()} permissions)");
                        }
                    }

                    $user->assignRole($role);

                    $this->command->info("      ✓ {$staff['name']} ({$staff['role']})");
                }
            }

            /*
            |--------------------------------------------------------------------------
            | 3. PARENTS (GLOBAL TENANT = 0)
            |--------------------------------------------------------------------------
            */

            $this->command->info('  Creating Parent Users...');

            // ✅ Reset team context to 0 for parents
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

            // ✅ Get parent role (global, school_id = 0)
            $parentRole = Role::where('name', 'parent')
                ->where('guard_name', 'web')
                ->where('school_id', 0)
                ->firstOrFail();

            foreach ($parents as [$name, $email]) {

                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'password' => Hash::make('password'),
                    'phone' => '+62-813-' . rand(1000, 9999) . '-' . rand(1000, 9999),
                    'school_id' => 0,
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]);

                $user->assignRole($parentRole);
            }

            DB::commit();

            $this->command->newLine();
            $this->command->info('✅ USERS SEEDING COMPLETE');
            $this->command->info('   Total users: ' . User::count());
            $this->command->info('   Total roles: ' . Role::count());
            $this->command->info('   Total role assignments: ' . DB::table('model_has_roles')->count());

        } catch (\Throwable $e) {

            DB::rollBack();

            $this->command->error("✗ Error: {$e->getMessage()}");
            $this->command->error("  File: {$e->getFile()}:{$e->getLine()}");

            throw $e;
        }
    }
}
