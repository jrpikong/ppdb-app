<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AcademicYear;

class AcademicYearSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $academicYears = [
            [
                'name' => '2023/2024',
                'start_year' => 2023,
                'end_year' => 2024,
                'is_active' => false,
                'description' => 'Tahun Ajaran 2023/2024',
            ],
            [
                'name' => '2024/2025',
                'start_year' => 2024,
                'end_year' => 2025,
                'is_active' => true,
                'description' => 'Tahun Ajaran 2024/2025 (Aktif)',
            ],
            [
                'name' => '2025/2026',
                'start_year' => 2025,
                'end_year' => 2026,
                'is_active' => false,
                'description' => 'Tahun Ajaran 2025/2026',
            ],
        ];

        foreach ($academicYears as $year) {
            AcademicYear::create($year);
        }

        $this->command->info('Academic Years created successfully!');
    }
}
