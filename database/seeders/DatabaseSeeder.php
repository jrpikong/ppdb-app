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
            ['Academic Years',     '3',     '✓'],
            ['Levels',             '36',    '✓'],
            ['Admission Periods',  '3',     '✓'],
            ['Payment Types',      '18',    '✓'],
            ['Users',              '38',    '✓'],
            ['Applications',       '45',    '✓'],
            ['─────────────────',  '─────', '────────'],
            ['TOTAL RECORDS',      '~240+', '✓'],
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
        $this->command->info('┌─ 🏫 VIS BINTARO (VIS-BIN)  —  /school/s/VIS-BIN');
        $this->command->info('│  sarah.johnson@vis-bin.sch.id       → super_admin    (Principal / Full Access)');
        $this->command->info('│  michael.chen@vis-bin.sch.id        → school_admin   (Academic Director)');
        $this->command->info('│  lisa.wong@vis-bin.sch.id           → admission_admin (Head of Admissions)');
        $this->command->info('│  robert.bintaro@vis-bin.sch.id      → finance_admin  (Finance Manager)');
        $this->command->info('└────────────────────────────────────────────────────');
        $this->command->newLine();

        // ── VIS KELAPA GADING ───────────────────────────────────────────────
        $this->command->info('┌─ 🏫 VIS KELAPA GADING (VIS-KG)  —  /school/s/VIS-KG');
        $this->command->info('│  david.kumar@vis-kg.sch.id          → super_admin    (Principal / Full Access)');
        $this->command->info('│  emma.wilson@vis-kg.sch.id          → school_admin   (Academic Director)');
        $this->command->info('│  robert.lee@vis-kg.sch.id           → admission_admin (Head of Admissions)');
        $this->command->info('│  cynthia.park@vis-kg.sch.id         → finance_admin  (Finance Manager)');
        $this->command->info('└────────────────────────────────────────────────────');
        $this->command->newLine();

        // ── VIS BALI ────────────────────────────────────────────────────────
        $this->command->info('┌─ 🏫 VIS BALI (VIS-BALI)  —  /school/s/VIS-BALI');
        $this->command->info('│  amanda.martinez@vis-bali.sch.id    → super_admin    (Principal / Full Access)');
        $this->command->info('│  james.taylor@vis-bali.sch.id       → school_admin   (Academic Director)');
        $this->command->info('│  michelle.tan@vis-bali.sch.id       → admission_admin (Head of Admissions)');
        $this->command->info('│  kevin.sanjaya@vis-bali.sch.id      → finance_admin  (Finance Manager)');
        $this->command->info('└────────────────────────────────────────────────────');
        $this->command->newLine();

        // ── PARENTS ─────────────────────────────────────────────────────────
        $this->command->info('┌─ 👨‍👩‍👧 PARENTS (25 users)  —  Portal: /my');
        $this->command->info('│  Role   : parent (school_id = 0)');
        $this->command->info('│  Akses  : Portal /my (bukan panel admin)');
        $this->command->info('│');
        $this->command->info('│  william.thompson@email.com    jennifer.martinez@email.com');
        $this->command->info('│  alexander.brown@email.com     sophia.anderson@email.com');
        $this->command->info('│  benjamin.davis@email.com      olivia.wilson@email.com');
        $this->command->info('│  daniel.garcia@email.com       emma.rodriguez@email.com');
        $this->command->info('│  matthew.lee@email.com         isabella.kim@email.com');
        $this->command->info('│  jonathan.park@email.com       priya.sharma@email.com');
        $this->command->info('│  david.nguyen@email.com        sarah.chen@email.com');
        $this->command->info('│  ryan.johnson@email.com        meilin.zhang@email.com');
        $this->command->info('│  patrick.obrien@email.com      anita.krishnan@email.com');
        $this->command->info('│  thomas.mueller@email.com      yuki.tanaka@email.com');
        $this->command->info('│  robert.santos@email.com       christine.lim@email.com');
        $this->command->info('│  marcus.williams@email.com     hana.jeon@email.com');
        $this->command->info('│  ahmad.fauzi@email.com');
        $this->command->info('└────────────────────────────────────────────────────');
        $this->command->newLine();

        // ── ROLE SUMMARY TABLE ──────────────────────────────────────────────
        $this->command->info('📋 RINGKASAN ROLE & AKSES:');
        $this->command->table(
            ['Role', 'Users', 'Panel', 'Hak Akses Utama'],
            [
                ['super_admin (global)',  '1',  '/superadmin',      'Semua fitur + semua sekolah'],
                ['super_admin (per school)','3', '/school/s/{code}', 'Full akses dalam 1 tenant (Principal)'],
                ['school_admin',          '3',  '/school/s/{code}', 'Manajemen sekolah + user'],
                ['admission_admin',       '3',  '/school/s/{code}', 'Aplikasi + dokumen + jadwal'],
                ['finance_admin',         '3',  '/school/s/{code}', 'Pembayaran + laporan keuangan'],
                ['parent',                '25', '/my',              'Portal orang tua (aplikasi sendiri)'],
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

        $this->command->info('  3. Data sample aplikasi (45 total, 15 per sekolah):');
        $this->command->info('     Semua status tersedia: draft → enrolled + waitlisted + withdrawn');
        $this->command->newLine();

        $this->command->info('════════════════════════════════════════════════════════');
        $this->command->info('📚 Docs: PROJECT_SPECIFICATION_V2.md');
        $this->command->info('🐛 Semua seeder dilengkapi error handling & rollback');
        $this->command->info('════════════════════════════════════════════════════════');
        $this->command->newLine();
    }
}
