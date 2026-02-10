<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Major;

class MajorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $majors = [
            [
                'code' => 'BOARDING_SCHOOL',
                'name' => 'BOARDING SCHOOL',
                'description' => 'Program boarding school dengan asrama penuh waktu. Siswa tinggal di asrama dan mendapatkan pendidikan agama yang intensif serta pembinaan karakter 24 jam.',
                'quota' => 20,
                'is_active' => true,
            ],
            [
                'code' => 'PROGRAM_KHUSUS',
                'name' => 'PROGRAM KHUSUS (PK)',
                'description' => 'Program khusus untuk siswa berprestasi dengan kurikulum yang diperkaya. Fokus pada pengembangan bakat dan minat siswa dalam bidang akademik dan non-akademik.',
                'quota' => 96,
                'is_active' => true,
            ],
            [
                'code' => 'RLG',
                'name' => 'REGULER',
                'description' => 'Program reguler dengan kurikulum standar nasional yang diperkaya dengan muatan lokal dan nilai-nilai keislaman.',
                'quota' => 140,
                'is_active' => true,
            ],
        ];

        foreach ($majors as $major) {
            Major::create($major);
        }

        $this->command->info('Majors created successfully!');
        $this->command->info('Total quota: ' . collect($majors)->sum('quota') . ' students');
    }
}
