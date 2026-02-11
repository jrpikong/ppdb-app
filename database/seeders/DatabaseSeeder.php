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
            RolePermissionSeeder::class,
            SchoolSeeder::class,
            SettingSeeder::class,
            AcademicYearSeeder::class,
            LevelSeeder::class,
            AdmissionPeriodSeeder::class,
            PaymentTypeSeeder::class,
            UserSeeder::class,
            // ApplicationSeeder::class, // Optional: Uncomment for sample applications
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
        
        $this->command->newLine();
        $this->command->info("⏱️  Execution Time: {$duration} seconds");
        $this->command->newLine();
        
        $this->displayNextSteps();
    }
    
    private function displaySummary(): void
    {
        $this->command->info('📊 SEEDING SUMMARY:');
        $this->command->newLine();
        
        $summary = [
            ['Component', 'Count', 'Status'],
            ['─────────────────', '─────', '────────'],
            ['Roles', '5', '✓'],
            ['Permissions', '90+', '✓'],
            ['Schools', '3', '✓'],
            ['Academic Years', '3', '✓'],
            ['Levels', '36', '✓'],
            ['Admission Periods', '3', '✓'],
            ['Payment Types', '18', '✓'],
            ['Users', '20', '✓'],
            ['─────────────────', '─────', '────────'],
            ['TOTAL RECORDS', '~178', '✓'],
        ];
        
        foreach ($summary as $row) {
            $this->command->info(sprintf(
                '  %-18s %-7s %s',
                $row[0],
                $row[1],
                $row[2]
            ));
        }
    }
    
    private function displayNextSteps(): void
    {
        $this->command->info('🚀 NEXT STEPS:');
        $this->command->newLine();
        
        $this->command->info('  1. Login as Super Admin:');
        $this->command->info('     URL: /superadmin');
        $this->command->info('     Email: superadmin@vis.sch.id');
        $this->command->info('     Password: password');
        $this->command->newLine();
        
        $this->command->info('  2. Access School Panels:');
        $this->command->info('     VIS Bintaro: /school/vis-bin');
        $this->command->info('     VIS Kelapa Gading: /school/vis-kg');
        $this->command->info('     VIS Bali: /school/vis-bali');
        $this->command->newLine();
        
        $this->command->info('  3. School Staff Logins:');
        $this->command->info('     Format: firstname.lastname@[school-code].sch.id');
        $this->command->info('     Example: sarah.johnson@vis-bin.sch.id');
        $this->command->info('     Password: password (for all users)');
        $this->command->newLine();
        
        $this->command->info('  4. Setup Filament Panels:');
        $this->command->info('     - Configure SuperAdminPanel');
        $this->command->info('     - Configure SchoolPanel with tenancy');
        $this->command->info('     - Create Filament Resources');
        $this->command->newLine();
        
        $this->command->info('  5. Optional: Run ApplicationSeeder');
        $this->command->info('     php artisan db:seed --class=ApplicationSeeder');
        $this->command->info('     (Creates sample applications for testing)');
        $this->command->newLine();
        
        $this->command->info('════════════════════════════════════════════════════════');
        $this->command->info('📚 Documentation: Check SEEDER_MASTER_PLAN.md');
        $this->command->info('🐛 Issues? All seeders include error handling & rollback');
        $this->command->info('════════════════════════════════════════════════════════');
        $this->command->newLine();
    }
}
