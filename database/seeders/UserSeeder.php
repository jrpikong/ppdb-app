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
            | Setiap sekolah memiliki 4 staff dengan role berbeda:
            |   - 1 super_admin    → akses penuh di school panel (IT / Kepala Sistem)
            |   - 1 school_admin   → manajemen sekolah (Principal)
            |   - 1 admission_admin → proses penerimaan
            |   - 1 finance_admin  → manajemen pembayaran
            |--------------------------------------------------------------------------
            */

            $staffData = [
                'VIS-BIN' => [
                    ['name' => 'Sarah Johnson',    'role' => 'super_admin',    'occupation' => 'School Principal'],
                    ['name' => 'Michael Chen',     'role' => 'school_admin',   'occupation' => 'Academic Director'],
                    ['name' => 'Lisa Wong',        'role' => 'admission_admin','occupation' => 'Head of Admissions'],
                    ['name' => 'Robert Bintaro',   'role' => 'finance_admin',  'occupation' => 'Finance Manager'],
                ],
                'VIS-KG' => [
                    ['name' => 'David Kumar',      'role' => 'super_admin',    'occupation' => 'School Principal'],
                    ['name' => 'Emma Wilson',      'role' => 'school_admin',   'occupation' => 'Academic Director'],
                    ['name' => 'Robert Lee',       'role' => 'admission_admin','occupation' => 'Head of Admissions'],
                    ['name' => 'Cynthia Park',     'role' => 'finance_admin',  'occupation' => 'Finance Manager'],
                ],
                'VIS-BALI' => [
                    ['name' => 'Amanda Martinez',  'role' => 'super_admin',    'occupation' => 'School Principal'],
                    ['name' => 'James Taylor',     'role' => 'school_admin',   'occupation' => 'Academic Director'],
                    ['name' => 'Michelle Tan',     'role' => 'admission_admin','occupation' => 'Head of Admissions'],
                    ['name' => 'Kevin Sanjaya',    'role' => 'finance_admin',  'occupation' => 'Finance Manager'],
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

                    $emailSlug   = strtolower(str_replace([' ', "'"], ['.', ''], $staff['name']));
                    $schoolSlug  = strtolower($school->code);
                    $email       = "{$emailSlug}@{$schoolSlug}.sch.id";

                    $user = User::create([
                        'name'              => $staff['name'],
                        'email'             => $email,
                        'password'          => Hash::make('password'),
                        'phone'             => '+62-812-' . rand(1000, 9999) . '-' . rand(1000, 9999),
                        'occupation'        => $staff['occupation'],
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

                    $this->command->info("      ✓ {$staff['name']} ({$staff['role']}) — {$email}");
                }
            }

            /*
            |--------------------------------------------------------------------------
            | 3. PARENTS (GLOBAL TENANT = 0)
            |--------------------------------------------------------------------------
            | 25 parent users mencakup kebutuhan 45 aplikasi (15 per sekolah × 3).
            | Setiap parent memiliki ~2 anak yang mendaftar ke sekolah berbeda.
            | Parents tidak terikat ke sekolah tertentu: school_id = 0.
            | Akses: portal /my (bukan panel admin).
            |--------------------------------------------------------------------------
            */

            $this->command->info('  Creating Parent Users (25 parents)...');

            // ✅ Reset team context ke 0 untuk parents
            app(PermissionRegistrar::class)->setPermissionsTeamId(0);

            $parents = [
                // Batch 1 — International Mix (mostly Jakarta / Bintaro area)
                ['name' => 'William Thompson',   'email' => 'william.thompson@email.com',   'occ' => 'Software Engineer',    'addr' => 'Bintaro, Tangerang Selatan'],
                ['name' => 'Jennifer Martinez',  'email' => 'jennifer.martinez@email.com',  'occ' => 'Doctor',               'addr' => 'Bintaro, Tangerang Selatan'],
                ['name' => 'Alexander Brown',    'email' => 'alexander.brown@email.com',    'occ' => 'Business Owner',       'addr' => 'BSD City, Tangerang'],
                ['name' => 'Sophia Anderson',    'email' => 'sophia.anderson@email.com',    'occ' => 'Marketing Manager',    'addr' => 'Bintaro, Tangerang Selatan'],
                ['name' => 'Benjamin Davis',     'email' => 'benjamin.davis@email.com',     'occ' => 'Financial Analyst',    'addr' => 'Pondok Indah, Jakarta Selatan'],

                // Batch 2 — Kelapa Gading / Jakarta Utara area
                ['name' => 'Olivia Wilson',      'email' => 'olivia.wilson@email.com',      'occ' => 'Entrepreneur',         'addr' => 'Kelapa Gading, Jakarta Utara'],
                ['name' => 'Daniel Garcia',      'email' => 'daniel.garcia@email.com',      'occ' => 'Lawyer',               'addr' => 'Kelapa Gading, Jakarta Utara'],
                ['name' => 'Emma Rodriguez',     'email' => 'emma.rodriguez@email.com',     'occ' => 'Architect',            'addr' => 'Sunter, Jakarta Utara'],
                ['name' => 'Matthew Lee',        'email' => 'matthew.lee@email.com',        'occ' => 'Finance Manager',      'addr' => 'Kelapa Gading, Jakarta Utara'],
                ['name' => 'Isabella Kim',       'email' => 'isabella.kim@email.com',       'occ' => 'Teacher',              'addr' => 'Tanjung Priok, Jakarta Utara'],

                // Batch 3 — Bali / Expat mix
                ['name' => 'Jonathan Park',      'email' => 'jonathan.park@email.com',      'occ' => 'Consultant',           'addr' => 'Sanur, Denpasar'],
                ['name' => 'Priya Sharma',       'email' => 'priya.sharma@email.com',       'occ' => 'Doctor',               'addr' => 'Seminyak, Badung'],
                ['name' => 'David Nguyen',       'email' => 'david.nguyen@email.com',       'occ' => 'Project Manager',      'addr' => 'Sanur, Denpasar'],
                ['name' => 'Sarah Chen',         'email' => 'sarah.chen@email.com',         'occ' => 'Interior Designer',    'addr' => 'Canggu, Badung'],
                ['name' => 'Ryan Johnson',       'email' => 'ryan.johnson@email.com',       'occ' => 'Entrepreneur',         'addr' => 'Ubud, Gianyar'],

                // Batch 4 — Multi-campus parents (kids in different schools)
                ['name' => 'Mei Lin Zhang',      'email' => 'meilin.zhang@email.com',       'occ' => 'Software Engineer',    'addr' => 'Bintaro, Tangerang Selatan'],
                ['name' => 'Patrick O\'Brien',   'email' => 'patrick.obrien@email.com',     'occ' => 'Business Owner',       'addr' => 'Kelapa Gading, Jakarta Utara'],
                ['name' => 'Anita Krishnan',     'email' => 'anita.krishnan@email.com',     'occ' => 'Accountant',           'addr' => 'Pondok Pinang, Jakarta Selatan'],
                ['name' => 'Thomas Mueller',     'email' => 'thomas.mueller@email.com',     'occ' => 'Engineer',             'addr' => 'Kemang, Jakarta Selatan'],
                ['name' => 'Yuki Tanaka',        'email' => 'yuki.tanaka@email.com',        'occ' => 'Marketing Director',   'addr' => 'Menteng, Jakarta Pusat'],

                // Batch 5 — Additional diverse parents
                ['name' => 'Robert Santos',      'email' => 'robert.santos@email.com',      'occ' => 'HR Manager',           'addr' => 'Kelapa Gading, Jakarta Utara'],
                ['name' => 'Christine Lim',      'email' => 'christine.lim@email.com',      'occ' => 'Educator',             'addr' => 'Bintaro, Tangerang Selatan'],
                ['name' => 'Marcus Williams',    'email' => 'marcus.williams@email.com',    'occ' => 'Financial Advisor',    'addr' => 'Sanur, Denpasar'],
                ['name' => 'Hana Jeon',          'email' => 'hana.jeon@email.com',          'occ' => 'Doctor',               'addr' => 'Ciputat, Tangerang Selatan'],
                ['name' => 'Ahmad Fauzi',        'email' => 'ahmad.fauzi@email.com',        'occ' => 'Business Owner',       'addr' => 'Kebayoran Baru, Jakarta Selatan'],
            ];

            $parentRole = Role::where('name', 'parent')
                ->where('guard_name', 'web')
                ->where('school_id', 0)
                ->firstOrFail();

            foreach ($parents as $data) {

                $user = User::create([
                    'name'              => $data['name'],
                    'email'             => $data['email'],
                    'password'          => Hash::make('password'),
                    'phone'             => '+62-813-' . rand(1000, 9999) . '-' . rand(1000, 9999),
                    'occupation'        => $data['occ'],
                    'address'           => $data['addr'],
                    'school_id'         => 0,
                    'is_active'         => true,
                    'email_verified_at' => now(),
                ]);

                $user->assignRole($parentRole);
            }

            $this->command->info('    ✓ 25 parent users created');

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
                    ['Parents',                 25],
                    ['Total Roles',             Role::count()],
                    ['Total Role Assignments',  DB::table('model_has_roles')->count()],
                ]
            );
            $this->command->newLine();
            $this->command->info('🔑 Login Credentials (password: "password")');
            $this->command->info('   Global Super Admin    : superadmin@vis.sch.id');
            $this->command->info('   VIS-BIN  (super_admin): sarah.johnson@vis-bin.sch.id');
            $this->command->info('   VIS-BIN  (school_admin): michael.chen@vis-bin.sch.id');
            $this->command->info('   VIS-BIN  (admission)  : lisa.wong@vis-bin.sch.id');
            $this->command->info('   VIS-BIN  (finance)    : robert.bintaro@vis-bin.sch.id');
            $this->command->info('   VIS-KG   (super_admin): david.kumar@vis-kg.sch.id');
            $this->command->info('   VIS-KG   (school_admin): emma.wilson@vis-kg.sch.id');
            $this->command->info('   VIS-KG   (admission)  : robert.lee@vis-kg.sch.id');
            $this->command->info('   VIS-KG   (finance)    : cynthia.park@vis-kg.sch.id');
            $this->command->info('   VIS-BALI (super_admin): amanda.martinez@vis-bali.sch.id');
            $this->command->info('   VIS-BALI (school_admin): james.taylor@vis-bali.sch.id');
            $this->command->info('   VIS-BALI (admission)  : michelle.tan@vis-bali.sch.id');
            $this->command->info('   VIS-BALI (finance)    : kevin.sanjaya@vis-bali.sch.id');
            $this->command->newLine();

        } catch (\Throwable $e) {

            DB::rollBack();

            $this->command->error("✗ Error: {$e->getMessage()}");
            $this->command->error("  File: {$e->getFile()}:{$e->getLine()}");

            throw $e;
        }
    }
}
