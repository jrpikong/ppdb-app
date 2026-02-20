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
            | Global super admin: akses ke /superadmin panel (no tenant).
            | school_id = 0 → tidak terikat ke sekolah manapun.
            |--------------------------------------------------------------------------
            */

            $this->command->info('  Creating Global Super Admin...');

            app(PermissionRegistrar::class)->setPermissionsTeamId(0);

            $superAdmin = User::create([
                'name'               => 'Dr. John Anderson',
                'email'              => 'superadmin@vis.sch.id',
                'password'           => Hash::make('password'),
                'phone'              => '+62-812-3456-7890',
                'occupation'         => 'Group Director',
                'address'            => 'Head Office VIS, Jakarta',
                'school_id'          => 0,
                'is_active'          => true,
                'email_verified_at'  => now(),
            ]);

            $globalSuperAdminRole = Role::where('name', 'super_admin')
                ->where('guard_name', 'web')
                ->where('school_id', 0)
                ->firstOrFail();

            $superAdmin->assignRole($globalSuperAdminRole);

            $this->command->info('    ✓ Dr. John Anderson (super_admin - global)');

            /*
            |--------------------------------------------------------------------------
            | 2. SCHOOL STAFF (TENANT = school_id)
            |--------------------------------------------------------------------------
            | Setiap sekolah memiliki:
            |   - 1 super_admin  → akses penuh di school panel (tenant-scoped)
            |   - 1 school_admin → manajemen sekolah
            |   - 1 admission_admin → proses penerimaan
            |   - 1 finance_admin → manajemen pembayaran
            |--------------------------------------------------------------------------
            */

            $staffData = [
                'VIS-BIN' => [
                    ['name' => 'Sarah Johnson',   'role' => 'super_admin'],
                    ['name' => 'Michael Chen',    'role' => 'school_admin'],
                    ['name' => 'Lisa Wong',       'role' => 'admission_admin'],
                    ['name' => 'Robert Bintaro',  'role' => 'finance_admin'],
                ],
                'VIS-KG' => [
                    ['name' => 'David Kumar',     'role' => 'super_admin'],
                    ['name' => 'Emma Wilson',     'role' => 'school_admin'],
                    ['name' => 'Robert Lee',      'role' => 'admission_admin'],
                    ['name' => 'Cynthia Park',    'role' => 'finance_admin'],
                ],
                'VIS-BALI' => [
                    ['name' => 'Amanda Martinez', 'role' => 'super_admin'],
                    ['name' => 'James Taylor',    'role' => 'school_admin'],
                    ['name' => 'Michelle Tan',    'role' => 'admission_admin'],
                    ['name' => 'Kevin Sanjaya',   'role' => 'finance_admin'],
                ],
            ];

            $this->command->info('  Creating School Staff...');

            foreach (School::all() as $school) {

                if (!isset($staffData[$school->code])) {
                    continue;
                }

                // ✅ CRITICAL: Set team context ke school_id ini
                app(PermissionRegistrar::class)->setPermissionsTeamId($school->id);

                $this->command->info("    {$school->name} (ID: {$school->id}):");

                foreach ($staffData[$school->code] as $staff) {

                    $emailSlug = strtolower(str_replace(' ', '.', $staff['name']));
                    $schoolSlug = strtolower(str_replace('-', '-', $school->code));
                    $email = "{$emailSlug}@{$schoolSlug}.sch.id";

                    $user = User::create([
                        'name'              => $staff['name'],
                        'email'             => $email,
                        'password'          => Hash::make('password'),
                        'phone'             => '+62-812-' . rand(1000, 9999) . '-' . rand(1000, 9999),
                        'occupation'        => str_replace('_', ' ', $staff['role']),
                        'address'           => "Campus office {$school->name}, {$school->city}",
                        'school_id'         => $school->id,
                        'is_active'         => true,
                        'email_verified_at' => now(),
                    ]);

                    // ✅ Cari role yang sudah ada untuk school ini
                    $role = Role::where('name', $staff['role'])
                        ->where('guard_name', 'web')
                        ->where('school_id', $school->id)
                        ->first();

                    if (!$role) {
                        // ✅ Buat role baru untuk school ini, copy permission dari global role
                        $globalRole = Role::where('name', $staff['role'])
                            ->where('guard_name', 'web')
                            ->where('school_id', 0)
                            ->first();

                        $role = Role::create([
                            'name'       => $staff['role'],
                            'guard_name' => 'web',
                            'school_id'  => $school->id,
                        ]);

                        if ($globalRole) {
                            $role->syncPermissions($globalRole->permissions);
                            $this->command->info(
                                "      → Created {$staff['role']} role for {$school->code} " .
                                "({$role->permissions->count()} permissions)"
                            );
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
            | Parent tidak terikat ke sekolah tertentu. school_id = 0.
            | Mereka hanya bisa akses public registration form, bukan panel admin.
            |--------------------------------------------------------------------------
            */

            $this->command->info('  Creating Parent Users...');

            // ✅ Reset team context ke 0 untuk parents
            app(PermissionRegistrar::class)->setPermissionsTeamId(0);

            $parents = [
                ['William Thompson',  'william.thompson@email.com'],
                ['Jennifer Martinez', 'jennifer.martinez@email.com'],
                ['Alexander Brown',   'alexander.brown@email.com'],
                ['Sophia Anderson',   'sophia.anderson@email.com'],
                ['Benjamin Davis',    'benjamin.davis@email.com'],
                ['Olivia Wilson',     'olivia.wilson@email.com'],
                ['Daniel Garcia',     'daniel.garcia@email.com'],
                ['Emma Rodriguez',    'emma.rodriguez@email.com'],
                ['Matthew Lee',       'matthew.lee@email.com'],
                ['Isabella Kim',      'isabella.kim@email.com'],
            ];

            $parentRole = Role::where('name', 'parent')
                ->where('guard_name', 'web')
                ->where('school_id', 0)
                ->firstOrFail();

            foreach ($parents as [$name, $email]) {

                $user = User::create([
                    'name'              => $name,
                    'email'             => $email,
                    'password'          => Hash::make('password'),
                    'phone'             => '+62-813-' . rand(1000, 9999) . '-' . rand(1000, 9999),
                    'occupation'        => collect([
                        'Entrepreneur', 'Doctor', 'Software Engineer', 'Teacher', 'Finance Manager',
                    ])->random(),
                    'address'           => collect([
                        'Bintaro, Tangerang Selatan',
                        'Kelapa Gading, Jakarta Utara',
                        'Sanur, Denpasar',
                        'BSD City, Tangerang',
                    ])->random(),
                    'school_id'         => 0,
                    'is_active'         => true,
                    'email_verified_at' => now(),
                ]);

                $user->assignRole($parentRole);
            }

            DB::commit();

            // ==================== SUMMARY ====================

            $this->command->newLine();
            $this->command->info('════════════════════════════════════════');
            $this->command->info('✅ USERS SEEDING COMPLETE');
            $this->command->info('════════════════════════════════════════');
            $this->command->table(
                ['Category', 'Count'],
                [
                    ['Total Users',            User::count()],
                    ['Global Super Admin',      1],
                    ['School Super Admins',     3],
                    ['School Admins',           3],
                    ['Admission Admins',        3],
                    ['Finance Admins',          3],
                    ['Parents',                 10],
                    ['Total Roles',             Role::count()],
                    ['Total Role Assignments',  DB::table('model_has_roles')->count()],
                ]
            );
            $this->command->newLine();
            $this->command->info('🔑 Login Credentials (password: "password")');
            $this->command->info('   Global Super Admin : superadmin@vis.sch.id');
            $this->command->info('   VIS-BIN Super Admin: sarah.johnson@vis-bin.sch.id');
            $this->command->info('   VIS-KG  Super Admin: david.kumar@vis-kg.sch.id');
            $this->command->info('   VIS-BALI Super Admin: amanda.martinez@vis-bali.sch.id');
            $this->command->newLine();

        } catch (\Throwable $e) {

            DB::rollBack();

            $this->command->error("✗ Error: {$e->getMessage()}");
            $this->command->error("  File: {$e->getFile()}:{$e->getLine()}");

            throw $e;
        }
    }
}
