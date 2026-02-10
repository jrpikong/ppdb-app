<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RegistrationPeriod;
use App\Models\AcademicYear;
use Carbon\Carbon;

class RegistrationPeriodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $activeYear = AcademicYear::where('is_active', true)->first();

        if (!$activeYear) {
            $this->command->error('No active academic year found!');
            return;
        }

        $periods = [
            [
                'academic_year_id' => $activeYear->id,
                'name' => 'Gelombang 1',
                'start_date' => Carbon::parse('2024-05-01'),
                'end_date' => Carbon::parse('2024-06-15'),
                'announcement_date' => Carbon::parse('2024-06-20'),
                're_registration_start' => Carbon::parse('2024-06-21'),
                're_registration_end' => Carbon::parse('2024-06-30'),
                'is_active' => true,
                'description' => 'Gelombang pendaftaran pertama untuk tahun ajaran 2024/2025',
            ],
            [
                'academic_year_id' => $activeYear->id,
                'name' => 'Gelombang 2',
                'start_date' => Carbon::parse('2024-07-01'),
                'end_date' => Carbon::parse('2024-07-20'),
                'announcement_date' => Carbon::parse('2024-07-25'),
                're_registration_start' => Carbon::parse('2024-07-26'),
                're_registration_end' => Carbon::parse('2024-08-05'),
                'is_active' => false,
                'description' => 'Gelombang pendaftaran kedua untuk sisa kuota',
            ],
        ];

        foreach ($periods as $period) {
            RegistrationPeriod::create($period);
        }

        $this->command->info('Registration Periods created successfully!');
    }
}
