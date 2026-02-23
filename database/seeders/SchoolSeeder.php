<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\School;
use Illuminate\Support\Facades\DB;

class SchoolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * VIS-BIN (Bintaro) → ACTIVE — primary school for trial/production
     * VIS-KG & VIS-BALI  → INACTIVE — seeded as reference data only
     */
    public function run(): void
    {
        $this->command->info('🏫 Creating VIS Schools...');

        DB::beginTransaction();

        try {
            $schools = [
                /*
                |------------------------------------------------------------------
                | VIS BINTARO — ACTIVE (Primary School)
                | Semua data lengkap dan realistis untuk trial production.
                |------------------------------------------------------------------
                */
                [
                    'code'                   => 'VIS-BIN',
                    'name'                   => 'VIS Bintaro',
                    'full_name'              => 'Veritas Intercultural School Bintaro',
                    'type'                   => 'main',
                    'email'                  => 'info@vis-bintaro.sch.id',
                    'phone'                  => '+62-21-7450-5678',
                    'website'                => 'https://vis-bintaro.sch.id',
                    'city'                   => 'Tangerang Selatan',
                    'country'                => 'Indonesia',
                    'address'                => 'Jl. Bintaro Utama Sektor 9 No. 8, Bintaro Jaya, Tangerang Selatan',
                    'postal_code'            => '15224',
                    'timezone'               => 'Asia/Jakarta',
                    'description'            => 'Veritas Intercultural School (VIS) Bintaro adalah sekolah internasional terkemuka yang berdiri sejak 2015 di kawasan Bintaro Jaya, Tangerang Selatan. Kami menawarkan kurikulum Cambridge International dari program Early Years hingga Middle Years, dengan lingkungan belajar multikultural yang mendukung perkembangan akademik, karakter, dan keterampilan abad ke-21 setiap siswa.',
                    'principal_name'         => 'Dr. Sarah Johnson, M.Ed.',
                    'principal_email'        => 'sarah.johnson@vis-bintaro.sch.id',
                    'is_active'              => true,
                    'allow_online_admission' => true,
                    'settings'               => [
                        'established_year'  => 2015,
                        'current_students'  => 320,
                        'max_capacity'      => 450,
                        'accreditation'     => 'Cambridge International Education (CIE)',
                        'curriculum'        => 'Cambridge Primary & Lower Secondary',
                        'school_type'       => 'International School',
                        'school_year_system'=> 'July - June',
                        'languages'         => ['English', 'Indonesian', 'Mandarin'],
                        'school_hours'      => [
                            'early_years'   => '07:30 - 13:00',
                            'primary_years' => '07:30 - 14:30',
                            'middle_years'  => '07:30 - 15:30',
                        ],
                        'facilities' => [
                            'Swimming Pool',
                            'Science Laboratory',
                            'Library & Digital Resource Center',
                            'Indoor Gymnasium',
                            'Computer Lab & Coding Room',
                            'Music Room',
                            'Art Studio',
                            'School Canteen',
                            'Nurse Room',
                            'Parent Lounge',
                        ],
                        'extracurricular' => [
                            'Swimming', 'Basketball', 'Football', 'Badminton',
                            'Piano', 'Choir', 'Robotics Club',
                            'Science Club', 'Debate Club', 'Art & Craft',
                        ],
                        'office_hours' => [
                            'monday_friday' => '07:00 - 16:30',
                            'saturday'      => '08:00 - 12:00',
                            'sunday'        => 'Closed',
                        ],
                        'bank_account' => [
                            'bank_name'      => 'Bank Mandiri',
                            'account_number' => '137-00-1234567-8',
                            'account_holder' => 'PT Veritas Intercultural School Bintaro',
                            'swift_code'     => 'BMRIIDJA',
                            'branch'         => 'Bintaro Jaya',
                        ],
                        'contact' => [
                            'admissions_email' => 'admissions@vis-bintaro.sch.id',
                            'admissions_phone' => '+62-21-7450-5678 ext. 201',
                            'finance_email'    => 'finance@vis-bintaro.sch.id',
                            'finance_phone'    => '+62-21-7450-5678 ext. 202',
                        ],
                    ],
                ],

                /*
                |------------------------------------------------------------------
                | VIS KELAPA GADING — INACTIVE (Reference Data Only)
                |------------------------------------------------------------------
                */
                [
                    'code'                   => 'VIS-KG',
                    'name'                   => 'VIS Kelapa Gading',
                    'full_name'              => 'Veritas Intercultural School Kelapa Gading',
                    'type'                   => 'branch',
                    'email'                  => 'info@vis-kg.sch.id',
                    'phone'                  => '+62-21-4589-5678',
                    'website'                => 'https://vis-kg.sch.id',
                    'city'                   => 'Jakarta Utara',
                    'country'                => 'Indonesia',
                    'address'                => 'Jl. Boulevard Raya No. 88, Kelapa Gading',
                    'postal_code'            => '14240',
                    'timezone'               => 'Asia/Jakarta',
                    'description'            => 'VIS Kelapa Gading branch campus.',
                    'principal_name'         => 'Mr. David Kumar',
                    'principal_email'        => 'david.kumar@vis-kg.sch.id',
                    'is_active'              => false,
                    'allow_online_admission' => false,
                    'settings'               => [
                        'established_year' => 2018,
                        'current_students' => 0,
                        'max_capacity'     => 350,
                        'accreditation'    => 'Cambridge International Education (CIE)',
                    ],
                ],

                /*
                |------------------------------------------------------------------
                | VIS BALI — INACTIVE (Reference Data Only)
                |------------------------------------------------------------------
                */
                [
                    'code'                   => 'VIS-BALI',
                    'name'                   => 'VIS Bali',
                    'full_name'              => 'Veritas Intercultural School Bali',
                    'type'                   => 'branch',
                    'email'                  => 'info@vis-bali.sch.id',
                    'phone'                  => '+62-361-789-4567',
                    'website'                => 'https://vis-bali.sch.id',
                    'city'                   => 'Denpasar',
                    'country'                => 'Indonesia',
                    'address'                => 'Jl. Danau Poso No. 99, Sanur, Denpasar',
                    'postal_code'            => '80228',
                    'timezone'               => 'Asia/Makassar',
                    'description'            => 'VIS Bali branch campus.',
                    'principal_name'         => 'Ms. Amanda Martinez',
                    'principal_email'        => 'amanda.martinez@vis-bali.sch.id',
                    'is_active'              => false,
                    'allow_online_admission' => false,
                    'settings'               => [
                        'established_year' => 2020,
                        'current_students' => 0,
                        'max_capacity'     => 300,
                        'accreditation'    => 'Cambridge International Education (CIE)',
                    ],
                ],
            ];

            $createdSchools = [];

            foreach ($schools as $schoolData) {
                $school = School::create($schoolData);
                $createdSchools[] = [
                    $school->code,
                    $school->name,
                    $school->city,
                    $school->is_active ? '✓ Active' : '✗ Inactive',
                    $school->settings['established_year'],
                ];

                $status = $school->is_active ? '✅' : '⏸️ ';
                $this->command->info("  {$status} {$school->code} - {$school->name}");
            }

            DB::commit();

            $this->command->newLine();
            $this->command->info('════════════════════════════════════════');
            $this->command->info('✅ SCHOOLS SEEDING COMPLETE');
            $this->command->info('════════════════════════════════════════');
            $this->command->table(
                ['Code', 'Name', 'Location', 'Status', 'Est.'],
                $createdSchools
            );
            $this->command->info('  ★ VIS-BIN is the PRIMARY ACTIVE school for this trial.');
            $this->command->newLine();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("✗ Error creating schools: {$e->getMessage()}");
            throw $e;
        }
    }
}
