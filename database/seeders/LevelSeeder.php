<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{School, AcademicYear, Level};
use Illuminate\Support\Facades\DB;

class LevelSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🎓 Creating Education Levels...');

        $schools = School::all();
        $created = 0;

        // Define level structure for International School
        $levelsData = [
            // Early Years Program
            ['code' => 'EP', 'name' => 'Early Preschool', 'category' => 'early_years', 'age_min' => 1.6, 'age_max' => 2.6, 'quota' => 20, 'tuition' => 45000000],
            ['code' => 'PS', 'name' => 'Preschool', 'category' => 'early_years', 'age_min' => 2.6, 'age_max' => 4.0, 'quota' => 25, 'tuition' => 48000000],
            ['code' => 'PK', 'name' => 'Pre-Kindy', 'category' => 'early_years', 'age_min' => 4.0, 'age_max' => 5.0, 'quota' => 30, 'tuition' => 52000000],

            // Primary Years Program
            ['code' => 'G1', 'name' => 'Grade 1', 'category' => 'primary_years', 'age_min' => 6.0, 'age_max' => 7.0, 'quota' => 35, 'tuition' => 65000000],
            ['code' => 'G2', 'name' => 'Grade 2', 'category' => 'primary_years', 'age_min' => 7.0, 'age_max' => 8.0, 'quota' => 35, 'tuition' => 65000000],
            ['code' => 'G3', 'name' => 'Grade 3', 'category' => 'primary_years', 'age_min' => 8.0, 'age_max' => 9.0, 'quota' => 35, 'tuition' => 68000000],
            ['code' => 'G4', 'name' => 'Grade 4', 'category' => 'primary_years', 'age_min' => 9.0, 'age_max' => 10.0, 'quota' => 35, 'tuition' => 68000000],
            ['code' => 'G5', 'name' => 'Grade 5', 'category' => 'primary_years', 'age_min' => 10.0, 'age_max' => 11.0, 'quota' => 35, 'tuition' => 70000000],

            // Middle Years Program
            ['code' => 'G6', 'name' => 'Grade 6', 'category' => 'middle_years', 'age_min' => 11.0, 'age_max' => 12.0, 'quota' => 40, 'tuition' => 75000000],
            ['code' => 'G7', 'name' => 'Grade 7', 'category' => 'middle_years', 'age_min' => 12.0, 'age_max' => 13.0, 'quota' => 40, 'tuition' => 75000000],
            ['code' => 'G8', 'name' => 'Grade 8', 'category' => 'middle_years', 'age_min' => 13.0, 'age_max' => 14.0, 'quota' => 40, 'tuition' => 78000000],
            ['code' => 'G9', 'name' => 'Grade 9', 'category' => 'middle_years', 'age_min' => 14.0, 'age_max' => 15.0, 'quota' => 40, 'tuition' => 78000000],
        ];

        DB::beginTransaction();

        try {
            foreach ($schools as $school) {
                $academicYear = AcademicYear::where('school_id', $school->id)->where('is_active', true)->first();

                if (!$academicYear) {
                    $this->command->warn("  ⚠ No active academic year for {$school->code}, skipping...");
                    continue;
                }

                foreach ($levelsData as $levelData) {
                    Level::create([
                        'school_id' => $school->id,
                        'code' => $levelData['code'],
                        'name' => $levelData['name'],
                        'program_category' => $levelData['category'],
                        'age_min' => $levelData['age_min'],
                        'age_max' => $levelData['age_max'],
                        'quota' => $levelData['quota'],
                        'annual_tuition_fee' => $levelData['tuition'],
                        'description' => $this->getDescription($levelData['category'], $levelData['name']),
                        'is_active' => true,
                    ]);
                    $created++;
                }

                $this->command->info("  ✓ {$school->code}: 12 levels created");
            }

            DB::commit();

            // Summary
            $this->command->newLine();
            $this->command->info('════════════════════════════════════════');
            $this->command->info('✅ LEVELS SEEDING COMPLETE');
            $this->command->info('════════════════════════════════════════');
            $this->command->info("Total Levels Created: {$created}");
            $this->command->table(
                ['Category', 'Levels', 'Total Quota'],
                [
                    ['Early Years', 'EP, PS, PK', '75 per school'],
                    ['Primary Years', 'G1-G5', '175 per school'],
                    ['Middle Years', 'G6-G9', '160 per school'],
                    ['TOTAL', '12 levels', '410 per school'],
                ]
            );
            $this->command->newLine();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("✗ Error creating levels: {$e->getMessage()}");
            throw $e;
        }
    }

    private function getDescription(string $category, string $name): string
    {
        return match($category) {
            'early_years' => "{$name} program focuses on early childhood development through play-based learning, fostering curiosity, creativity, and social skills in a nurturing environment.",
            'primary_years' => "{$name} follows an inquiry-based curriculum that develops critical thinking, problem-solving skills, and a love for learning across all subject areas.",
            'middle_years' => "{$name} prepares students for higher education with rigorous academics, leadership development, and emphasis on global citizenship and service learning.",
            default => "{$name} at VIS International School.",
        };
    }
}
