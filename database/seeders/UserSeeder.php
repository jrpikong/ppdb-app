<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{User, School};
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('👥 Creating Users...');
        
        DB::beginTransaction();
        
        try {
            // 1. SUPER ADMIN (Global)
            $this->command->info('  Creating Super Admin...');
            $superAdmin = User::create([
                'name' => 'Dr. John Anderson',
                'email' => 'superadmin@vis.sch.id',
                'password' => Hash::make('password'),
                'phone' => '+62-812-3456-7890',
                'school_id' => null, // Global access
                'is_active' => true,
                'email_verified_at' => now(),
            ]);
            $superAdmin->assignRole('super_admin');
            $this->command->info('    ✓ Dr. John Anderson (Super Admin)');
            
            // 2. SCHOOL STAFF
            $schools = School::all();
            $staffData = [
                'VIS-BIN' => [
                    ['name' => 'Sarah Johnson', 'role' => 'school_admin', 'title' => 'School Admin'],
                    ['name' => 'Michael Chen', 'role' => 'admission_admin', 'title' => 'Admission Admin'],
                    ['name' => 'Lisa Wong', 'role' => 'finance_admin', 'title' => 'Finance Admin'],
                ],
                'VIS-KG' => [
                    ['name' => 'David Kumar', 'role' => 'school_admin', 'title' => 'School Admin'],
                    ['name' => 'Emma Wilson', 'role' => 'admission_admin', 'title' => 'Admission Admin'],
                    ['name' => 'Robert Lee', 'role' => 'finance_admin', 'title' => 'Finance Admin'],
                ],
                'VIS-BALI' => [
                    ['name' => 'Amanda Martinez', 'role' => 'school_admin', 'title' => 'School Admin'],
                    ['name' => 'James Taylor', 'role' => 'admission_admin', 'title' => 'Admission Admin'],
                    ['name' => 'Michelle Tan', 'role' => 'finance_admin', 'title' => 'Finance Admin'],
                ],
            ];
            
            $this->command->info('  Creating School Staff...');
            foreach ($schools as $school) {
                if (!isset($staffData[$school->code])) continue;
                
                $this->command->info("    {$school->name}:");
                foreach ($staffData[$school->code] as $staff) {
                    $email = strtolower(str_replace(' ', '.', $staff['name'])) . '@' . strtolower($school->code) . '.sch.id';
                    
                    $user = User::create([
                        'name' => $staff['name'],
                        'email' => $email,
                        'password' => Hash::make('password'),
                        'phone' => '+62-812-' . rand(1000, 9999) . '-' . rand(1000, 9999),
                        'school_id' => $school->id,
                        'is_active' => true,
                        'email_verified_at' => now(),
                    ]);
                    $user->assignRole($staff['role']);
                    
                    $this->command->info("      ✓ {$staff['name']} ({$staff['title']})");
                }
            }
            
            // 3. PARENT USERS
            $this->command->info('  Creating Parent Users...');
            $parents = [
                ['name' => 'William Thompson', 'email' => 'william.thompson@email.com', 'phone' => '+62-813-1111-1001'],
                ['name' => 'Jennifer Martinez', 'email' => 'jennifer.martinez@email.com', 'phone' => '+62-813-1111-1002'],
                ['name' => 'Alexander Brown', 'email' => 'alexander.brown@email.com', 'phone' => '+62-813-1111-1003'],
                ['name' => 'Sophia Anderson', 'email' => 'sophia.anderson@email.com', 'phone' => '+62-813-1111-1004'],
                ['name' => 'Benjamin Davis', 'email' => 'benjamin.davis@email.com', 'phone' => '+62-813-1111-1005'],
                ['name' => 'Olivia Wilson', 'email' => 'olivia.wilson@email.com', 'phone' => '+62-813-1111-1006'],
                ['name' => 'Daniel Garcia', 'email' => 'daniel.garcia@email.com', 'phone' => '+62-813-1111-1007'],
                ['name' => 'Emma Rodriguez', 'email' => 'emma.rodriguez@email.com', 'phone' => '+62-813-1111-1008'],
                ['name' => 'Matthew Lee', 'email' => 'matthew.lee@email.com', 'phone' => '+62-813-1111-1009'],
                ['name' => 'Isabella Kim', 'email' => 'isabella.kim@email.com', 'phone' => '+62-813-1111-1010'],
            ];
            
            foreach ($parents as $parent) {
                $user = User::create([
                    'name' => $parent['name'],
                    'email' => $parent['email'],
                    'password' => Hash::make('password'),
                    'phone' => $parent['phone'],
                    'school_id' => null, // Will be set via application
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]);
                $user->assignRole('parent');
            }
            $this->command->info("    ✓ 10 parent users created");
            
            DB::commit();
            
            // Summary
            $this->command->newLine();
            $this->command->info('════════════════════════════════════════');
            $this->command->info('✅ USERS SEEDING COMPLETE');
            $this->command->info('════════════════════════════════════════');
            $this->command->table(
                ['Role', 'Count', 'Access'],
                [
                    ['Super Admin', '1', 'Global (All Schools)'],
                    ['School Admins', '3', 'Per School (Full Access)'],
                    ['Admission Admins', '3', 'Per School (Applications)'],
                    ['Finance Admins', '3', 'Per School (Payments)'],
                    ['Parents', '10', 'Own Applications Only'],
                    ['TOTAL', '20', '-'],
                ]
            );
            $this->command->newLine();
            $this->command->info('📝 LOGIN CREDENTIALS:');
            $this->command->info('  Email: [user-email]');
            $this->command->info('  Password: password');
            $this->command->newLine();
            $this->command->info('🔐 Super Admin Login:');
            $this->command->info('  Email: superadmin@vis.sch.id');
            $this->command->info('  Password: password');
            $this->command->newLine();
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("✗ Error: {$e->getMessage()}");
            throw $e;
        }
    }
}
