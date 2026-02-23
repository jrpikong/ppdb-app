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
            | 1. GLOBAL SUPER ADMIN (TENANT = 0)
            |--------------------------------------------------------------------------
            | Akses ke /superadmin panel (no tenant). school_id = 0.
            |--------------------------------------------------------------------------
            */

            $this->command->info('  Creating Global Super Admin...');

            app(PermissionRegistrar::class)->setPermissionsTeamId(0);

            $superAdmin = User::create([
                'name'              => 'Dr. John Anderson',
                'email'             => 'superadmin@vis.sch.id',
                'password'          => Hash::make('password'),
                'phone'             => '+62-811-100-0001',
                'occupation'        => 'Group Director of Education',
                'address'           => 'VIS Group Head Office, Jakarta',
                'employee_id'       => 'VIS-GLOBAL-001',
                'department'        => 'Executive',
                'school_id'         => 0,
                'is_active'         => true,
                'email_verified_at' => now(),
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
            | VIS-BIN (active) : is_active = true, data lengkap dengan employee_id
            | VIS-KG & VIS-BALI (inactive) : is_active = false
            |--------------------------------------------------------------------------
            */

            $staffData = [
                /*
                 * VIS BINTARO — Active school
                 * Staff data: name, role, occupation, phone, employee_id, department
                 */
                'VIS-BIN' => [
                    [
                        'name'        => 'Sarah Johnson',
                        'role'        => 'super_admin',
                        'occupation'  => 'School Principal',
                        'phone'       => '+62-811-200-1001',
                        'employee_id' => 'VIS-BIN-001',
                        'department'  => 'Leadership & Management',
                    ],
                    [
                        'name'        => 'Michael Chen',
                        'role'        => 'school_admin',
                        'occupation'  => 'Academic Director',
                        'phone'       => '+62-811-200-1002',
                        'employee_id' => 'VIS-BIN-002',
                        'department'  => 'Academic Affairs',
                    ],
                    [
                        'name'        => 'Lisa Wong',
                        'role'        => 'admission_admin',
                        'occupation'  => 'Head of Admissions',
                        'phone'       => '+62-811-200-1003',
                        'employee_id' => 'VIS-BIN-003',
                        'department'  => 'Admissions & Enrollment',
                    ],
                    [
                        'name'        => 'Robert Bintaro',
                        'role'        => 'finance_admin',
                        'occupation'  => 'Finance Manager',
                        'phone'       => '+62-811-200-1004',
                        'employee_id' => 'VIS-BIN-004',
                        'department'  => 'Finance & Accounting',
                    ],
                ],

                /*
                 * VIS KELAPA GADING — Inactive school
                 * Staff created but is_active = false
                 */
                'VIS-KG' => [
                    ['name' => 'David Kumar',   'role' => 'super_admin',    'occupation' => 'School Principal',    'phone' => '+62-812-3001-0001', 'employee_id' => 'VIS-KG-001', 'department' => 'Leadership'],
                    ['name' => 'Emma Wilson',   'role' => 'school_admin',   'occupation' => 'Academic Director',   'phone' => '+62-812-3001-0002', 'employee_id' => 'VIS-KG-002', 'department' => 'Academic'],
                    ['name' => 'Robert Lee',    'role' => 'admission_admin','occupation' => 'Head of Admissions',  'phone' => '+62-812-3001-0003', 'employee_id' => 'VIS-KG-003', 'department' => 'Admissions'],
                    ['name' => 'Cynthia Park',  'role' => 'finance_admin',  'occupation' => 'Finance Manager',     'phone' => '+62-812-3001-0004', 'employee_id' => 'VIS-KG-004', 'department' => 'Finance'],
                ],

                /*
                 * VIS BALI — Inactive school
                 * Staff created but is_active = false
                 */
                'VIS-BALI' => [
                    ['name' => 'Amanda Martinez','role' => 'super_admin',    'occupation' => 'School Principal',   'phone' => '+62-812-4001-0001', 'employee_id' => 'VIS-BAL-001', 'department' => 'Leadership'],
                    ['name' => 'James Taylor',   'role' => 'school_admin',   'occupation' => 'Academic Director',  'phone' => '+62-812-4001-0002', 'employee_id' => 'VIS-BAL-002', 'department' => 'Academic'],
                    ['name' => 'Michelle Tan',   'role' => 'admission_admin','occupation' => 'Head of Admissions', 'phone' => '+62-812-4001-0003', 'employee_id' => 'VIS-BAL-003', 'department' => 'Admissions'],
                    ['name' => 'Kevin Sanjaya',  'role' => 'finance_admin',  'occupation' => 'Finance Manager',    'phone' => '+62-812-4001-0004', 'employee_id' => 'VIS-BAL-004', 'department' => 'Finance'],
                ],
            ];

            $this->command->info('  Creating School Staff...');

            foreach (School::all() as $school) {

                if (!isset($staffData[$school->code])) {
                    continue;
                }

                // ✅ CRITICAL: Set team context ke school_id ini
                app(PermissionRegistrar::class)->setPermissionsTeamId($school->id);

                // Staff is_active mirrors school is_active
                $staffActive = (bool) $school->is_active;
                $statusLabel = $staffActive ? '✓' : '⏸️ (inactive school)';

                $this->command->info("    {$school->name} (ID: {$school->id}) [is_active={$school->is_active}]:");

                foreach ($staffData[$school->code] as $staff) {

                    $emailSlug  = strtolower(str_replace([' ', "'"], ['.', ''], $staff['name']));
                    $schoolSlug = strtolower($school->code);
                    $email      = "{$emailSlug}@{$schoolSlug}.sch.id";

                    $user = User::create([
                        'name'              => $staff['name'],
                        'email'             => $email,
                        'password'          => Hash::make('password'),
                        'phone'             => $staff['phone'],
                        'occupation'        => $staff['occupation'],
                        'address'           => "Campus office {$school->name}, {$school->city}",
                        'employee_id'       => $staff['employee_id'],
                        'department'        => $staff['department'],
                        'school_id'         => $school->id,
                        'is_active'         => $staffActive,
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
                        }
                    }

                    $user->assignRole($role);

                    $this->command->info("      {$statusLabel} {$staff['name']} ({$staff['role']}) — {$email}");
                }
            }

            /*
            |--------------------------------------------------------------------------
            | 3. PARENTS (GLOBAL TENANT = 0)
            |--------------------------------------------------------------------------
            | 25 parent users — akses portal /my.
            | Fokus area Bintaro / Jakarta Selatan / Tangerang Selatan untuk trial.
            | Tetap international mix karena VIS adalah sekolah internasional.
            |--------------------------------------------------------------------------
            */

            $this->command->info('  Creating Parent Users (25 parents)...');

            app(PermissionRegistrar::class)->setPermissionsTeamId(0);

            $parents = [
                // Batch 1 — Bintaro / BSD / Pondok Indah (VIS Bintaro catchment area)
                ['name' => 'William Thompson',   'email' => 'william.thompson@email.com',   'occ' => 'Software Engineer',        'addr' => 'Jl. Bintaro Utama Blok A5, Bintaro Jaya, Tangerang Selatan'],
                ['name' => 'Jennifer Martinez',  'email' => 'jennifer.martinez@email.com',  'occ' => 'Doctor',                   'addr' => 'Jl. Taman Bintaro Sektor 7 No. 12, Tangerang Selatan'],
                ['name' => 'Alexander Brown',    'email' => 'alexander.brown@email.com',    'occ' => 'Business Owner',           'addr' => 'Jl. Pahlawan Seribu No. 45, BSD City, Tangerang'],
                ['name' => 'Sophia Anderson',    'email' => 'sophia.anderson@email.com',    'occ' => 'Marketing Director',       'addr' => 'Jl. Boulevard Bintaro Jaya No. 23, Tangerang Selatan'],
                ['name' => 'Benjamin Davis',     'email' => 'benjamin.davis@email.com',     'occ' => 'Financial Analyst',        'addr' => 'Jl. Pondok Indah Raya No. 88, Pondok Indah, Jakarta Selatan'],

                // Batch 2 — Kebayoran / Cilandak / TB Simatupang (Jakarta Selatan)
                ['name' => 'Olivia Wilson',      'email' => 'olivia.wilson@email.com',      'occ' => 'Entrepreneur',             'addr' => 'Jl. Kebayoran Lama No. 34, Jakarta Selatan'],
                ['name' => 'Daniel Garcia',      'email' => 'daniel.garcia@email.com',      'occ' => 'Corporate Lawyer',         'addr' => 'Jl. Cilandak KKO No. 15, Cilandak, Jakarta Selatan'],
                ['name' => 'Emma Rodriguez',     'email' => 'emma.rodriguez@email.com',     'occ' => 'Architect',                'addr' => 'Jl. TB Simatupang No. 7, Jakarta Selatan'],
                ['name' => 'Matthew Lee',        'email' => 'matthew.lee@email.com',        'occ' => 'Investment Manager',       'addr' => 'Jl. Fatmawati Raya No. 56, Cilandak, Jakarta Selatan'],
                ['name' => 'Isabella Kim',       'email' => 'isabella.kim@email.com',       'occ' => 'University Lecturer',      'addr' => 'Jl. Lebak Bulus No. 19, Lebak Bulus, Jakarta Selatan'],

                // Batch 3 — Alam Sutera / Serpong / Gading Serpong (West Tangerang)
                ['name' => 'Jonathan Park',      'email' => 'jonathan.park@email.com',      'occ' => 'Management Consultant',    'addr' => 'Jl. Alam Sutera Boulevard No. 33, Alam Sutera, Tangerang'],
                ['name' => 'Priya Sharma',       'email' => 'priya.sharma@email.com',       'occ' => 'Pediatrician',             'addr' => 'Jl. Gading Serpong Utama No. 5, Gading Serpong, Tangerang'],
                ['name' => 'David Nguyen',       'email' => 'david.nguyen@email.com',       'occ' => 'IT Project Manager',       'addr' => 'Jl. Alam Sutera Raya No. 88, Alam Sutera, Tangerang'],
                ['name' => 'Sarah Chen',         'email' => 'sarah.chen@email.com',         'occ' => 'Interior Designer',        'addr' => 'Jl. Graha Raya No. 12, Serpong, Tangerang Selatan'],
                ['name' => 'Ryan Johnson',       'email' => 'ryan.johnson@email.com',       'occ' => 'Business Development',     'addr' => 'Jl. Puspitek Raya No. 22, Serpong, Tangerang Selatan'],

                // Batch 4 — Expat & International Mix (Kemang / Menteng / Dharmawangsa)
                ['name' => 'Mei Lin Zhang',      'email' => 'meilin.zhang@email.com',       'occ' => 'Tech Startup Founder',     'addr' => 'Jl. Kemang Raya No. 99, Kemang, Jakarta Selatan'],
                ['name' => 'Patrick O\'Brien',   'email' => 'patrick.obrien@email.com',     'occ' => 'Regional Sales Director',  'addr' => 'Jl. Dharmawangsa No. 45, Kebayoran Baru, Jakarta Selatan'],
                ['name' => 'Anita Krishnan',     'email' => 'anita.krishnan@email.com',     'occ' => 'Chartered Accountant',     'addr' => 'Jl. Radio Dalam No. 28, Kebayoran Baru, Jakarta Selatan'],
                ['name' => 'Thomas Mueller',     'email' => 'thomas.mueller@email.com',     'occ' => 'Mechanical Engineer',      'addr' => 'Jl. Kemang Timur No. 14, Kemang, Jakarta Selatan'],
                ['name' => 'Yuki Tanaka',        'email' => 'yuki.tanaka@email.com',        'occ' => 'Regional Marketing Head',  'addr' => 'Jl. Menteng Raya No. 37, Menteng, Jakarta Pusat'],

                // Batch 5 — Cinere / Depok / Pamulang (South area near Bintaro)
                ['name' => 'Robert Santos',      'email' => 'robert.santos@email.com',      'occ' => 'Human Resource Director',  'addr' => 'Jl. Cinere Raya No. 55, Cinere, Depok'],
                ['name' => 'Christine Lim',      'email' => 'christine.lim@email.com',      'occ' => 'Secondary School Teacher', 'addr' => 'Jl. Pamulang Permai No. 8, Pamulang, Tangerang Selatan'],
                ['name' => 'Marcus Williams',    'email' => 'marcus.williams@email.com',    'occ' => 'Financial Advisor',        'addr' => 'Jl. Ciputat Raya No. 67, Ciputat, Tangerang Selatan'],
                ['name' => 'Hana Jeon',          'email' => 'hana.jeon@email.com',          'occ' => 'Dentist',                  'addr' => 'Jl. Pondok Cabe Raya No. 31, Pamulang, Tangerang Selatan'],
                ['name' => 'Ahmad Fauzi',        'email' => 'ahmad.fauzi@email.com',        'occ' => 'Business Owner',           'addr' => 'Jl. WR Supratman No. 18, Ciputat, Tangerang Selatan'],
            ];

            $parentRole = Role::where('name', 'parent')
                ->where('guard_name', 'web')
                ->where('school_id', 0)
                ->firstOrFail();

            $parentPhonePrefixes = ['0812', '0813', '0821', '0822', '0851', '0852', '0857', '0878', '0896', '0815'];
            $phoneIndex = 0;

            foreach ($parents as $data) {

                $phone = $parentPhonePrefixes[$phoneIndex % count($parentPhonePrefixes)] . str_pad((string)($phoneIndex * 1234 + 10000000), 8, '0');
                $phoneIndex++;

                $user = User::create([
                    'name'              => $data['name'],
                    'email'             => $data['email'],
                    'password'          => Hash::make('password'),
                    'phone'             => $phone,
                    'occupation'        => $data['occ'],
                    'address'           => $data['addr'],
                    'school_id'         => 0,
                    'is_active'         => true,
                    'email_verified_at' => now(),
                ]);

                $user->assignRole($parentRole);
            }

            $this->command->info('    ✓ 25 parent users created (Bintaro/Jakarta Selatan area focused)');

            DB::commit();

            // ==================== SUMMARY ====================

            $this->command->newLine();
            $this->command->info('════════════════════════════════════════');
            $this->command->info('✅ USERS SEEDING COMPLETE');
            $this->command->info('════════════════════════════════════════');
            $this->command->table(
                ['Category', 'Count', 'Status'],
                [
                    ['Total Users',                      User::count(),  '✓'],
                    ['Global Super Admin',               1,              '✓ Active'],
                    ['VIS-BIN Staff (super_admin)',       1,              '✓ Active'],
                    ['VIS-BIN Staff (school_admin)',      1,              '✓ Active'],
                    ['VIS-BIN Staff (admission_admin)',   1,              '✓ Active'],
                    ['VIS-BIN Staff (finance_admin)',     1,              '✓ Active'],
                    ['VIS-KG Staff (all roles)',          4,              '⏸️  Inactive'],
                    ['VIS-BALI Staff (all roles)',        4,              '⏸️  Inactive'],
                    ['Parents',                          25,             '✓ Active'],
                    ['Total Roles',                      Role::count(),  '✓'],
                ]
            );
            $this->command->newLine();
            $this->command->info('🔑 Login Credentials (password: "password")');
            $this->command->info('   Global Super Admin         : superadmin@vis.sch.id');
            $this->command->info('   VIS-BIN (super_admin)     : sarah.johnson@vis-bin.sch.id');
            $this->command->info('   VIS-BIN (school_admin)    : michael.chen@vis-bin.sch.id');
            $this->command->info('   VIS-BIN (admission_admin) : lisa.wong@vis-bin.sch.id');
            $this->command->info('   VIS-BIN (finance_admin)   : robert.bintaro@vis-bin.sch.id');
            $this->command->info('   Parent (sample)           : william.thompson@email.com');
            $this->command->newLine();

        } catch (\Throwable $e) {

            DB::rollBack();

            $this->command->error("✗ Error: {$e->getMessage()}");
            $this->command->error("  File: {$e->getFile()}:{$e->getLine()}");

            throw $e;
        }
    }
}
