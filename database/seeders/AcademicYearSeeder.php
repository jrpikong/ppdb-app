<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\School;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AcademicYearSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Creating Academic Years...');

        $schools = School::all();
        $created = 0;
        $startYear = (int) now()->format('Y');
        $endYear = $startYear + 1;

        foreach ($schools as $school) {
            AcademicYear::create([
                'school_id' => $school->id,
                'name' => "{$startYear}-{$endYear}",
                'start_year' => $startYear,
                'end_year' => $endYear,
                'start_date' => Carbon::create($startYear, 7, 1),
                'end_date' => Carbon::create($endYear, 6, 30),
                'is_active' => true,
                'description' => "Academic Year {$startYear}-{$endYear} for {$school->name}",
            ]);

            $created++;
            $this->command->info("  - {$school->code}: {$startYear}-{$endYear}");
        }

        $this->command->info("Created {$created} academic years.");
    }
}
