<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🌱 Starting PPDB Database Seeding...');
        $this->command->info('');

        // Run seeders in order
        $this->call([
            RolePermissionSeeder::class,
            SettingSeeder::class,
            AcademicYearSeeder::class,
            MajorSeeder::class,
            RegistrationPeriodSeeder::class,
            PaymentTypeSeeder::class,
            UserSeeder::class,
            SampleRegistrationSeeder::class,
        ]);

        $this->command->info('');
        $this->command->info('✅ Database seeding completed successfully!');
        $this->command->info('');
        $this->command->info('==============================================');
        $this->command->info('📊 SUMMARY');
        $this->command->info('==============================================');
        $this->command->info('Roles: ' . \Spatie\Permission\Models\Role::count());
        $this->command->info('Permissions: ' . \Spatie\Permission\Models\Permission::count());
        $this->command->info('Users: ' . \App\Models\User::count());
        $this->command->info('  - Super Admin: ' . \App\Models\User::role('super_admin')->count());
        $this->command->info('  - Admin Sekolah: ' . \App\Models\User::role('admin_sekolah')->count());
        $this->command->info('  - Panitia: ' . \App\Models\User::role('panitia')->count());
        $this->command->info('  - Calon Siswa: ' . \App\Models\User::role('calon_siswa')->count());
        $this->command->info('Academic Years: ' . \App\Models\AcademicYear::count());
        $this->command->info('Majors: ' . \App\Models\Major::count());
        $this->command->info('Registration Periods: ' . \App\Models\RegistrationPeriod::count());
        $this->command->info('Payment Types: ' . \App\Models\PaymentType::count());
        $this->command->info('Registrations: ' . \App\Models\Registration::count());
        $this->command->info('  - Draft: ' . \App\Models\Registration::where('status', 'draft')->count());
        $this->command->info('  - Submitted: ' . \App\Models\Registration::where('status', 'submitted')->count());
        $this->command->info('  - Verified: ' . \App\Models\Registration::where('status', 'verified')->count());
        $this->command->info('  - Passed: ' . \App\Models\Registration::where('status', 'passed')->count());
        $this->command->info('  - Re-registered: ' . \App\Models\Registration::where('status', 're_registered')->count());
        $this->command->info('Documents: ' . \App\Models\Document::count());
        $this->command->info('Payments: ' . \App\Models\Payment::count());
        $this->command->info('Scores: ' . \App\Models\Score::count());
        $this->command->info('Announcements: ' . \App\Models\Announcement::count());
        $this->command->info('Re-registrations: ' . \App\Models\ReRegistration::count());
        $this->command->info('==============================================');
        $this->command->info('');
        $this->command->info('🎉 Ready to use!');
        $this->command->info('');
    }
}
