<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{School, AcademicYear, AdmissionPeriod};
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdmissionPeriodSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('📆 Creating Admission Periods...');
        
        $schools = School::all();
        $created = 0;
        
        DB::beginTransaction();
        
        try {
            foreach ($schools as $school) {
                $academicYear = AcademicYear::where('school_id', $school->id)
                    ->where('is_active', true)
                    ->first();
                
                if (!$academicYear) {
                    $this->command->warn("  ⚠ No active academic year for {$school->code}");
                    continue;
                }
                
                // Main Admission Period (Currently Open)
                AdmissionPeriod::create([
                    'school_id' => $school->id,
                    'academic_year_id' => $academicYear->id,
                    'name' => '2024-2025 Main Intake',
                    'start_date' => Carbon::parse('2025-02-01'),
                    'end_date' => Carbon::parse('2025-06-30'),
                    'decision_date' => Carbon::parse('2025-07-15'),
                    'enrollment_deadline' => Carbon::parse('2025-07-31'),
                    'is_active' => true,
                    'allow_applications' => true,
                    'description' => 'Main admission period for Academic Year 2024-2025. We welcome applications from families seeking world-class international education.',
                    'settings' => [
                        'max_applications' => 500,
                        'interview_required' => true,
                        'observation_required' => true,
                        'entrance_test_required' => false,
                        'early_decision_available' => true,
                        'rolling_admissions' => true,
                    ],
                ]);
                $created++;
                
                $this->command->info("  ✓ {$school->code}: Main admission period created");
            }
            
            DB::commit();
            
            // Summary
            $this->command->newLine();
            $this->command->info('════════════════════════════════════════');
            $this->command->info('✅ ADMISSION PERIODS SEEDING COMPLETE');
            $this->command->info('════════════════════════════════════════');
            $this->command->table(
                ['School', 'Period', 'Start', 'End', 'Status'],
                $schools->map(fn($s) => [
                    $s->code,
                    '2024-2025 Main',
                    '2025-02-01',
                    '2025-06-30',
                    '✓ OPEN'
                ])->toArray()
            );
            $this->command->info("Total Periods: {$created}");
            $this->command->newLine();
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("✗ Error: {$e->getMessage()}");
            throw $e;
        }
    }
}
