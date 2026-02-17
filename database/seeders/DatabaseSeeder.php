<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Executes seeders in the correct order for VIS multi-tenant admission system
     */
    public function run(): void
    {
        $this->command->newLine();
        $this->command->info('╔════════════════════════════════════════════════════════╗');
        $this->command->info('║  VIS ADMISSION SYSTEM - DATABASE SEEDING              ║');
        $this->command->info('║  Multi-Tenancy with Filament                          ║');
        $this->command->info('╚════════════════════════════════════════════════════════╝');
        $this->command->newLine();

        $startTime = microtime(true);

        // Execute seeders in dependency order
        $seeders = [
            RolePermissionSeeder::class,  // 1. Roles & permissions (global templates)
            SchoolSeeder::class,           // 2. Schools (tenants)
            SettingSeeder::class,          // 3. Default settings per school
            AcademicYearSeeder::class,     // 4. Academic years per school
            LevelSeeder::class,            // 5. Education levels per school
            AdmissionPeriodSeeder::class,  // 6. Admission periods per school
            PaymentTypeSeeder::class,      // 7. Payment types per school
            UserSeeder::class,             // 8. Users with roles (depends on schools)
            ApplicationSeeder::class,      // 9. Sample applications (optional)
        ];

        foreach ($seeders as $index => $seeder) {
            $seederName = class_basename($seeder);
            $this->command->info("▶ Running: {$seederName} [" . ($index + 1) . "/" . count($seeders) . "]");
            $this->call($seeder);
        }

        $endTime = microtime(true);
        $duration = round($endTime - $startTime, 2);

        // Final Summary
        $this->command->newLine();
        $this->command->info('════════════════════════════════════════════════════════');
        $this->command->info('✅ DATABASE SEEDING COMPLETED SUCCESSFULLY!');
        $this->command->info('════════════════════════════════════════════════════════');
        $this->command->newLine();

        $this->displaySummary();
        $this->displayCredentials();
        $this->displayNextSteps();

        $this->command->newLine();
        $this->command->info("⏱️  Execution Time: {$duration} seconds");
        $this->command->newLine();
    }

    private function displaySummary(): void
    {
        $this->command->info('📊 SEEDING SUMMARY:');
        $this->command->newLine();

        $summary = [
            ['Component',          'Count', 'Status'],
            ['─────────────────',  '─────', '────────'],
            ['Roles',              '5 global + 15 tenant', '✓'],
            ['Permissions',        '90+',   '✓'],
            ['Schools',            '3',     '✓'],
            ['Academic Years',     '9',     '✓'],
            ['Levels',             '36',    '✓'],
            ['Admission Periods',  '3',     '✓'],
            ['Payment Types',      '18',    '✓'],
            ['Users',              '23',    '✓'],
            ['─────────────────',  '─────', '────────'],
            ['TOTAL RECORDS',      '~183+', '✓'],
        ];

        foreach ($summary as $row) {
            $this->command->info(sprintf(
                '  %-18s %-24s %s',
                $row[0],
                $row[1],
                $row[2]
            ));
        }

        $this->command->newLine();
    }

    private function displayCredentials(): void
    {
        $this->command->info('════════════════════════════════════════════════════════');
        $this->command->info('🔑 LOGIN CREDENTIALS (Password semua: "password")');
        $this->command->info('════════════════════════════════════════════════════════');
        $this->command->newLine();

        // ── GLOBAL SUPER ADMIN ──────────────────────────────────────────────
        $this->command->info('┌─ 🌐 GLOBAL SUPER ADMIN');
        $this->command->info('│  Panel  : /superadmin');
        $this->command->info('│  Akses  : Semua sekolah, semua fitur sistem');
        $this->command->info('│');
        $this->command->info('│  Email  : superadmin@vis.sch.id');
        $this->command->info('│  Role   : super_admin (school_id = 0)');
        $this->command->info('└────────────────────────────────────────────────────');
        $this->command->newLine();

        // ── VIS BINTARO ─────────────────────────────────────────────────────
        $this->command->info('┌─ 🏫 VIS BINTARO (VIS-BIN)');
        $this->command->info('│  Panel  : /school/s/VIS-BIN');
        $this->command->info('│');
        $this->command->info('│  Email  : sarah.johnson@vis-bin.sch.id');
        $this->command->info('│  Role   : super_admin');
        $this->command->info('│  Akses  : Full access dalam tenant VIS-BIN');
        $this->command->info('│');
        $this->command->info('│  Email  : michael.chen@vis-bin.sch.id');
        $this->command->info('│  Role   : school_admin');
        $this->command->info('│  Akses  : Manajemen sekolah, user, laporan');
        $this->command->info('│');
        $this->command->info('│  Email  : lisa.wong@vis-bin.sch.id');
        $this->command->info('│  Role   : admission_admin');
        $this->command->info('│  Akses  : Aplikasi, dokumen, jadwal wawancara');
        $this->command->info('│');
        $this->command->info('│  Email  : robert.bintaro@vis-bin.sch.id');
        $this->command->info('│  Role   : finance_admin');
        $this->command->info('│  Akses  : Pembayaran, verifikasi, laporan keuangan');
        $this->command->info('└────────────────────────────────────────────────────');
        $this->command->newLine();

        // ── VIS KELAPA GADING ───────────────────────────────────────────────
        $this->command->info('┌─ 🏫 VIS KELAPA GADING (VIS-KG)');
        $this->command->info('│  Panel  : /school/s/VIS-KG');
        $this->command->info('│');
        $this->command->info('│  Email  : david.kumar@vis-kg.sch.id');
        $this->command->info('│  Role   : super_admin');
        $this->command->info('│  Akses  : Full access dalam tenant VIS-KG');
        $this->command->info('│');
        $this->command->info('│  Email  : emma.wilson@vis-kg.sch.id');
        $this->command->info('│  Role   : school_admin');
        $this->command->info('│  Akses  : Manajemen sekolah, user, laporan');
        $this->command->info('│');
        $this->command->info('│  Email  : robert.lee@vis-kg.sch.id');
        $this->command->info('│  Role   : admission_admin');
        $this->command->info('│  Akses  : Aplikasi, dokumen, jadwal wawancara');
        $this->command->info('│');
        $this->command->info('│  Email  : cynthia.park@vis-kg.sch.id');
        $this->command->info('│  Role   : finance_admin');
        $this->command->info('│  Akses  : Pembayaran, verifikasi, laporan keuangan');
        $this->command->info('└────────────────────────────────────────────────────');
        $this->command->newLine();

        // ── VIS BALI ────────────────────────────────────────────────────────
        $this->command->info('┌─ 🏫 VIS BALI (VIS-BALI)');
        $this->command->info('│  Panel  : /school/s/VIS-BALI');
        $this->command->info('│');
        $this->command->info('│  Email  : amanda.martinez@vis-bali.sch.id');
        $this->command->info('│  Role   : super_admin');
        $this->command->info('│  Akses  : Full access dalam tenant VIS-BALI');
        $this->command->info('│');
        $this->command->info('│  Email  : james.taylor@vis-bali.sch.id');
        $this->command->info('│  Role   : school_admin');
        $this->command->info('│  Akses  : Manajemen sekolah, user, laporan');
        $this->command->info('│');
        $this->command->info('│  Email  : michelle.tan@vis-bali.sch.id');
        $this->command->info('│  Role   : admission_admin');
        $this->command->info('│  Akses  : Aplikasi, dokumen, jadwal wawancara');
        $this->command->info('│');
        $this->command->info('│  Email  : kevin.sanjaya@vis-bali.sch.id');
        $this->command->info('│  Role   : finance_admin');
        $this->command->info('│  Akses  : Pembayaran, verifikasi, laporan keuangan');
        $this->command->info('└────────────────────────────────────────────────────');
        $this->command->newLine();

        // ── PARENTS ─────────────────────────────────────────────────────────
        $this->command->info('┌─ 👨‍👩‍👧 PARENTS (Tidak ada panel admin)');
        $this->command->info('│  Akses  : Public registration form saja');
        $this->command->info('│  Role   : parent (school_id = 0)');
        $this->command->info('│');
        $this->command->info('│  william.thompson@email.com');
        $this->command->info('│  jennifer.martinez@email.com');
        $this->command->info('│  alexander.brown@email.com');
        $this->command->info('│  sophia.anderson@email.com');
        $this->command->info('│  benjamin.davis@email.com');
        $this->command->info('│  olivia.wilson@email.com');
        $this->command->info('│  daniel.garcia@email.com');
        $this->command->info('│  emma.rodriguez@email.com');
        $this->command->info('│  matthew.lee@email.com');
        $this->command->info('│  isabella.kim@email.com');
        $this->command->info('└────────────────────────────────────────────────────');
        $this->command->newLine();

        // ── ROLE SUMMARY TABLE ──────────────────────────────────────────────
        $this->command->info('📋 RINGKASAN ROLE & AKSES:');
        $this->command->table(
            ['Role', 'Scope', 'Panel', 'Hak Akses Utama'],
            [
                ['super_admin',    'Global (school_id=0)',  '/superadmin',     'Semua fitur + semua sekolah'],
                ['super_admin',    'Per Sekolah',           '/school/s/{code}','Full akses dalam 1 tenant'],
                ['school_admin',   'Per Sekolah',           '/school/s/{code}','Manajemen sekolah + user'],
                ['admission_admin','Per Sekolah',           '/school/s/{code}','Aplikasi + dokumen + jadwal'],
                ['finance_admin',  'Per Sekolah',           '/school/s/{code}','Pembayaran + laporan keuangan'],
                ['parent',         'Global (school_id=0)',  'Tidak ada panel', 'Form pendaftaran publik saja'],
            ]
        );
        $this->command->newLine();
    }

    private function displayNextSteps(): void
    {
        $this->command->info('🚀 NEXT STEPS:');
        $this->command->newLine();

        $this->command->info('  1. Login Global Super Admin → /superadmin');
        $this->command->info('     superadmin@vis.sch.id / password');
        $this->command->newLine();

        $this->command->info('  2. Login Per-School → /school/s/{code}');
        $this->command->info('     Gunakan email staff sekolah yang sesuai');
        $this->command->info('     Contoh: sarah.johnson@vis-bin.sch.id / password');
        $this->command->newLine();

        $this->command->info('  3. Jalankan ApplicationSeeder untuk data sample:');
        $this->command->info('     php artisan db:seed --class=ApplicationSeeder');
        $this->command->newLine();

        $this->command->info('════════════════════════════════════════════════════════');
        $this->command->info('📚 Docs: PROJECT_SPECIFICATION_V2.md');
        $this->command->info('🐛 Semua seeder dilengkapi error handling & rollback');
        $this->command->info('════════════════════════════════════════════════════════');
        $this->command->newLine();
    }
}
