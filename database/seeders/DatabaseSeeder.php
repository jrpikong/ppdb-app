<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Executes seeders in the correct order for VIS Bintaro admission system.
     * Primary school: VIS-BIN (active). VIS-KG & VIS-BALI seeded as inactive reference data.
     */
    public function run(): void
    {
        $this->command->newLine();
        $this->command->info('╔═════════════════════════════════════════════════════════╗');
        $this->command->info('║  VIS BINTARO ADMISSION SYSTEM - DATABASE SEEDING        ║');
        $this->command->info('║  Primary School: VIS Bintaro (Active)                   ║');
        $this->command->info('╚═════════════════════════════════════════════════════════╝');
        $this->command->newLine();

        $startTime = microtime(true);

        $seeders = [
            RolePermissionSeeder::class,  // 1. Roles & permissions (global templates)
            SchoolSeeder::class,           // 2. Schools (VIS-BIN active, KG & BALI inactive)
            SettingSeeder::class,          // 3. System settings (VIS Bintaro config)
            AcademicYearSeeder::class,     // 4. Academic years per school
            LevelSeeder::class,            // 5. Education levels per school
            AdmissionPeriodSeeder::class,  // 6. Admission periods per school
            PaymentTypeSeeder::class,      // 7. Payment types per school
            UserSeeder::class,             // 8. Users with roles
            ApplicationSeeder::class,      // 9. Sample applications (VIS-BIN only)
        ];

        foreach ($seeders as $index => $seeder) {
            $seederName = class_basename($seeder);
            $this->command->info("▶ Running: {$seederName} [" . ($index + 1) . "/" . count($seeders) . "]");
            $this->call($seeder);
        }

        $endTime  = microtime(true);
        $duration = round($endTime - $startTime, 2);

        $this->command->newLine();
        $this->command->info('══════════════════════════════════════════════════════════');
        $this->command->info('✅ DATABASE SEEDING COMPLETED SUCCESSFULLY!');
        $this->command->info('══════════════════════════════════════════════════════════');
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
            ['Component',               'VIS-BIN (Active)',  'KG & BALI (Inactive)'],
            ['────────────────────────','─────────────────', '─────────────────────'],
            ['Academic Years',           '1 (active)',        '2 (inactive)'],
            ['Education Levels',         '12 (active)',       '24 (inactive)'],
            ['Admission Periods',        '1 (open)',          '2 (closed)'],
            ['Payment Types',            '6 (active)',        '12 (inactive)'],
            ['Staff Users',              '4 (active)',        '8 (inactive)'],
            ['Sample Applications',      '15',                '0'],
            ['────────────────────────','─────────────────', '─────────────────────'],
            ['Roles',                    '5 global + tenant roles', ''],
            ['Permissions',              '90+',               ''],
            ['Parent Users',             '25 (active)',       ''],
            ['Global Super Admin',       '1',                 ''],
        ];

        foreach ($summary as $row) {
            $this->command->info(sprintf(
                '  %-26s %-22s %s',
                $row[0],
                $row[1],
                $row[2] ?? ''
            ));
        }

        $this->command->newLine();
    }

    private function displayCredentials(): void
    {
        $this->command->info('══════════════════════════════════════════════════════════');
        $this->command->info('🔑 LOGIN CREDENTIALS (Password semua: "password")');
        $this->command->info('══════════════════════════════════════════════════════════');
        $this->command->newLine();

        // ── VIS BINTARO — PRIMARY ACTIVE SCHOOL ─────────────────────────────
        $this->command->info('┌─ 🏫 VIS BINTARO — ACTIVE  (/school/s/VIS-BIN)');
        $this->command->info('│');
        $this->command->info('│  sarah.johnson@vis-bin.sch.id       → super_admin    (Principal / Full Access)');
        $this->command->info('│  michael.chen@vis-bin.sch.id        → school_admin   (Academic Director)');
        $this->command->info('│  lisa.wong@vis-bin.sch.id           → admission_admin (Head of Admissions)');
        $this->command->info('│  robert.bintaro@vis-bin.sch.id      → finance_admin  (Finance Manager)');
        $this->command->info('└─────────────────────────────────────────────────────────');
        $this->command->newLine();

        // ── GLOBAL SUPER ADMIN ──────────────────────────────────────────────
        $this->command->info('┌─ 🌐 GLOBAL SUPER ADMIN  (/superadmin)');
        $this->command->info('│  superadmin@vis.sch.id  → Group Director (all-school access)');
        $this->command->info('└─────────────────────────────────────────────────────────');
        $this->command->newLine();

        // ── PARENTS ─────────────────────────────────────────────────────────
        $this->command->info('┌─ 👨‍👩‍👧 PARENTS (25 users)  —  Portal: /my');
        $this->command->info('│  Role   : parent  |  Area: Bintaro / Jakarta Selatan / Tangerang Selatan');
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
        $this->command->info('└─────────────────────────────────────────────────────────');
        $this->command->newLine();

        // ── INACTIVE SCHOOLS (reference only) ──────────────────────────────
        $this->command->info('┌─ ⏸️  INACTIVE SCHOOLS (staff cannot login — is_active = false)');
        $this->command->info('│  VIS-KG   : david.kumar@vis-kg.sch.id (and 3 others)');
        $this->command->info('│  VIS-BALI : amanda.martinez@vis-bali.sch.id (and 3 others)');
        $this->command->info('└─────────────────────────────────────────────────────────');
        $this->command->newLine();

        $this->command->info('📋 RINGKASAN ROLE & AKSES (VIS BINTARO):');
        $this->command->table(
            ['Role', 'User', 'Panel', 'Hak Akses Utama'],
            [
                ['super_admin (global)', 'superadmin@vis.sch.id', '/superadmin', 'Semua fitur sistem'],
                ['super_admin (BIN)',    'sarah.johnson@vis-bin.sch.id', '/school/s/VIS-BIN', 'Full akses (Principal)'],
                ['school_admin',        'michael.chen@vis-bin.sch.id', '/school/s/VIS-BIN', 'Manajemen akademik'],
                ['admission_admin',     'lisa.wong@vis-bin.sch.id', '/school/s/VIS-BIN', 'Aplikasi + dokumen + jadwal'],
                ['finance_admin',       'robert.bintaro@vis-bin.sch.id', '/school/s/VIS-BIN', 'Pembayaran + laporan'],
                ['parent',             'william.thompson@email.com (+ 24)', '/my', 'Portal orang tua'],
            ]
        );
        $this->command->newLine();
    }

    private function displayNextSteps(): void
    {
        $this->command->info('🚀 NEXT STEPS FOR VIS BINTARO TRIAL:');
        $this->command->newLine();

        $this->command->info('  1. Login sebagai Principal (full access):');
        $this->command->info('     URL  : /school/s/VIS-BIN');
        $this->command->info('     Email: sarah.johnson@vis-bin.sch.id / password');
        $this->command->newLine();

        $this->command->info('  2. Login sebagai Parent (portal pendaftaran):');
        $this->command->info('     URL  : /my');
        $this->command->info('     Email: william.thompson@email.com / password');
        $this->command->newLine();

        $this->command->info('  3. Data sample aplikasi (15 aplikasi — semua status):');
        $this->command->info('     draft, submitted, under_review, documents_verified,');
        $this->command->info('     interview_scheduled, interview_completed, payment_pending,');
        $this->command->info('     payment_verified, accepted, enrolled, rejected, waitlisted, withdrawn');
        $this->command->newLine();

        $this->command->info('══════════════════════════════════════════════════════════');
        $this->command->info('📚 Panduan: CLIENT_GUIDE_V2.md');
        $this->command->info('🏫 Primary School: VIS Bintaro (Tangerang Selatan)');
        $this->command->info('══════════════════════════════════════════════════════════');
        $this->command->newLine();
    }
}
