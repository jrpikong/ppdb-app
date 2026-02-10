<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Registration;
use App\Models\Address;
use App\Models\ParentModel;
use App\Models\Document;
use App\Models\Payment;
use App\Models\PaymentType;
use App\Models\Score;
use App\Models\Announcement;
use App\Models\ReRegistration;
use App\Models\AcademicYear;
use App\Models\RegistrationPeriod;
use App\Models\Major;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SampleRegistrationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $activeYear = AcademicYear::where('is_active', true)->first();
        $activePeriod = RegistrationPeriod::where('is_active', true)->first();
        $majors = Major::where('is_active', true)->get();

        if (!$activeYear || !$activePeriod || $majors->isEmpty()) {
            $this->command->error('Missing required data: academic year, period, or majors!');
            return;
        }

        $students = User::role('calon_siswa')->get();
        $panitia = User::role('panitia')->first();

        if ($students->isEmpty() || !$panitia) {
            $this->command->error('No students or panitia found!');
            return;
        }

        $provinces = ['Jawa Tengah', 'Jawa Timur', 'DI Yogyakarta', 'Jawa Barat'];
        $regencies = ['Wonogiri', 'Sukoharjo', 'Karanganyar', 'Sragen', 'Boyolali'];
        $previousSchools = [
            'SD Negeri 1 Wonogiri',
            'SD Negeri 2 Wonogiri',
            'MI Muhammadiyah Wonogiri',
            'SD Islam Terpadu Al-Ikhlas',
            'SD Negeri Purwantoro',
        ];

        $statuses = ['draft', 'submitted', 'verified', 'passed', 're_registered'];
        $counter = 1;

        foreach ($students as $index => $student) {
            DB::beginTransaction();

            try {
                // Determine status (varied for realistic data)
                $status = $statuses[array_rand($statuses)];

                // Generate realistic NISN (10 digits)
                $nisn = str_pad((1234567890 + $index), 10, '0', STR_PAD_LEFT);

                // Generate realistic NIK (16 digits - Jawa Tengah)
                $nik = '3313' . str_pad((123456789012 + $index), 12, '0', STR_PAD_LEFT);

                // Create Registration
                $registration = Registration::create([
                    'user_id' => $student->id,
                    'academic_year_id' => $activeYear->id,
                    'registration_period_id' => $activePeriod->id,
                    'major_id' => $majors->random()->id,
                    'major_id_second' => $majors->random()->id,
                    'registration_number' => 'REG/2024/' . str_pad($counter++, 4, '0', STR_PAD_LEFT),
                    'registration_type' => 'siswa_baru',
                    'nisn' => $nisn,
                    'nik' => $nik,
                    'full_name' => $student->name,
                    'gender' => $index % 2 == 0 ? 'laki-laki' : 'perempuan',
                    'birth_place' => $regencies[array_rand($regencies)],
                    'birth_date' => Carbon::now()->subYears(13)->subDays(rand(1, 365)),
                    'religion' => 'islam',
                    'citizenship' => 'wni',
                    'child_number' => rand(1, 4),
                    'siblings_count' => rand(0, 3),
                    'phone' => $student->phone,
                    'email' => $student->email,
                    'hobby' => ['Membaca', 'Olahraga', 'Musik', 'Melukis'][array_rand(['Membaca', 'Olahraga', 'Musik', 'Melukis'])],
                    'previous_school' => $previousSchools[array_rand($previousSchools)],
                    'npsn_previous_school' => '203638' . rand(10, 99),
                    'living_status' => 'dengan_orang_tua',
                    'transportation' => ['sepeda', 'motor', 'angkutan_umum'][array_rand(['sepeda', 'motor', 'angkutan_umum'])],
                    'distance_to_school' => rand(1, 20),
                    'travel_time' => rand(10, 60),
                    'has_kip' => rand(0, 10) < 3, // 30% have KIP
                    'kip_number' => rand(0, 10) < 3 ? 'KIP' . rand(100000, 999999) : null,
                    'immunization_hepatitis_b' => true,
                    'immunization_bcg' => true,
                    'immunization_dpt' => true,
                    'immunization_polio' => true,
                    'immunization_campak' => true,
                    'immunization_covid' => true,
                    'status' => $status,
                    'submitted_at' => in_array($status, ['submitted', 'verified', 'passed', 're_registered']) ? now()->subDays(rand(1, 30)) : null,
                    'verified_at' => in_array($status, ['verified', 'passed', 're_registered']) ? now()->subDays(rand(1, 20)) : null,
                    'verified_by' => in_array($status, ['verified', 'passed', 're_registered']) ? $panitia->id : null,
                ]);

                // Create Address
                Address::create([
                    'registration_id' => $registration->id,
                    'province' => $provinces[array_rand($provinces)],
                    'province_code' => '33',
                    'regency' => $regencies[array_rand($regencies)],
                    'regency_code' => '3313',
                    'district' => 'Wonogiri',
                    'district_code' => '331301',
                    'village' => 'Wonokarto',
                    'village_code' => '33130101',
                    'street_address' => 'Jl. Raya No. ' . rand(1, 100),
                    'rt' => str_pad(rand(1, 10), 2, '0', STR_PAD_LEFT),
                    'rw' => str_pad(rand(1, 5), 2, '0', STR_PAD_LEFT),
                    'postal_code' => '5761' . rand(1, 9),
                ]);

                // Create Father Data
                $fatherNik = '3313' . str_pad((123456789012 + $index + 1000), 12, '0', STR_PAD_LEFT);

                ParentModel::create([
                    'registration_id' => $registration->id,
                    'type' => 'ayah',
                    'name' => 'Bapak ' . explode(' ', $student->name)[0],
                    'nik' => $fatherNik,
                    'birth_place' => $regencies[array_rand($regencies)],
                    'birth_date' => Carbon::now()->subYears(40 + rand(0, 10)),
                    'status' => 'masih_hidup',
                    'citizenship' => 'wni',
                    'education' => ['sma', 'diploma', 'sarjana'][array_rand(['sma', 'diploma', 'sarjana'])],
                    'occupation' => ['Pegawai Swasta', 'Wiraswasta', 'PNS', 'Petani'][array_rand(['Pegawai Swasta', 'Wiraswasta', 'PNS', 'Petani'])],
                    'monthly_income' => rand(2, 10) * 1000000,
                    'phone' => '081' . rand(100000000, 999999999),
                ]);

                // Create Mother Data
                $motherNik = '3313' . str_pad((123456789012 + $index + 2000), 12, '0', STR_PAD_LEFT);

                ParentModel::create([
                    'registration_id' => $registration->id,
                    'type' => 'ibu',
                    'name' => 'Ibu ' . explode(' ', $student->name)[0],
                    'nik' => $motherNik,
                    'birth_place' => $regencies[array_rand($regencies)],
                    'birth_date' => Carbon::now()->subYears(35 + rand(0, 10)),
                    'status' => 'masih_hidup',
                    'citizenship' => 'wni',
                    'education' => ['smp', 'sma', 'diploma'][array_rand(['smp', 'sma', 'diploma'])],
                    'occupation' => ['Ibu Rumah Tangga', 'Pegawai Swasta', 'Guru', 'Wiraswasta'][array_rand(['Ibu Rumah Tangga', 'Pegawai Swasta', 'Guru', 'Wiraswasta'])],
                    'monthly_income' => rand(0, 5) * 1000000,
                    'phone' => '081' . rand(100000000, 999999999),
                ]);

                // Create Documents (if submitted or later)
                if (in_array($status, ['submitted', 'verified', 'passed', 're_registered'])) {
                    $documentTypes = ['foto_siswa', 'kartu_keluarga', 'akta_kelahiran', 'ijazah'];

                    foreach ($documentTypes as $docType) {
                        Document::create([
                            'registration_id' => $registration->id,
                            'type' => $docType,
                            'name' => ucfirst(str_replace('_', ' ', $docType)) . '.pdf',
                            'file_path' => 'documents/' . $registration->registration_number . '/' . $docType . '.pdf',
                            'file_type' => 'application/pdf',
                            'file_size' => rand(100000, 500000),
                            'status' => in_array($status, ['verified', 'passed', 're_registered']) ? 'approved' : 'pending',
                            'verified_by' => in_array($status, ['verified', 'passed', 're_registered']) ? $panitia->id : null,
                            'verified_at' => in_array($status, ['verified', 'passed', 're_registered']) ? now()->subDays(rand(1, 15)) : null,
                        ]);
                    }
                }

                // Create Payment (if submitted or later)
                if (in_array($status, ['submitted', 'verified', 'passed', 're_registered'])) {
                    $pendaftaran = PaymentType::where('code', '001')->first();

                    Payment::create([
                        'registration_id' => $registration->id,
                        'payment_type_id' => $pendaftaran->id,
                        'transaction_code' => date('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
                        'amount' => $pendaftaran->amount,
                        'payment_date' => now()->subDays(rand(5, 25)),
                        'payment_method' => 'Transfer Bank',
                        'proof_file' => 'payments/' . $registration->registration_number . '/bukti-pendaftaran.jpg',
                        'status' => in_array($status, ['verified', 'passed', 're_registered']) ? 'verified' : 'waiting_verification',
                        'verified_by' => in_array($status, ['verified', 'passed', 're_registered']) ? $panitia->id : null,
                        'verified_at' => in_array($status, ['verified', 'passed', 're_registered']) ? now()->subDays(rand(1, 10)) : null,
                    ]);
                }

                // Create Score (if verified or later)
                if (in_array($status, ['verified', 'passed', 're_registered'])) {
                    Score::create([
                        'registration_id' => $registration->id,
                        'rapor_semester_1' => rand(75, 95),
                        'rapor_semester_2' => rand(75, 95),
                        'rapor_semester_3' => rand(75, 95),
                        'rapor_semester_4' => rand(75, 95),
                        'rapor_semester_5' => rand(75, 95),
                        'exam_math' => rand(70, 100),
                        'exam_science' => rand(70, 100),
                        'exam_indonesian' => rand(70, 100),
                        'exam_english' => rand(70, 100),
                        'exam_religion' => rand(70, 100),
                        'achievement_score' => rand(0, 10),
                        'inputted_by' => $panitia->id,
                        'inputted_at' => now()->subDays(rand(1, 5)),
                    ]);
                }

                // Create Announcement (if passed or re_registered)
                if (in_array($status, ['passed', 're_registered'])) {
                    $announcement = Announcement::create([
                        'registration_id' => $registration->id,
                        'major_id' => $registration->major_id,
                        'announcement_number' => 'ANN/2024/' . str_pad(rand(1, 100), 4, '0', STR_PAD_LEFT),
                        'status' => 'lulus',
                        'rank' => rand(1, 50),
                        'final_score' => $registration->score->total_score ?? rand(75, 95),
                        'announced_at' => now()->subDays(rand(1, 5)),
                        're_registration_deadline' => now()->addDays(7),
                        'email_sent' => true,
                        'email_sent_at' => now()->subDays(rand(1, 5)),
                        'published_by' => $panitia->id,
                    ]);

                    // Create Re-registration (if re_registered)
                    if ($status === 're_registered') {
                        ReRegistration::create([
                            'registration_id' => $registration->id,
                            'announcement_id' => $announcement->id,
                            're_registration_number' => 'REREG/2024/' . str_pad(rand(1, 100), 4, '0', STR_PAD_LEFT),
                            're_registration_date' => now()->subDays(rand(1, 3)),
                            'total_payment' => 2020000, // Seragam + Buku + Daftar Ulang
                            'payment_proof' => 'payments/' . $registration->registration_number . '/bukti-daftar-ulang.jpg',
                            'status' => 'verified',
                            'original_documents_submitted' => true,
                            'submitted_documents' => ['kartu_keluarga', 'akta_kelahiran', 'ijazah'],
                            'verified_by' => $panitia->id,
                            'verified_at' => now()->subDays(rand(1, 2)),
                        ]);
                    }
                }

                DB::commit();
                $this->command->info("✓ Registration created for: {$student->name} (Status: {$status}, NISN: {$nisn})");

            } catch (\Exception $e) {
                DB::rollBack();
                $this->command->error("✗ Failed to create registration for: {$student->name}");
                $this->command->error("  Error: " . $e->getMessage());
            }
        }

        // Update rankings after all scores created
        if (Score::count() > 0) {
            Score::updateRankings();
            $this->command->info('✓ Rankings updated successfully!');
        }

        $this->command->info('');
        $this->command->info('================================================');
        $this->command->info('✅ Sample Registrations created successfully!');
        $this->command->info('Total registrations: ' . Registration::count());
        $this->command->info('================================================');
    }
}
