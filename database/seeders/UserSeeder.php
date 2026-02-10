<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Super Admin
        $superAdmin = User::create([
            'name' => 'Super Administrator',
            'email' => 'superadmin@mtsn1wonogiri.sch.id',
            'password' => Hash::make('password'),
            'phone' => '081234567890',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $superAdmin->assignRole('super_admin');

        // 2. Admin Sekolah
        $adminSekolah = User::create([
            'name' => 'Admin Sekolah',
            'email' => 'admin@mtsn1wonogiri.sch.id',
            'password' => Hash::make('password'),
            'phone' => '081234567891',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $adminSekolah->assignRole('admin_sekolah');

        // 3. Panitia PPDB
        $panitia1 = User::create([
            'name' => 'Mulyono, S.Pd',
            'email' => 'mulyono@mtsn1wonogiri.sch.id',
            'password' => Hash::make('password'),
            'phone' => '081234567892',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $panitia1->assignRole('panitia');

        $panitia2 = User::create([
            'name' => 'Sri Wahyuni, S.Pd.I',
            'email' => 'sriwahyuni@mtsn1wonogiri.sch.id',
            'password' => Hash::make('password'),
            'phone' => '081234567893',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $panitia2->assignRole('panitia');

        // 4. Sample Students (Calon Siswa)
        $students = [
            [
                'name' => 'Ahmad Fauzi',
                'email' => 'ahmad.fauzi@student.com',
                'phone' => '085267964065',
            ],
            [
                'name' => 'Siti Nurhaliza',
                'email' => 'siti.nurhaliza@student.com',
                'phone' => '085267964066',
            ],
            [
                'name' => 'Muhammad Rizki',
                'email' => 'muhammad.rizki@student.com',
                'phone' => '085267964067',
            ],
            [
                'name' => 'Fatimah Azzahra',
                'email' => 'fatimah.azzahra@student.com',
                'phone' => '085267964068',
            ],
            [
                'name' => 'Abdullah Hakim',
                'email' => 'abdullah.hakim@student.com',
                'phone' => '085267964069',
            ],
            [
                'name' => 'Khadijah Aisha',
                'email' => 'khadijah.aisha@student.com',
                'phone' => '085267964070',
            ],
            [
                'name' => 'Umar Hasan',
                'email' => 'umar.hasan@student.com',
                'phone' => '085267964071',
            ],
            [
                'name' => 'Aisyah Putri',
                'email' => 'aisyah.putri@student.com',
                'phone' => '085267964072',
            ],
            [
                'name' => 'Ibrahim Khalil',
                'email' => 'ibrahim.khalil@student.com',
                'phone' => '085267964073',
            ],
            [
                'name' => 'Maryam Safira',
                'email' => 'maryam.safira@student.com',
                'phone' => '085267964074',
            ],
        ];

        foreach ($students as $studentData) {
            $student = User::create([
                'name' => $studentData['name'],
                'email' => $studentData['email'],
                'password' => Hash::make('password'),
                'phone' => $studentData['phone'],
                'is_active' => true,
                'email_verified_at' => now(),
            ]);
            $student->assignRole('calon_siswa');
        }

        $this->command->info('Users created successfully!');
        $this->command->info('');
        $this->command->info('=== LOGIN CREDENTIALS ===');
        $this->command->info('Super Admin:');
        $this->command->info('  Email: superadmin@mtsn1wonogiri.sch.id');
        $this->command->info('  Password: password');
        $this->command->info('');
        $this->command->info('Admin Sekolah:');
        $this->command->info('  Email: admin@mtsn1wonogiri.sch.id');
        $this->command->info('  Password: password');
        $this->command->info('');
        $this->command->info('Panitia:');
        $this->command->info('  Email: mulyono@mtsn1wonogiri.sch.id');
        $this->command->info('  Password: password');
        $this->command->info('');
        $this->command->info('Students (10 users):');
        $this->command->info('  Email: ahmad.fauzi@student.com (and others)');
        $this->command->info('  Password: password');
        $this->command->info('');
    }
}
