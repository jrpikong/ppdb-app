<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Application, School, AcademicYear, AdmissionPeriod, Level, User, ParentGuardian, Document, Payment, PaymentType, Schedule, MedicalRecord};
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ApplicationSeeder extends Seeder
{
    /**
     * Seed realistic VIS application data with complete student information
     */
    public function run(): void
    {
        $this->command->info('📝 Creating Student Applications with Complete Data...');

        DB::transaction(function () {
            $schools = School::all();

            // Get all parent users (created by UserSeeder)
            $parentUsers = User::whereHas('roles', function ($query) {
                $query->where('name', 'parent');
            })->get();

            if ($parentUsers->isEmpty()) {
                $this->command->error('  ✗ No parent users found! Please run UserSeeder first.');
                return;
            }

            $this->command->info("  → Found {$parentUsers->count()} parent users");

            $totalCreated = 0;
            $parentIndex = 0;

            foreach ($schools as $school) {
                $this->command->info("  → Processing {$school->name}...");

                $academicYear = AcademicYear::where('school_id', $school->id)
                    ->where('is_active', true)
                    ->first();

                $admissionPeriod = AdmissionPeriod::where('school_id', $school->id)
                    ->where('is_active', true)
                    ->first();

                if (!$academicYear || !$admissionPeriod) {
                    $this->command->warn("    ⚠ No active academic year or admission period for {$school->name}");
                    continue;
                }

                $levels = Level::where('school_id', $school->id)->get();

                // Calculate applications per school based on available parents
                $applicationsPerSchool = min(15, ceil($parentUsers->count() / $schools->count()));

                for ($i = 1; $i <= $applicationsPerSchool; $i++) {
                    // Get next available parent (cycle through parents)
                    $parent = $parentUsers[$parentIndex % $parentUsers->count()];
                    $parentIndex++;

                    $level = $levels->random();
                    $status = $this->getStatusByIndex($i);

                    // Create application
                    $application = $this->createApplication(
                        $school,
                        $academicYear,
                        $admissionPeriod,
                        $level,
                        $parent,
                        $status,
                        $i
                    );

                    // Add related data
                    $this->createParentGuardians($application);
                    $this->createDocuments($application, $status);
                    $this->createPayments($application, $school, $status);
                    $this->createSchedules($application, $school, $status);
                    $this->createMedicalRecord($application);

                    $totalCreated++;
                }
            }

            $this->command->info("  ✓ Created {$totalCreated} complete applications");
        });
    }

    /**
     * Get status based on index for variety
     */
    private function getStatusByIndex(int $index): string
    {
        return match (true) {
            $index <= 2 => 'draft',
            $index <= 5 => 'submitted',
            $index <= 7 => 'under_review',
            $index == 8 => 'documents_verified',
            $index == 9 => 'interview_scheduled',
            $index == 10 => 'interview_completed',
            $index == 11 => 'payment_pending',
            $index == 12 => 'payment_verified',
            $index == 13 => 'accepted',
            $index == 14 => 'enrolled',
            $index == 15 => 'rejected',
            default => 'submitted',
        };
    }

    /**
     * Create application with realistic student data
     */
    private function createApplication(
        School $school,
        AcademicYear $academicYear,
        AdmissionPeriod $admissionPeriod,
        Level $level,
        User $parent,
        string $status,
        int $index
    ): Application {
        $studentNames = [
            ['first' => 'Ethan', 'middle' => 'James', 'last' => 'Thompson', 'preferred' => 'Ethan', 'gender' => 'male'],
            ['first' => 'Ava', 'middle' => 'Grace', 'last' => 'Martinez', 'preferred' => 'Ava', 'gender' => 'female'],
            ['first' => 'Noah', 'middle' => 'Patrick', 'last' => 'O\'Brien', 'preferred' => 'Noah', 'gender' => 'male'],
            ['first' => 'Chloe', 'middle' => 'Min-Ji', 'last' => 'Kim', 'preferred' => 'Chloe', 'gender' => 'female'],
            ['first' => 'Liam', 'middle' => 'Raj', 'last' => 'Patel', 'preferred' => 'Liam', 'gender' => 'male'],
            ['first' => 'Sophie', 'middle' => 'Mei Ling', 'last' => 'Tan', 'preferred' => 'Sophie', 'gender' => 'female'],
            ['first' => 'Lucas', 'middle' => 'William', 'last' => 'Anderson', 'preferred' => 'Luke', 'gender' => 'male'],
            ['first' => 'Emily', 'middle' => 'Isabella', 'last' => 'Rodriguez', 'preferred' => 'Emily', 'gender' => 'female'],
            ['first' => 'Mason', 'middle' => 'Wei', 'last' => 'Chen', 'preferred' => 'Mason', 'gender' => 'male'],
            ['first' => 'Amelia', 'middle' => 'Rose', 'last' => 'Wilson', 'preferred' => 'Amy', 'gender' => 'female'],
            ['first' => 'Aiden', 'middle' => 'Arjun', 'last' => 'Singh', 'preferred' => 'Aiden', 'gender' => 'male'],
            ['first' => 'Lily', 'middle' => 'Yeon', 'last' => 'Lee', 'preferred' => 'Lily', 'gender' => 'female'],
            ['first' => 'Benjamin', 'middle' => 'Minh', 'last' => 'Nguyen', 'preferred' => 'Ben', 'gender' => 'male'],
            ['first' => 'Charlotte', 'middle' => 'Sofia', 'last' => 'Garcia', 'preferred' => 'Charlie', 'gender' => 'female'],
            ['first' => 'Henry', 'middle' => 'Alexander', 'last' => 'Brown', 'preferred' => 'Henry', 'gender' => 'male'],
        ];

        $student = $studentNames[($index - 1) % count($studentNames)];

        // Calculate appropriate birth date based on level
        $birthDate = $this->calculateBirthDate($level);

        $applicationNumber = $this->generateApplicationNumber($school, $index);

        $submittedAt = in_array($status, ['draft']) ? null : now()->subDays(rand(5, 30));
        $reviewedAt = in_array($status, ['under_review', 'documents_verified', 'interview_scheduled', 'interview_completed', 'payment_pending', 'payment_verified', 'accepted', 'enrolled', 'rejected'])
            ? $submittedAt?->addDays(rand(2, 5))
            : null;
        $decisionMadeAt = in_array($status, ['accepted', 'enrolled', 'rejected'])
            ? $reviewedAt?->addDays(rand(3, 7))
            : null;
        $enrolledAt = $status === 'enrolled' ? $decisionMadeAt?->addDays(rand(1, 3)) : null;

        // Assign reviewer for applications in review
        $assignedTo = in_array($status, ['under_review', 'documents_verified', 'interview_scheduled', 'interview_completed', 'payment_pending', 'payment_verified', 'accepted', 'enrolled', 'rejected'])
            ? User::where('school_id', $school->id)
                ->whereHas('roles', fn($q) => $q->whereIn('name', ['school_admin', 'admission_admin']))
                ->inRandomOrder()
                ->first()?->id
            : null;

        $reviewedBy = $reviewedAt ? $assignedTo : null;

        return Application::create([
            'user_id' => $parent->id,
            'school_id' => $school->id,
            'academic_year_id' => $academicYear->id,
            'admission_period_id' => $admissionPeriod->id,
            'level_id' => $level->id,
            'application_number' => $applicationNumber,

            // Student Information
            'student_first_name' => $student['first'],
            'student_middle_name' => $student['middle'],
            'student_last_name' => $student['last'],
            'student_preferred_name' => $student['preferred'],
            'gender' => $student['gender'],
            'birth_date' => $birthDate,
            'birth_place' => $this->getRandomBirthPlace(),
            'nationality' => $this->getRandomNationality(),
            'passport_number' => $this->generatePassportNumber(),

            // Contact
            'email' => strtolower($student['first'] . '.' . $student['last']) . '@student.vis.sch.id',
            'phone' => $this->generateIndonesianPhone(),

            // Address
            'current_address' => $this->getIndonesianAddress($school),
            'current_city' => $this->getCityBySchool($school),
            'current_country' => 'Indonesia',
            'current_postal_code' => $this->getPostalCodeBySchool($school),

            // Previous School
            'previous_school_name' => $this->getPreviousSchoolName($level),
            'previous_school_country' => rand(1, 10) > 7 ? $this->getRandomCountry() : 'Indonesia',
            'current_grade_level' => $this->getCurrentGradeLevel($level),
            'previous_school_start_date' => $birthDate->copy()->addYears(3),
            'previous_school_end_date' => now()->subMonths(3),

            // Educational
            'languages_spoken' => $this->getLanguages(),
            'interests_hobbies' => $this->getInterests(),

            // Special Needs
            'special_needs' => rand(1, 10) > 8 ? $this->getSpecialNeeds() : null,
            'learning_support_required' => rand(1, 10) > 9 ? $this->getLearningSupport() : null,

            // Assessment Requirements
            'requires_observation' => in_array($level->code, ['EP', 'PS', 'PK']),
            'requires_test' => in_array($level->code, ['G1', 'G2', 'G3', 'G4', 'G5', 'G6', 'G7', 'G8', 'G9']),
            'requires_interview' => true,

            // Status & Workflow
            'status' => $status,
            'status_notes' => $this->getStatusNotes($status),
            'submitted_at' => $submittedAt,
            'reviewed_at' => $reviewedAt,
            'reviewed_by' => $reviewedBy,
            'decision_made_at' => $decisionMadeAt,
            'enrolled_at' => $enrolledAt,
            'assigned_to' => $assignedTo,
        ]);
    }

    /**
     * Create parent/guardian records based on the parent user
     */
    private function createParentGuardians(Application $application): void
    {
        $fatherFirstNames = ['John', 'Michael', 'David', 'Robert', 'James', 'William', 'Thomas', 'Richard', 'Charles', 'Daniel', 'Paul', 'Mark', 'Steven', 'Andrew', 'Peter'];
        $motherFirstNames = ['Mary', 'Jennifer', 'Sarah', 'Lisa', 'Michelle', 'Amanda', 'Jessica', 'Emily', 'Emma', 'Sophia', 'Isabella', 'Mia', 'Charlotte', 'Amelia', 'Olivia'];

        $occupations = [
            'Software Engineer', 'Doctor', 'Lawyer', 'Business Owner', 'Teacher',
            'Accountant', 'Marketing Manager', 'Architect', 'Consultant', 'Entrepreneur',
            'Financial Analyst', 'Project Manager', 'Designer', 'Engineer', 'Director'
        ];

        // Get parent user to use their info
        $parentUser = $application->user;

        // Extract last name from student
        $lastName = $application->student_last_name;

        // Father
        $fatherFirstName = $fatherFirstNames[array_rand($fatherFirstNames)];
        ParentGuardian::create([
            'application_id' => $application->id,
            'type' => 'father',
            'first_name' => $fatherFirstName,
            'middle_name' => null,
            'last_name' => $lastName,
            'email' => 'father.' . strtolower($lastName) . rand(100, 999) . '@email.com',
            'phone' => $parentUser->phone ?? $this->generateIndonesianPhone(),
            'mobile' => $this->generateIndonesianPhone(),
            'nationality' => $application->nationality,
            'occupation' => $occupations[array_rand($occupations)],
            'company_name' => $this->getWorkplace(),
            'address' => $application->current_address,
            'city' => $application->current_city,
            'country' => $application->current_country,
            'postal_code' => $application->current_postal_code,
            'is_primary_contact' => rand(0, 1) === 1,
            'is_emergency_contact' => true,
        ]);

        // Mother - use parent user's data
        $motherFirstName = $motherFirstNames[array_rand($motherFirstNames)];
        ParentGuardian::create([
            'application_id' => $application->id,
            'type' => 'mother',
            'first_name' => $motherFirstName,
            'middle_name' => null,
            'last_name' => $lastName,
            'email' => $parentUser->email, // Use actual parent email
            'phone' => $this->generateIndonesianPhone(),
            'mobile' => $parentUser->phone ?? $this->generateIndonesianPhone(),
            'nationality' => $application->nationality,
            'occupation' => $occupations[array_rand($occupations)],
            'company_name' => $this->getWorkplace(),
            'address' => $application->current_address,
            'city' => $application->current_city,
            'country' => $application->current_country,
            'postal_code' => $application->current_postal_code,
            'is_primary_contact' => true, // Mother as primary contact
            'is_emergency_contact' => true,
        ]);
    }

    /**
     * Create document records
     */
    private function createDocuments(Application $application, string $status): void
    {
        if ($status === 'draft') return; // Draft applications don't have documents yet

        $documentTypes = [
            'birth_certificate' => 'Birth Certificate',
            'passport' => 'Passport Copy',
            'family_card' => 'Family Card (Kartu Keluarga)',
            'student_photo_1' => 'Student Photo 3x4 (First)',
            'student_photo_2' => 'Student Photo 3x4 (Second)',
            'father_photo' => 'Father Photo 3x4',
            'mother_photo' => 'Mother Photo 3x4',
            'latest_report_book' => 'Latest School Report Card',
            'medical_history' => 'Health Certificate',
            'immunization_record' => 'Immunization Record',
        ];

        foreach ($documentTypes as $type => $name) {
            $verificationStatus = match ($status) {
                'submitted' => 'pending',
                'under_review' => rand(1, 10) > 5 ? 'pending' : 'approved',
                'documents_verified', 'interview_scheduled', 'interview_completed', 'payment_pending', 'payment_verified', 'accepted', 'enrolled' => 'approved',
                'rejected' => rand(1, 10) > 7 ? 'rejected' : 'approved',
                default => 'pending',
            };

            $verifiedAt = $verificationStatus === 'approved' ? now()->subDays(rand(1, 10)) : null;
            $verifiedBy = $verificationStatus === 'approved'
                ? User::where('school_id', $application->school_id)
                    ->whereHas('roles', fn($q) => $q->whereIn('name', ['school_admin', 'admission_admin']))
                    ->inRandomOrder()
                    ->first()?->id
                : null;

            Document::create([
                'application_id' => $application->id,
                'type' => $type,
                'name' => $name . ' - ' . $application->student_first_name . ' ' . $application->student_last_name . '.pdf',
                'file_path' => 'documents/' . Str::slug($name) . '-' . $application->application_number . '.pdf',
                'file_type' => 'application/pdf',
                'file_size' => rand(500000, 2000000), // 500KB - 2MB
                'status' => $verificationStatus,
                'verification_notes' => $verificationStatus === 'approved' ? 'Document verified and approved' : null,
                'verified_at' => $verifiedAt,
                'verified_by' => $verifiedBy,
            ]);
        }
    }

    /**
     * Create payment records
     */
    private function createPayments(Application $application, School $school, string $status): void
    {
        if (in_array($status, ['draft', 'submitted'])) return;

        $paymentTypes = PaymentType::where('school_id', $school->id)->get();

        // Pre-submission payment (Saving Seat)
        if (in_array($status, ['under_review', 'documents_verified', 'interview_scheduled', 'interview_completed', 'payment_pending', 'payment_verified', 'accepted', 'enrolled', 'rejected'])) {
            $savingSeatType = $paymentTypes->where('code', 'SAVING_SEAT')->first();

            if ($savingSeatType) {
                Payment::create([
                    'application_id' => $application->id,
                    'payment_type_id' => $savingSeatType->id,
                    'transaction_code' => $this->generateTransactionCode($school, 1),
                    'amount' => $savingSeatType->amount,
                    'currency' => 'IDR',
                    'payment_date' => $application->submitted_at?->addDay() ?? now(),
                    'payment_method' => $this->getRandomPaymentMethod(),
                    'proof_file' => 'payments/saving-seat-' . $application->application_number . '.jpg',
                    'status' => 'verified',
                    'verified_at' => $application->submitted_at?->addDays(2),
                    'verified_by' => User::where('school_id', $school->id)
                        ->whereHas('roles', fn($q) => $q->where('name', 'finance_admin'))
                        ->inRandomOrder()
                        ->first()?->id,
                ]);
            }
        }

        // Post-acceptance payments
        if (in_array($status, ['payment_pending', 'payment_verified', 'accepted', 'enrolled'])) {
            $postAcceptanceTypes = $paymentTypes->whereIn('code', ['REGISTRATION_FEE', 'DEVELOPMENT_FEE'])->all();

            foreach ($postAcceptanceTypes as $index => $paymentType) {
                $isPaid = in_array($status, ['payment_verified', 'accepted', 'enrolled']);

                Payment::create([
                    'application_id' => $application->id,
                    'payment_type_id' => $paymentType->id,
                    'transaction_code' => $this->generateTransactionCode($school, $index + 2),
                    'amount' => $paymentType->amount,
                    'currency' => 'IDR',
                    'payment_date' => $isPaid ? ($application->decision_made_at?->addDays(rand(1, 3)) ?? now()) : now(),
                    'payment_method' => $this->getRandomPaymentMethod(),
                    'proof_file' => $isPaid ? 'payments/' . $paymentType->code . '-' . $application->application_number . '.jpg' : null,
                    'status' => $isPaid ? 'verified' : 'pending',
                    'verified_at' => $isPaid ? ($application->decision_made_at?->addDays(rand(2, 5))) : null,
                    'verified_by' => $isPaid
                        ? User::where('school_id', $school->id)
                            ->whereHas('roles', fn($q) => $q->where('name', 'finance_admin'))
                            ->inRandomOrder()
                            ->first()?->id
                        : null,
                ]);
            }
        }

        // Enrollment payments
        if ($status === 'enrolled') {
            $enrollmentTypes = $paymentTypes->whereIn('code', ['UNIFORM_PACKAGE', 'BOOK_PACKAGE', 'TECHNOLOGY_FEE'])->all();

            foreach ($enrollmentTypes as $index => $paymentType) {
                Payment::create([
                    'application_id' => $application->id,
                    'payment_type_id' => $paymentType->id,
                    'transaction_code' => $this->generateTransactionCode($school, $index + 5),
                    'amount' => $paymentType->amount,
                    'currency' => 'IDR',
                    'payment_date' => $application->enrolled_at?->subDays(rand(1, 2)) ?? now(),
                    'payment_method' => $this->getRandomPaymentMethod(),
                    'proof_file' => 'payments/' . $paymentType->code . '-' . $application->application_number . '.jpg',
                    'status' => 'verified',
                    'verified_at' => $application->enrolled_at?->subDay(),
                    'verified_by' => User::where('school_id', $school->id)
                        ->whereHas('roles', fn($q) => $q->where('name', 'finance_admin'))
                        ->inRandomOrder()
                        ->first()?->id,
                ]);
            }
        }
    }

    private function generateTransactionCode(School $school, int $sequence): string
    {
        $date = date('Ymd');
        return sprintf('%s-PAY-%s-%04d', $school->code, $date, $sequence);
    }

    /**
     * Create schedule records
     */
    private function createSchedules(Application $application, School $school, string $status): void
    {
        if (!in_array($status, ['interview_scheduled', 'interview_completed', 'payment_pending', 'payment_verified', 'accepted', 'enrolled', 'rejected'])) return;

        $interviewer = User::where('school_id', $school->id)
            ->whereHas('roles', fn($q) => $q->whereIn('name', ['school_admin', 'admission_admin']))
            ->inRandomOrder()
            ->first();

        if (!$interviewer) return;

        // Observation (for Early Years)
        if ($application->requires_observation) {
            $scheduleDate = $application->reviewed_at?->addDays(rand(3, 7));
            $isCompleted = in_array($status, ['interview_completed', 'payment_pending', 'payment_verified', 'accepted', 'enrolled', 'rejected']);

            Schedule::create([
                'application_id' => $application->id,
                'type' => 'observation',
                'scheduled_date' => $scheduleDate,
                'duration_minutes' => 90,
                'interviewer_id' => $interviewer->id,
                'location' => 'Early Years Classroom',
                'is_online' => false,
                'notes' => 'Student will participate in classroom activities. Please bring a snack for your child.',
                'status' => $isCompleted ? 'completed' : 'scheduled',
                'score' => $isCompleted ? rand(70, 95) : null,
                'completed_at' => $isCompleted ? $scheduleDate->addMinutes(90) : null,
                'completed_by' => $isCompleted ? $interviewer->id : null,
                'created_by' => $interviewer->id,
            ]);
        }

        // Test (for Primary and Middle Years)
        if ($application->requires_test) {
            $scheduleDate = $application->reviewed_at?->addDays(rand(5, 10));
            $isCompleted = in_array($status, ['interview_completed', 'payment_pending', 'payment_verified', 'accepted', 'enrolled', 'rejected']);

            Schedule::create([
                'application_id' => $application->id,
                'type' => 'test',
                'scheduled_date' => $scheduleDate,
                'duration_minutes' => 120,
                'interviewer_id' => $interviewer->id,
                'location' => 'Assessment Room 2',
                'is_online' => false,
                'notes' => 'Mathematics and English assessment. Please bring pencils and eraser.',
                'status' => $isCompleted ? 'completed' : 'scheduled',
                'score' => $isCompleted ? rand(65, 95) : null,
                'completed_at' => $isCompleted ? $scheduleDate->addMinutes(120) : null,
                'completed_by' => $isCompleted ? $interviewer->id : null,
                'created_by' => $interviewer->id,
            ]);
        }

        // Interview (all levels)
        if ($application->requires_interview) {
            $scheduleDate = $application->reviewed_at?->addDays(rand(7, 14));
            $isCompleted = in_array($status, ['interview_completed', 'payment_pending', 'payment_verified', 'accepted', 'enrolled', 'rejected']);
            $isOnline = rand(1, 10) > 7; // 30% online

            Schedule::create([
                'application_id' => $application->id,
                'type' => 'interview',
                'scheduled_date' => $scheduleDate,
                'duration_minutes' => 60,
                'interviewer_id' => $interviewer->id,
                'location' => $isOnline ? 'https://zoom.us/j/' . rand(1000000000, 9999999999) : 'Principal Office',
                'is_online' => $isOnline,
                'notes' => 'Parent interview with Principal. Please prepare questions about school curriculum and values.',
                'status' => $isCompleted ? 'completed' : 'scheduled',
                'score' => $isCompleted ? rand(70, 95) : null,
                'completed_at' => $isCompleted ? $scheduleDate->addMinutes(60) : null,
                'completed_by' => $isCompleted ? $interviewer->id : null,
                'created_by' => $interviewer->id,
            ]);
        }
    }

    /**
     * Create medical record
     */
    private function createMedicalRecord(Application $application): void
    {
        if ($application->status === 'draft') return;

        $bloodTypes = ['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-', 'unknown'];

        MedicalRecord::create([
            'application_id' => $application->id,
            'blood_type' => $bloodTypes[array_rand($bloodTypes)],
            'height' => rand(90, 160), // cm based on age
            'weight' => rand(15, 60), // kg based on age
            'has_food_allergies' => rand(1, 10) > 8,
            'food_allergies_details' => rand(1, 10) > 8 ? 'Peanuts, Shellfish' : null,
            'has_medical_conditions' => rand(1, 10) > 9,
            'medical_conditions' => rand(1, 10) > 9 ? $this->getMedicalCondition() : null,
            'requires_daily_medication' => rand(1, 10) > 9,
            'daily_medications' => rand(1, 10) > 9 ? 'As prescribed by family doctor' : null,
            'has_dietary_restrictions' => rand(1, 10) > 8,
            'dietary_restrictions' => rand(1, 10) > 8 ? 'Vegetarian / Halal only' : null,
            'has_special_needs' => !empty($application->special_needs),
            'special_needs_description' => $application->special_needs,
            'requires_learning_support' => !empty($application->learning_support_required),
            'learning_support_details' => $application->learning_support_required,
            'immunizations_up_to_date' => rand(1, 10) > 2, // 80% up to date
            'emergency_contact_name' => 'Emergency Contact - ' . $application->student_last_name,
            'emergency_contact_phone' => $this->generateIndonesianPhone(),
            'emergency_contact_relationship' => rand(0, 1) === 1 ? 'Father' : 'Mother',
            'additional_notes' => rand(1, 10) > 8 ? 'Regular health check-ups completed. No major concerns.' : null,
        ]);
    }

    // ==================== HELPER METHODS ====================

    private function generateApplicationNumber(School $school, int $index): string
    {
        $year = date('y');
        $month = date('m');
        return sprintf('%s-%s%s-%04d', $school->code, $year, $month, $index);
    }

    private function generateIndonesianPhone(): string
    {
        $prefixes = ['0812', '0813', '0821', '0822', '0823', '0852', '0853', '0857'];
        return $prefixes[array_rand($prefixes)] . rand(10000000, 99999999);
    }

    private function generatePassportNumber(): string
    {
        return strtoupper(chr(rand(65, 90)) . rand(10000000, 99999999));
    }

    private function calculateBirthDate(Level $level): Carbon
    {
        // Calculate appropriate birth date based on level's age range
        $targetAge = ($level->min_age + $level->max_age) / 2;
        return now()->subYears((int)$targetAge)->subMonths(rand(0, 11));
    }

    private function getRandomBirthPlace(): string
    {
        $places = ['Jakarta', 'Singapore', 'Hong Kong', 'Seoul', 'Tokyo', 'London', 'Sydney', 'New York', 'Los Angeles', 'Toronto', 'Manila', 'Kuala Lumpur', 'Bangkok', 'Bali', 'Surabaya'];
        return $places[array_rand($places)];
    }

    private function getRandomNationality(): string
    {
        $nationalities = ['Indonesian', 'American', 'British', 'Australian', 'Canadian', 'Singaporean', 'Korean', 'Japanese', 'Chinese', 'Malaysian', 'Filipino', 'Indian', 'French', 'German', 'Dutch'];
        return $nationalities[array_rand($nationalities)];
    }

    private function getRandomCountry(): string
    {
        $countries = ['Singapore', 'Malaysia', 'Thailand', 'Australia', 'United States', 'United Kingdom', 'Canada', 'Japan', 'South Korea', 'China', 'Hong Kong'];
        return $countries[array_rand($countries)];
    }

    private function getCityBySchool(School $school): string
    {
        return match ($school->code) {
            'VIS-BIN' => 'Tangerang Selatan',
            'VIS-KG' => 'Jakarta Utara',
            'VIS-BALI' => 'Denpasar',
            default => 'Jakarta',
        };
    }

    private function getPostalCodeBySchool(School $school): string
    {
        return match ($school->code) {
            'VIS-BIN' => '15224',
            'VIS-KG' => '14240',
            'VIS-BALI' => '80228',
            default => '12345',
        };
    }

    private function getIndonesianAddress(School $school): string
    {
        $streetNumbers = rand(1, 150);

        $addresses = match ($school->code) {
            'VIS-BIN' => [
                "Jl. Bintaro Utama {$streetNumbers} Blok A",
                "Jl. Boulevard Bintaro Jaya {$streetNumbers}",
                "Jl. Taman Bintaro Sektor 9 No. {$streetNumbers}",
                "Jl. Maleo Sektor 7 No. {$streetNumbers}",
            ],
            'VIS-KG' => [
                "Jl. Boulevard Raya Blok RA No. {$streetNumbers}",
                "Jl. Boulevard Timur No. {$streetNumbers}",
                "Jl. Pegangsaan Dua No. {$streetNumbers}",
                "Jl. Raya Kelapa Gading No. {$streetNumbers}",
            ],
            'VIS-BALI' => [
                "Jl. Danau Tamblingan No. {$streetNumbers}, Sanur",
                "Jl. Bypass Ngurah Rai No. {$streetNumbers}",
                "Jl. Pantai Karang No. {$streetNumbers}, Sanur",
                "Jl. Mertasari No. {$streetNumbers}, Sidakarya",
            ],
            default => ["Jl. Example No. {$streetNumbers}"],
        };

        return $addresses[array_rand($addresses)];
    }

    private function getPreviousSchoolName(Level $level): string
    {
        if (in_array($level->code, ['EP', 'PS'])) {
            $schools = ['Sunshine Preschool', 'Little Stars Kindergarten', 'Happy Kids Daycare', 'Bright Beginnings Early Learning', 'Rainbow Nursery School'];
        } elseif (in_array($level->code, ['PK', 'G1', 'G2'])) {
            $schools = ['International Kindergarten Jakarta', 'Global Preschool', 'Worldwide Kids School', 'Premier Early Years', 'Elite Kindergarten'];
        } else {
            $schools = ['Jakarta International School', 'Singapore International School', 'British International School', 'Australian International School', 'Canadian International School', 'Local Public School Jakarta'];
        }
        return $schools[array_rand($schools)];
    }

    private function getCurrentGradeLevel(Level $level): string
    {
        $mapping = [
            'EP' => 'Daycare',
            'PS' => 'Nursery',
            'PK' => 'Kindergarten',
            'G1' => 'Kindergarten',
            'G2' => 'Grade 1',
            'G3' => 'Grade 2',
            'G4' => 'Grade 3',
            'G5' => 'Grade 4',
            'G6' => 'Grade 5',
            'G7' => 'Grade 6',
            'G8' => 'Grade 7',
            'G9' => 'Grade 8',
        ];
        return $mapping[$level->code] ?? 'Not applicable';
    }

    private function getLanguages(): string
    {
        $combinations = [
            'English, Indonesian',
            'English, Mandarin, Indonesian',
            'English, Korean',
            'English, Japanese, Indonesian',
            'English, Spanish, Indonesian',
            'English, French',
            'English, Indonesian, Malay',
            'English only',
            'English, Tamil, Indonesian',
            'English, Arabic, Indonesian',
        ];
        return $combinations[array_rand($combinations)];
    }

    private function getInterests(): string
    {
        $interests = [
            'Reading, Swimming, Piano',
            'Soccer, Basketball, Coding',
            'Art, Dance, Music',
            'Science experiments, Robotics, Math puzzles',
            'Swimming, Tennis, Chess',
            'Drawing, Painting, Crafts',
            'Drama, Singing, Public speaking',
            'Football, Cricket, Video games',
            'Gymnastics, Ballet, Ice skating',
            'Cooking, Gardening, Animal care',
        ];
        return $interests[array_rand($interests)];
    }

    private function getSpecialNeeds(): string
    {
        $needs = [
            'Wears glasses for myopia',
            'Mild asthma - requires inhaler',
            'Food allergies - peanuts and shellfish',
            'ADHD - under medication and monitoring',
            'Dyslexia - requires learning support',
            'Autism Spectrum - high functioning, needs structured environment',
        ];
        return $needs[array_rand($needs)];
    }

    private function getLearningSupport(): string
    {
        $support = [
            'Extra time for tests and assignments',
            'Preferential seating in classroom',
            'Multisensory learning approach',
            'Regular breaks during lessons',
            'Visual aids and written instructions',
            'Small group instruction for core subjects',
        ];
        return $support[array_rand($support)];
    }

    private function getWorkplace(): string
    {
        $workplaces = [
            'Google Indonesia',
            'Microsoft Asia Pacific',
            'Citibank Jakarta',
            'Unilever Indonesia',
            'Deloitte Consulting',
            'McKinsey & Company',
            'PT Astra International',
            'Bank Mandiri',
            'Telkom Indonesia',
            'Self-employed / Own Business',
            'International School Jakarta',
            'Siemens Indonesia',
            'Samsung Electronics',
            'Shell Indonesia',
            'Ministry of Finance',
        ];
        return $workplaces[array_rand($workplaces)];
    }

    private function getRandomPaymentMethod(): string
    {
        $methods = ['bank_transfer', 'virtual_account', 'credit_card', 'debit_card'];
        return $methods[array_rand($methods)];
    }

    private function getStatusNotes(string $status): ?string
    {
        return match ($status) {
            'submitted' => 'Application received and awaiting initial review',
            'under_review' => 'Documents being verified by admission team',
            'documents_verified' => 'All documents verified successfully',
            'interview_scheduled' => 'Interview appointment has been scheduled',
            'interview_completed' => 'Interview completed, awaiting final decision',
            'payment_pending' => 'Waiting for payment confirmation',
            'payment_verified' => 'Payment confirmed, preparing enrollment',
            'accepted' => 'Congratulations! Student accepted to VIS',
            'enrolled' => 'Enrollment completed successfully',
            'rejected' => 'Application not successful at this time',
            default => null,
        };
    }

    private function getMedicalCondition(): string
    {
        $conditions = [
            'Asthma - well-controlled with medication',
            'Eczema - periodic flare-ups',
            'Seasonal allergies',
            'Minor hearing impairment - uses hearing aid',
        ];
        return $conditions[array_rand($conditions)];
    }
}
