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
     * Creates 3 VIS schools with real data
     */
    public function run(): void
    {
        $this->command->info('🏫 Creating VIS Schools...');

        DB::beginTransaction();

        try {
            $schools = [
                [
                    'code' => 'VIS-BIN',
                    'name' => 'VIS Bintaro',
                    'full_name' => 'Veritas Intercultural School Bintaro',
                    'type' => 'main',
                    'email' => 'info@vis-bintaro.sch.id',
                    'phone' => '+62-21-7452-1234',
                    'website' => 'https://vis-bintaro.sch.id',
                    'city' => 'Jakarta Selatan',
                    'country' => 'Indonesia',
                    'address' => 'Jl. Bintaro Utama No. 123, Bintaro',
                    'postal_code' => '12330',
                    'timezone' => 'Asia/Jakarta',
                    'description' => 'VIS Bintaro is the flagship campus established in 2015, offering a comprehensive international curriculum from Early Years to Middle Years. Located in the heart of Bintaro, Jakarta Selatan, we provide world-class education with state-of-the-art facilities.',
                    'principal_name' => 'Dr. Sarah Johnson',
                    'principal_email' => 'sarah.johnson@vis-bintaro.sch.id',
                    'is_active' => true,
                    'allow_online_admission' => true,
                    'settings' => [
                        'established_year' => 2015,
                        'current_students' => 300,
                        'max_capacity' => 400,
                        'accreditation' => 'Cambridge International',
                        'languages' => ['English', 'Indonesian', 'Mandarin'],
                        'facilities' => [
                            'Swimming Pool',
                            'Science Laboratory',
                            'Library',
                            'Playground',
                            'Computer Lab',
                            'Music Room',
                            'Art Studio',
                        ],
                        'office_hours' => [
                            'monday_friday' => '07:00 - 16:00',
                            'saturday' => '08:00 - 12:00',
                            'sunday' => 'Closed',
                        ],
                    ],
                ],
                [
                    'code' => 'VIS-KG',
                    'name' => 'VIS Kelapa Gading',
                    'full_name' => 'Veritas Intercultural School Kelapa Gading',
                    'type' => 'branch',
                    'email' => 'info@vis-kg.sch.id',
                    'phone' => '+62-21-4589-5678',
                    'website' => 'https://vis-kg.sch.id',
                    'city' => 'Jakarta Utara',
                    'country' => 'Indonesia',
                    'address' => 'Jl. Boulevard Raya No. 88, Kelapa Gading',
                    'postal_code' => '14240',
                    'timezone' => 'Asia/Jakarta',
                    'description' => 'VIS Kelapa Gading opened in 2018, bringing excellence in international education to North Jakarta. Our modern campus features innovative learning spaces and a nurturing environment for young learners.',
                    'principal_name' => 'Mr. David Kumar',
                    'principal_email' => 'david.kumar@vis-kg.sch.id',
                    'is_active' => true,
                    'allow_online_admission' => true,
                    'settings' => [
                        'established_year' => 2018,
                        'current_students' => 250,
                        'max_capacity' => 350,
                        'accreditation' => 'Cambridge International',
                        'languages' => ['English', 'Indonesian'],
                        'facilities' => [
                            'Indoor Playground',
                            'Science Lab',
                            'Library',
                            'Computer Lab',
                            'Art Studio',
                            'Music Room',
                        ],
                        'office_hours' => [
                            'monday_friday' => '07:00 - 16:00',
                            'saturday' => '08:00 - 12:00',
                            'sunday' => 'Closed',
                        ],
                    ],
                ],
                [
                    'code' => 'VIS-BALI',
                    'name' => 'VIS Bali',
                    'full_name' => 'Veritas Intercultural School Bali',
                    'type' => 'branch',
                    'email' => 'info@vis-bali.sch.id',
                    'phone' => '+62-361-789-4567',
                    'website' => 'https://vis-bali.sch.id',
                    'city' => 'Denpasar',
                    'country' => 'Indonesia',
                    'address' => 'Jl. Danau Poso No. 99, Sanur, Denpasar',
                    'postal_code' => '80228',
                    'timezone' => 'Asia/Makassar',
                    'description' => 'VIS Bali, our newest campus established in 2020, combines world-class education with the serene beauty of Bali. Located in Sanur, we offer a unique learning environment that embraces both academic excellence and cultural richness.',
                    'principal_name' => 'Ms. Amanda Martinez',
                    'principal_email' => 'amanda.martinez@vis-bali.sch.id',
                    'is_active' => true,
                    'allow_online_admission' => true,
                    'settings' => [
                        'established_year' => 2020,
                        'current_students' => 200,
                        'max_capacity' => 300,
                        'accreditation' => 'Cambridge International',
                        'languages' => ['English', 'Indonesian', 'Balinese'],
                        'facilities' => [
                            'Outdoor Learning Spaces',
                            'Swimming Pool',
                            'Library',
                            'Computer Lab',
                            'Art Studio',
                            'Traditional Balinese Pavilion',
                        ],
                        'office_hours' => [
                            'monday_friday' => '07:30 - 16:00',
                            'saturday' => '08:00 - 12:00',
                            'sunday' => 'Closed',
                        ],
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
                    $school->settings['established_year'],
                    $school->settings['current_students'],
                ];

                $this->command->info("  ✓ {$school->code} - {$school->name}");
            }

            DB::commit();

            // Summary
            $this->command->newLine();
            $this->command->info('════════════════════════════════════════');
            $this->command->info('✅ SCHOOLS SEEDING COMPLETE');
            $this->command->info('════════════════════════════════════════');
            $this->command->table(
                ['Code', 'Name', 'Location', 'Est.', 'Students'],
                $createdSchools
            );
            $this->command->newLine();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("✗ Error creating schools: {$e->getMessage()}");
            throw $e;
        }
    }
}
