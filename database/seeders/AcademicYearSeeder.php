<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{School, AcademicYear};
use Carbon\Carbon;

class AcademicYearSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('📅 Creating Academic Years...');
        
        $schools = School::all();
        $created = 0;
        
        foreach ($schools as $school) {
            AcademicYear::create([
                'school_id' => $school->id,
                'name' => '2024-2025',
                'start_year' => 2024,
                'end_year' => 2025,
                'start_date' => Carbon::parse('2024-08-01'),
                'end_date' => Carbon::parse('2025-06-30'),
                'is_active' => true,
                'description' => "Academic Year 2024-2025 for {$school->name}",
            ]);
            $created++;
            $this->command->info("  ✓ {$school->code}: 2024-2025");
        }
        
        $this->command->info("✅ Created {$created} academic years");
    }
}
