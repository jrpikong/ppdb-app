<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\AdmissionPeriod;
use App\Models\School;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdmissionPeriodSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Creating Admission Periods...');

        $schools = School::all();
        $created = 0;

        DB::beginTransaction();

        try {
            foreach ($schools as $school) {
                $academicYear = AcademicYear::query()
                    ->where('school_id', $school->id)
                    ->where('is_active', true)
                    ->first();

                if (! $academicYear) {
                    $this->command->warn("  - {$school->code}: active academic year not found, skipped.");
                    continue;
                }

                $startDate = Carbon::today()->subMonth();
                $endDate = Carbon::today()->addMonths(4);
                $decisionDate = $endDate->copy()->addWeeks(2);
                $enrollmentDeadline = $decisionDate->copy()->addWeeks(2);

                AdmissionPeriod::create([
                    'school_id' => $school->id,
                    'academic_year_id' => $academicYear->id,
                    'name' => "{$academicYear->name} Main Intake",
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'decision_date' => $decisionDate,
                    'enrollment_deadline' => $enrollmentDeadline,
                    'is_active' => true,
                    'allow_applications' => true,
                    'is_rolling' => false,
                    'description' => "Main admission period for {$academicYear->name}.",
                    'settings' => [
                        'max_applications' => 500,
                        'interview_required' => true,
                        'observation_required' => true,
                        'entrance_test_required' => true,
                        'early_decision_available' => true,
                    ],
                ]);

                $created++;
                $this->command->info("  - {$school->code}: {$academicYear->name} Main Intake");
            }

            DB::commit();
            $this->command->info("Created {$created} admission periods.");
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->command->error("Failed creating admission periods: {$e->getMessage()}");
            throw $e;
        }
    }
}
