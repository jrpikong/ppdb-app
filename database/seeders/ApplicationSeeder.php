<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Application, Enrollment, School, AcademicYear, AdmissionPeriod, Level, User, ParentGuardian, Document, Payment, PaymentType, Schedule, MedicalRecord};
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ApplicationSeeder extends Seeder
{
    /**
     * 45 unique student names — 15 per school (keyed by school index 0/1/2).
     * Prevents duplicate student names within a single school's admission period.
     */
    private array $studentNames = [
        // ── VIS BINTARO (schoolIndex 0, indices 0-14) ────────────────────
        ['first' => 'Ethan',     'middle' => 'James',     'last' => 'Thompson',  'preferred' => 'Ethan',   'gender' => 'male'],
        ['first' => 'Ava',       'middle' => 'Grace',     'last' => 'Martinez',  'preferred' => 'Ava',     'gender' => 'female'],
        ['first' => 'Noah',      'middle' => 'Patrick',   'last' => "O'Brien",   'preferred' => 'Noah',    'gender' => 'male'],
        ['first' => 'Chloe',     'middle' => 'Min-Ji',    'last' => 'Kim',       'preferred' => 'Chloe',   'gender' => 'female'],
        ['first' => 'Liam',      'middle' => 'Raj',       'last' => 'Patel',     'preferred' => 'Liam',    'gender' => 'male'],
        ['first' => 'Sophie',    'middle' => 'Mei Ling',  'last' => 'Tan',       'preferred' => 'Sophie',  'gender' => 'female'],
        ['first' => 'Lucas',     'middle' => 'William',   'last' => 'Anderson',  'preferred' => 'Luke',    'gender' => 'male'],
        ['first' => 'Emily',     'middle' => 'Isabella',  'last' => 'Rodriguez', 'preferred' => 'Emily',   'gender' => 'female'],
        ['first' => 'Mason',     'middle' => 'Wei',       'last' => 'Chen',      'preferred' => 'Mason',   'gender' => 'male'],
        ['first' => 'Amelia',    'middle' => 'Rose',      'last' => 'Wilson',    'preferred' => 'Amy',     'gender' => 'female'],
        ['first' => 'Aiden',     'middle' => 'Arjun',     'last' => 'Singh',     'preferred' => 'Aiden',   'gender' => 'male'],
        ['first' => 'Lily',      'middle' => 'Yeon',      'last' => 'Lee',       'preferred' => 'Lily',    'gender' => 'female'],
        ['first' => 'Benjamin',  'middle' => 'Minh',      'last' => 'Nguyen',    'preferred' => 'Ben',     'gender' => 'male'],
        ['first' => 'Charlotte', 'middle' => 'Sofia',     'last' => 'Garcia',    'preferred' => 'Charlie', 'gender' => 'female'],
        ['first' => 'Henry',     'middle' => 'Alexander', 'last' => 'Brown',     'preferred' => 'Henry',   'gender' => 'male'],

        // ── VIS KELAPA GADING (schoolIndex 1, indices 15-29) ─────────────
        ['first' => 'Oliver',    'middle' => 'James',     'last' => 'Davis',     'preferred' => 'Oliver',  'gender' => 'male'],
        ['first' => 'Ella',      'middle' => 'Grace',     'last' => 'Thompson',  'preferred' => 'Ella',    'gender' => 'female'],
        ['first' => 'James',     'middle' => 'Patrick',   'last' => 'Williams',  'preferred' => 'Jamie',   'gender' => 'male'],
        ['first' => 'Grace',     'middle' => 'Min-Seo',   'last' => 'Kim',       'preferred' => 'Grace',   'gender' => 'female'],
        ['first' => 'Sebastian', 'middle' => 'Andrew',    'last' => 'Park',      'preferred' => 'Seb',     'gender' => 'male'],
        ['first' => 'Victoria',  'middle' => 'Mei',       'last' => 'Lee',       'preferred' => 'Vicky',   'gender' => 'female'],
        ['first' => 'Elijah',    'middle' => 'Wei',       'last' => 'Chen',      'preferred' => 'Eli',     'gender' => 'male'],
        ['first' => 'Zoe',       'middle' => 'Sofia',     'last' => 'Martinez',  'preferred' => 'Zoe',     'gender' => 'female'],
        ['first' => 'Alexander', 'middle' => 'James',     'last' => 'Brown',     'preferred' => 'Alex',    'gender' => 'male'],
        ['first' => 'Mia',       'middle' => 'Rose',      'last' => 'Patel',     'preferred' => 'Mia',     'gender' => 'female'],
        ['first' => 'Jayden',    'middle' => 'Minh',      'last' => 'Nguyen',    'preferred' => 'Jay',     'gender' => 'male'],
        ['first' => 'Luna',      'middle' => 'Sofia',     'last' => 'Garcia',    'preferred' => 'Luna',    'gender' => 'female'],
        ['first' => 'Daniel',    'middle' => 'Alexander', 'last' => 'Smith',     'preferred' => 'Dan',     'gender' => 'male'],
        ['first' => 'Aria',      'middle' => 'Isabella',  'last' => 'Johnson',   'preferred' => 'Aria',    'gender' => 'female'],
        ['first' => 'William',   'middle' => 'James',     'last' => 'Anderson',  'preferred' => 'Will',    'gender' => 'male'],

        // ── VIS BALI (schoolIndex 2, indices 30-44) ──────────────────────
        ['first' => 'Jack',      'middle' => 'Owen',      'last' => 'Wilson',    'preferred' => 'Jack',    'gender' => 'male'],
        ['first' => 'Hannah',    'middle' => 'Rose',      'last' => 'Lee',       'preferred' => 'Hannah',  'gender' => 'female'],
        ['first' => 'Leo',       'middle' => 'James',     'last' => 'Martinez',  'preferred' => 'Leo',     'gender' => 'male'],
        ['first' => 'Isla',      'middle' => 'Grace',     'last' => 'Chen',      'preferred' => 'Isla',    'gender' => 'female'],
        ['first' => 'Matthew',   'middle' => 'Patrick',   'last' => 'Rodriguez', 'preferred' => 'Matt',    'gender' => 'male'],
        ['first' => 'Evelyn',    'middle' => 'Min-Ji',    'last' => 'Kim',       'preferred' => 'Evie',    'gender' => 'female'],
        ['first' => 'Ryan',      'middle' => 'Alexander', 'last' => 'Johnson',   'preferred' => 'Ryan',    'gender' => 'male'],
        ['first' => 'Scarlett',  'middle' => 'Isabella',  'last' => 'Davis',     'preferred' => 'Scar',    'gender' => 'female'],
        ['first' => 'Nathan',    'middle' => 'Arjun',     'last' => 'Brown',     'preferred' => 'Nate',    'gender' => 'male'],
        ['first' => 'Sofia',     'middle' => 'Mei',       'last' => 'Park',      'preferred' => 'Sofia',   'gender' => 'female'],
        ['first' => 'Luke',      'middle' => 'William',   'last' => 'Thompson',  'preferred' => 'Luke',    'gender' => 'male'],
        ['first' => 'Penelope',  'middle' => 'Grace',     'last' => 'White',     'preferred' => 'Penny',   'gender' => 'female'],
        ['first' => 'Tyler',     'middle' => 'James',     'last' => 'Garcia',    'preferred' => 'Tyler',   'gender' => 'male'],
        ['first' => 'Hazel',     'middle' => 'Rose',      'last' => 'Anderson',  'preferred' => 'Hazel',   'gender' => 'female'],
        ['first' => 'Caleb',     'middle' => 'Patrick',   'last' => 'Smith',     'preferred' => 'Cal',     'gender' => 'male'],
    ];

    /**
     * Seed realistic VIS application data with complete student information.
     * Creates 15 applications per school (45 total) covering all status workflows.
     */
    public function run(): void
    {
        $this->command->info('📝 Creating Student Applications with Complete Data...');

        DB::transaction(function () {
            // ✅ Order by ID for consistent school → name-group assignment
            $schools = School::orderBy('id')->get();

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
            $parentIndex  = 0;
            $schoolCounter = 0; // Tracks school index for unique student-name groups

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
                    $schoolCounter++;
                    continue;
                }

                $levels = Level::where('school_id', $school->id)->get();

                // ✅ Hard-coded 15 per school — ensures all statuses are represented
                $applicationsPerSchool = 15;

                for ($i = 1; $i <= $applicationsPerSchool; $i++) {
                    // Cycle through parents — each parent gets ~2 apps across schools
                    $parent = $parentUsers[$parentIndex % $parentUsers->count()];
                    $parentIndex++;

                    $level  = $levels->random();
                    $status = $this->getStatusByIndex($i);

                    $application = $this->createApplication(
                        $school,
                        $academicYear,
                        $admissionPeriod,
                        $level,
                        $parent,
                        $status,
                        $i,
                        $schoolCounter
                    );

                    $this->createParentGuardians($application);
                    $this->createDocuments($application, $status);
                    $this->createPayments($application, $school, $status);
                    $this->createSchedules($application, $school, $status);
                    $this->createMedicalRecord($application);

                    // ✅ Create Enrollment record for fully enrolled applications
                    if ($status === 'enrolled') {
                        $this->createEnrollment($application, $school);
                    }

                    $totalCreated++;
                }

                $schoolCounter++;
            }

            $this->command->info("  ✓ Created {$totalCreated} complete applications");
        });
    }

    /**
     * Status distribution across 15 applications per school.
     * All 13 valid statuses are represented for realistic variety.
     */
    private function getStatusByIndex(int $index): string
    {
        return match (true) {
            $index === 1              => 'draft',               // 1 — incomplete draft
            $index <= 3              => 'submitted',            // 2,3 — awaiting review
            $index <= 5              => 'under_review',         // 4,5 — being reviewed
            $index === 6             => 'documents_verified',   // 6 — docs approved
            $index === 7             => 'interview_scheduled',  // 7 — interview set
            $index === 8             => 'interview_completed',  // 8 — interview done
            $index === 9             => 'payment_pending',      // 9 — payment awaited
            $index === 10            => 'payment_verified',     // 10 — payment confirmed
            $index === 11            => 'accepted',             // 11 — accepted
            $index === 12            => 'enrolled',             // 12 — fully enrolled
            $index === 13            => 'rejected',             // 13 — not successful
            $index === 14            => 'waitlisted',           // 14 — on waitlist
            $index === 15            => 'withdrawn',            // 15 — parent withdrew
            default                  => 'submitted',
        };
    }

    /**
     * Create application with realistic student data.
     * Uses school-specific student names (15 per school) to avoid duplicate names
     * within the same school's admission period (enforced by DB unique constraint).
     */
    private function createApplication(
        School $school,
        AcademicYear $academicYear,
        AdmissionPeriod $admissionPeriod,
        Level $level,
        User $parent,
        string $status,
        int $index,
        int $schoolIndex = 0
    ): Application {
        // Pick the student from the school-specific name block (15 names per school)
        $nameIndex = ($schoolIndex * 15) + ($index - 1);
        $student   = $this->studentNames[$nameIndex] ?? $this->studentNames[0];

        $birthDate = $this->calculateBirthDate($level);

        $applicationNumber = $this->generateApplicationNumber($school, $index);

        // Timestamps — build a realistic chronological chain
        $submittedAt = $status === 'draft' ? null : now()->subDays(rand(5, 30));

        $reviewedAt = in_array($status, [
            'under_review', 'documents_verified', 'interview_scheduled',
            'interview_completed', 'payment_pending', 'payment_verified',
            'accepted', 'enrolled', 'rejected', 'waitlisted',
        ], true)
            ? $submittedAt?->copy()->addDays(rand(2, 5))
            : null;

        $decisionMadeAt = in_array($status, ['accepted', 'enrolled', 'rejected', 'waitlisted'], true)
            ? $reviewedAt?->copy()->addDays(rand(3, 7))
            : null;

        $enrolledAt = $status === 'enrolled'
            ? $decisionMadeAt?->copy()->addDays(rand(1, 3))
            : null;

        // Assign reviewer for all reviewed/decided/active statuses
        $assignedTo = in_array($status, [
            'under_review', 'documents_verified', 'interview_scheduled',
            'interview_completed', 'payment_pending', 'payment_verified',
            'accepted', 'enrolled', 'rejected', 'waitlisted',
        ], true)
            ? User::where('school_id', $school->id)
                ->whereHas('roles', fn($q) => $q->whereIn('name', ['school_admin', 'admission_admin']))
                ->inRandomOrder()
                ->first()?->id
            : null;

        $reviewedBy = $reviewedAt ? $assignedTo : null;

        // Sanitise email: remove apostrophes and non-alphanumeric chars
        $emailFirst = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $student['first']));
        $emailLast  = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $student['last']));

        return Application::create([
            'user_id'             => $parent->id,
            'school_id'           => $school->id,
            'academic_year_id'    => $academicYear->id,
            'admission_period_id' => $admissionPeriod->id,
            'level_id'            => $level->id,
            'application_number'  => $applicationNumber,

            // Student Information
            'student_first_name'    => $student['first'],
            'student_middle_name'   => $student['middle'],
            'student_last_name'     => $student['last'],
            'student_preferred_name'=> $student['preferred'],
            'gender'                => $student['gender'],
            'birth_date'            => $birthDate,
            'birth_place'           => $this->getRandomBirthPlace(),
            'nationality'           => $this->getRandomNationality(),
            'passport_number'       => $this->generatePassportNumber(),

            // Contact
            'email' => "{$emailFirst}.{$emailLast}@student.vis.sch.id",
            'phone' => $this->generateIndonesianPhone(),

            // Address
            'current_address'     => $this->getIndonesianAddress($school),
            'current_city'        => $this->getCityBySchool($school),
            'current_country'     => 'Indonesia',
            'current_postal_code' => $this->getPostalCodeBySchool($school),

            // Previous School
            'previous_school_name'       => $this->getPreviousSchoolName($level),
            'previous_school_country'    => rand(1, 10) > 7 ? $this->getRandomCountry() : 'Indonesia',
            'current_grade_level'        => $this->getCurrentGradeLevel($level),
            'previous_school_start_date' => $birthDate->copy()->addYears(3),
            'previous_school_end_date'   => now()->subMonths(3),

            // Educational profile
            'languages_spoken'      => $this->getLanguages(),
            'interests_hobbies'     => $this->getInterests(),

            // Special Needs (rare)
            'special_needs'             => rand(1, 10) > 8 ? $this->getSpecialNeeds() : null,
            'learning_support_required' => rand(1, 10) > 9 ? $this->getLearningSupport() : null,

            // Assessment requirements by level
            'requires_observation' => in_array($level->code, ['EP', 'PS', 'PK']),
            'requires_test'        => in_array($level->code, ['G1', 'G2', 'G3', 'G4', 'G5', 'G6', 'G7', 'G8', 'G9']),
            'requires_interview'   => true,

            // Workflow
            'status'          => $status,
            'status_notes'    => $this->getStatusNotes($status),
            'submitted_at'    => $submittedAt,
            'reviewed_at'     => $reviewedAt,
            'reviewed_by'     => $reviewedBy,
            'decision_made_at'=> $decisionMadeAt,
            'enrolled_at'     => $enrolledAt,
            'assigned_to'     => $assignedTo,
        ]);
    }

    /**
     * Create parent/guardian records for an application.
     */
    private function createParentGuardians(Application $application): void
    {
        $fatherFirstNames = ['John', 'Michael', 'David', 'Robert', 'James', 'William', 'Thomas', 'Richard', 'Charles', 'Daniel', 'Paul', 'Mark', 'Steven', 'Andrew', 'Peter'];
        $motherFirstNames = ['Mary', 'Jennifer', 'Sarah', 'Lisa', 'Michelle', 'Amanda', 'Jessica', 'Emily', 'Emma', 'Sophia', 'Isabella', 'Mia', 'Charlotte', 'Amelia', 'Olivia'];

        $occupations = [
            'Software Engineer', 'Doctor', 'Lawyer', 'Business Owner', 'Teacher',
            'Accountant', 'Marketing Manager', 'Architect', 'Consultant', 'Entrepreneur',
            'Financial Analyst', 'Project Manager', 'Designer', 'Engineer', 'Director',
        ];

        $parentUser = $application->user;
        $lastName   = $application->student_last_name;

        // Father
        $fatherFirstName = $fatherFirstNames[array_rand($fatherFirstNames)];
        ParentGuardian::create([
            'application_id'      => $application->id,
            'type'                => 'father',
            'first_name'          => $fatherFirstName,
            'last_name'           => $lastName,
            'email'               => 'father.' . strtolower(preg_replace('/[^a-z]/i', '', $lastName)) . rand(100, 999) . '@email.com',
            'phone'               => $parentUser->phone ?? $this->generateIndonesianPhone(),
            'mobile'              => $this->generateIndonesianPhone(),
            'nationality'         => $application->nationality,
            'occupation'          => $occupations[array_rand($occupations)],
            'company_name'        => $this->getWorkplace(),
            'address'             => $application->current_address,
            'city'                => $application->current_city,
            'country'             => $application->current_country,
            'postal_code'         => $application->current_postal_code,
            'is_primary_contact'  => false,
            'is_emergency_contact'=> true,
        ]);

        // Mother — uses parent user's email as primary contact
        $motherFirstName = $motherFirstNames[array_rand($motherFirstNames)];
        ParentGuardian::create([
            'application_id'      => $application->id,
            'type'                => 'mother',
            'first_name'          => $motherFirstName,
            'last_name'           => $lastName,
            'email'               => $parentUser->email,
            'phone'               => $this->generateIndonesianPhone(),
            'mobile'              => $parentUser->phone ?? $this->generateIndonesianPhone(),
            'nationality'         => $application->nationality,
            'occupation'          => $occupations[array_rand($occupations)],
            'company_name'        => $this->getWorkplace(),
            'address'             => $application->current_address,
            'city'                => $application->current_city,
            'country'             => $application->current_country,
            'postal_code'         => $application->current_postal_code,
            'is_primary_contact'  => true,
            'is_emergency_contact'=> true,
        ]);
    }

    /**
     * Create document records for an application.
     * Includes all required document types (per Application::hasAllRequiredDocuments).
     */
    private function createDocuments(Application $application, string $status): void
    {
        if ($status === 'draft') {
            return; // Draft applications have no documents yet
        }

        $documentTypes = [
            'birth_certificate'   => 'Birth Certificate',
            'passport'            => 'Passport Copy',
            'family_card'         => 'Family Card (Kartu Keluarga)',
            'student_photo_1'     => 'Student Photo 3x4 (First)',
            'student_photo_2'     => 'Student Photo 3x4 (Second)',
            'father_photo'        => 'Father Photo 3x4',
            'mother_photo'        => 'Mother Photo 3x4',
            'father_id_card'      => 'Father ID Card (KTP)',
            'mother_id_card'      => 'Mother ID Card (KTP)',
            'latest_report_book'  => 'Latest School Report Card',
            'medical_history'     => 'Health Certificate',
            'immunization_record' => 'Immunization Record',
        ];

        foreach ($documentTypes as $type => $name) {
            $verificationStatus = match ($status) {
                'submitted', 'withdrawn'  => 'pending',
                'under_review'            => rand(1, 10) > 5 ? 'pending' : 'approved',
                'documents_verified', 'interview_scheduled', 'interview_completed',
                'payment_pending', 'payment_verified', 'accepted', 'enrolled',
                'waitlisted'              => 'approved',
                'rejected'               => rand(1, 10) > 7 ? 'rejected' : 'approved',
                default                   => 'pending',
            };

            $verifiedAt = $verificationStatus === 'approved'
                ? now()->subDays(rand(1, 10))
                : null;

            $verifiedBy = $verificationStatus === 'approved'
                ? User::where('school_id', $application->school_id)
                    ->whereHas('roles', fn($q) => $q->whereIn('name', ['school_admin', 'admission_admin']))
                    ->inRandomOrder()
                    ->first()?->id
                : null;

            Document::create([
                'application_id'     => $application->id,
                'type'               => $type,
                'name'               => $name . ' - ' . $application->student_first_name . ' ' . $application->student_last_name . '.pdf',
                'file_path'          => 'documents/' . Str::slug($name) . '-' . $application->application_number . '.pdf',
                'file_type'          => 'application/pdf',
                'file_size'          => rand(500000, 2000000), // 500 KB – 2 MB
                'status'             => $verificationStatus,
                'verification_notes' => $verificationStatus === 'approved' ? 'Document verified and approved' : null,
                'verified_at'        => $verifiedAt,
                'verified_by'        => $verifiedBy,
            ]);
        }
    }

    /**
     * Create payment records based on application status stage.
     */
    private function createPayments(Application $application, School $school, string $status): void
    {
        if (in_array($status, ['draft', 'submitted'], true)) {
            return;
        }

        $paymentTypes = PaymentType::where('school_id', $school->id)->get();

        // ── Pre-submission: Saving Seat ────────────────────────────────────
        // Required before submission — present for all submitted+ applications
        // including withdrawn (parent paid before withdrawing)
        if (in_array($status, [
            'under_review', 'documents_verified', 'interview_scheduled',
            'interview_completed', 'payment_pending', 'payment_verified',
            'accepted', 'enrolled', 'rejected', 'waitlisted', 'withdrawn',
        ], true)) {
            $savingSeatType = $paymentTypes->where('code', 'SAVING_SEAT')->first();

            if ($savingSeatType) {
                Payment::create([
                    'application_id'   => $application->id,
                    'payment_type_id'  => $savingSeatType->id,
                    'transaction_code' => $this->generateTransactionCode($school, $application->id, 1),
                    'amount'           => $savingSeatType->amount,
                    'currency'         => 'IDR',
                    'payment_date'     => $application->submitted_at?->addDay() ?? now(),
                    'payment_method'   => $this->getRandomPaymentMethod(),
                    'proof_file'       => 'payments/saving-seat-' . $application->application_number . '.jpg',
                    'status'           => 'verified',
                    'verified_at'      => $application->submitted_at?->addDays(2),
                    'verified_by'      => User::where('school_id', $school->id)
                        ->whereHas('roles', fn($q) => $q->where('name', 'finance_admin'))
                        ->inRandomOrder()
                        ->first()?->id,
                ]);
            }
        }

        // ── Post-acceptance: Registration + Development ───────────────────
        if (in_array($status, ['payment_pending', 'payment_verified', 'accepted', 'enrolled'], true)) {
            $postAcceptanceTypes = $paymentTypes->whereIn('code', ['REGISTRATION', 'DEVELOPMENT'])->values();

            foreach ($postAcceptanceTypes as $index => $paymentType) {
                $isPaid = in_array($status, ['payment_verified', 'accepted', 'enrolled'], true);

                Payment::create([
                    'application_id'   => $application->id,
                    'payment_type_id'  => $paymentType->id,
                    'transaction_code' => $this->generateTransactionCode($school, $application->id, $index + 2),
                    'amount'           => $paymentType->amount,
                    'currency'         => 'IDR',
                    'payment_date'     => $isPaid ? ($application->decision_made_at?->addDays(rand(1, 3)) ?? now()) : now(),
                    'payment_method'   => $this->getRandomPaymentMethod(),
                    'proof_file'       => $isPaid ? 'payments/' . $paymentType->code . '-' . $application->application_number . '.jpg' : null,
                    'status'           => $isPaid ? 'verified' : 'pending',
                    'verified_at'      => $isPaid ? ($application->decision_made_at?->addDays(rand(2, 5))) : null,
                    'verified_by'      => $isPaid
                        ? User::where('school_id', $school->id)
                            ->whereHas('roles', fn($q) => $q->where('name', 'finance_admin'))
                            ->inRandomOrder()
                            ->first()?->id
                        : null,
                ]);
            }
        }

        // ── Enrollment: Uniform + Books + Technology ──────────────────────
        if ($status === 'enrolled') {
            $enrollmentTypes = $paymentTypes->whereIn('code', ['UNIFORM', 'BOOKS', 'TECHNOLOGY'])->values();

            foreach ($enrollmentTypes as $index => $paymentType) {
                Payment::create([
                    'application_id'   => $application->id,
                    'payment_type_id'  => $paymentType->id,
                    'transaction_code' => $this->generateTransactionCode($school, $application->id, $index + 5),
                    'amount'           => $paymentType->amount,
                    'currency'         => 'IDR',
                    'payment_date'     => $application->enrolled_at?->subDays(rand(1, 2)) ?? now(),
                    'payment_method'   => $this->getRandomPaymentMethod(),
                    'proof_file'       => 'payments/' . $paymentType->code . '-' . $application->application_number . '.jpg',
                    'status'           => 'verified',
                    'verified_at'      => $application->enrolled_at?->subDay(),
                    'verified_by'      => User::where('school_id', $school->id)
                        ->whereHas('roles', fn($q) => $q->where('name', 'finance_admin'))
                        ->inRandomOrder()
                        ->first()?->id,
                ]);
            }
        }
    }

    private function generateTransactionCode(School $school, int $applicationId, int $sequence): string
    {
        $date = now()->format('Ymd');
        return sprintf('%s-PAY-%s-%04d-%02d', $school->code, $date, $applicationId, $sequence);
    }

    /**
     * Create schedule records for interview/test/observation stages.
     */
    private function createSchedules(Application $application, School $school, string $status): void
    {
        if (!in_array($status, [
            'interview_scheduled', 'interview_completed',
            'payment_pending', 'payment_verified',
            'accepted', 'enrolled', 'rejected', 'waitlisted',
        ], true)) {
            return;
        }

        $interviewer = User::where('school_id', $school->id)
            ->whereHas('roles', fn($q) => $q->whereIn('name', ['school_admin', 'admission_admin']))
            ->inRandomOrder()
            ->first();

        if (!$interviewer) {
            return;
        }

        $isCompleted = in_array($status, [
            'interview_completed', 'payment_pending', 'payment_verified',
            'accepted', 'enrolled', 'rejected', 'waitlisted',
        ], true);

        // Observation (Early Years only)
        if ($application->requires_observation) {
            $scheduleDate = $application->reviewed_at?->copy()->addDays(rand(3, 7));

            Schedule::create([
                'application_id'  => $application->id,
                'type'            => 'observation',
                'scheduled_date'  => $scheduleDate,
                'scheduled_time'  => '09:00:00',
                'duration_minutes'=> 90,
                'interviewer_id'  => $interviewer->id,
                'location'        => 'Early Years Classroom',
                'is_online'       => false,
                'notes'           => 'Student will participate in classroom activities. Please bring a snack for your child.',
                'status'          => $isCompleted ? 'completed' : 'scheduled',
                'score'           => $isCompleted ? rand(70, 95) : null,
                'completed_at'    => $isCompleted ? $scheduleDate?->copy()->addMinutes(90) : null,
                'completed_by'    => $isCompleted ? $interviewer->id : null,
                'created_by'      => $interviewer->id,
            ]);
        }

        // Written Test (Primary & Middle Years)
        if ($application->requires_test) {
            $scheduleDate = $application->reviewed_at?->copy()->addDays(rand(5, 10));

            Schedule::create([
                'application_id'  => $application->id,
                'type'            => 'test',
                'scheduled_date'  => $scheduleDate,
                'scheduled_time'  => '10:00:00',
                'duration_minutes'=> 120,
                'interviewer_id'  => $interviewer->id,
                'location'        => 'Assessment Room 2',
                'is_online'       => false,
                'notes'           => 'Mathematics and English assessment. Please bring pencils and eraser.',
                'status'          => $isCompleted ? 'completed' : 'scheduled',
                'score'           => $isCompleted ? rand(65, 95) : null,
                'completed_at'    => $isCompleted ? $scheduleDate?->copy()->addMinutes(120) : null,
                'completed_by'    => $isCompleted ? $interviewer->id : null,
                'created_by'      => $interviewer->id,
            ]);
        }

        // Parent Interview (all levels)
        if ($application->requires_interview) {
            $scheduleDate = $application->reviewed_at?->copy()->addDays(rand(7, 14));
            $isOnline     = rand(1, 10) > 7; // 30% online

            Schedule::create([
                'application_id'  => $application->id,
                'type'            => 'interview',
                'scheduled_date'  => $scheduleDate,
                'scheduled_time'  => '13:00:00',
                'duration_minutes'=> 60,
                'interviewer_id'  => $interviewer->id,
                'location'        => $isOnline
                    ? 'https://zoom.us/j/' . rand(100000000, 999999999)
                    : 'Principal Office',
                'is_online'       => $isOnline,
                'notes'           => 'Parent interview with Principal. Please prepare questions about school curriculum and values.',
                'status'          => $isCompleted ? 'completed' : 'scheduled',
                'score'           => $isCompleted ? rand(70, 95) : null,
                'completed_at'    => $isCompleted ? $scheduleDate?->copy()->addMinutes(60) : null,
                'completed_by'    => $isCompleted ? $interviewer->id : null,
                'created_by'      => $interviewer->id,
            ]);
        }
    }

    /**
     * Create medical record for the application.
     */
    private function createMedicalRecord(Application $application): void
    {
        if ($application->status === 'draft') {
            return;
        }

        $bloodTypes              = ['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-', 'unknown'];
        $hasFoodAllergies        = rand(1, 10) > 8;
        $hasMedicalConditions    = rand(1, 10) > 9;
        $requiresDailyMedication = $hasMedicalConditions && rand(1, 10) > 6;
        $hasDietaryRestrictions  = rand(1, 10) > 8;
        $immunizationsUpToDate   = rand(1, 10) > 2; // 80% up-to-date

        MedicalRecord::create([
            'application_id'          => $application->id,
            'blood_type'              => $bloodTypes[array_rand($bloodTypes)],
            'height'                  => rand(90, 160),
            'weight'                  => rand(15, 60),
            'has_food_allergies'      => $hasFoodAllergies,
            'food_allergies_details'  => $hasFoodAllergies ? 'Peanuts, Shellfish' : null,
            'has_medical_conditions'  => $hasMedicalConditions,
            'medical_conditions'      => $hasMedicalConditions ? $this->getMedicalCondition() : null,
            'requires_daily_medication'  => $requiresDailyMedication,
            'daily_medications'          => $requiresDailyMedication ? 'As prescribed by family doctor' : null,
            'has_dietary_restrictions'   => $hasDietaryRestrictions,
            'dietary_restrictions'       => $hasDietaryRestrictions ? 'Vegetarian / Halal only' : null,
            'has_special_needs'          => !empty($application->special_needs),
            'special_needs_description'  => $application->special_needs,
            'requires_learning_support'  => !empty($application->learning_support_required),
            'learning_support_details'   => $application->learning_support_required,
            'immunizations_up_to_date'   => $immunizationsUpToDate,
            'emergency_contact_name'     => 'Emergency Contact - ' . $application->student_last_name,
            'emergency_contact_phone'    => $this->generateIndonesianPhone(),
            'emergency_contact_relationship' => rand(0, 1) === 1 ? 'Father' : 'Mother',
            'additional_notes'           => rand(1, 10) > 8
                ? 'Regular health check-ups completed. No major concerns.'
                : null,
        ]);
    }

    /**
     * Create Enrollment record for fully enrolled applications.
     */
    private function createEnrollment(Application $application, School $school): void
    {
        $enrolledBy = User::where('school_id', $school->id)
            ->whereHas('roles', fn($q) => $q->whereIn('name', ['school_admin', 'admission_admin']))
            ->inRandomOrder()
            ->first();

        // Total one-time enrollment fees (all stages combined):
        // SAVING_SEAT 2.5M + REGISTRATION 5M + DEVELOPMENT 10M + UNIFORM 3.5M + BOOKS 4M + TECHNOLOGY 2M = 27M
        $totalFees = 27_000_000;

        $level = Level::find($application->level_id);

        Enrollment::create([
            'application_id'    => $application->id,
            'enrollment_date'   => $application->enrolled_at ?? now(),
            'start_date'        => Carbon::create(now()->year, 7, 14), // Academic year starts July
            'class_name'        => $level?->name ?? 'TBD',
            'homeroom_teacher'  => $this->getHomroomTeacher(),
            'total_amount_due'  => $totalFees,
            'total_amount_paid' => $totalFees,
            'balance'           => 0,
            'payment_status'    => 'paid',
            'status'            => 'enrolled',
            'enrolled_by'       => $enrolledBy?->id,
            'notes'             => 'Enrollment completed successfully. Welcome to the VIS family!',
        ]);
    }

    // ==================== HELPER METHODS ====================

    private function generateApplicationNumber(School $school, int $index): string
    {
        // Use 4-digit year consistent with Application::generateApplicationNumber()
        return sprintf('%s-%d-%04d', $school->code, now()->year, $index);
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
        $targetAge = ((float) $level->age_min + (float) $level->age_max) / 2;
        return now()->subYears((int) $targetAge)->subMonths(rand(0, 11));
    }

    private function getRandomBirthPlace(): string
    {
        $places = [
            'Jakarta', 'Singapore', 'Hong Kong', 'Seoul', 'Tokyo',
            'London', 'Sydney', 'New York', 'Toronto', 'Manila',
            'Kuala Lumpur', 'Bangkok', 'Bali', 'Surabaya', 'Bandung',
        ];
        return $places[array_rand($places)];
    }

    private function getRandomNationality(): string
    {
        $nationalities = [
            'Indonesian', 'American', 'British', 'Australian', 'Canadian',
            'Singaporean', 'Korean', 'Japanese', 'Chinese', 'Malaysian',
            'Filipino', 'Indian', 'French', 'German', 'Dutch',
        ];
        return $nationalities[array_rand($nationalities)];
    }

    private function getRandomCountry(): string
    {
        $countries = [
            'Singapore', 'Malaysia', 'Thailand', 'Australia', 'United States',
            'United Kingdom', 'Canada', 'Japan', 'South Korea', 'China', 'Hong Kong',
        ];
        return $countries[array_rand($countries)];
    }

    private function getCityBySchool(School $school): string
    {
        return match ($school->code) {
            'VIS-BIN'  => 'Tangerang Selatan',
            'VIS-KG'   => 'Jakarta Utara',
            'VIS-BALI' => 'Denpasar',
            default    => 'Jakarta',
        };
    }

    private function getPostalCodeBySchool(School $school): string
    {
        return match ($school->code) {
            'VIS-BIN'  => '15224',
            'VIS-KG'   => '14240',
            'VIS-BALI' => '80228',
            default    => '12345',
        };
    }

    private function getIndonesianAddress(School $school): string
    {
        $no = rand(1, 150);

        $addresses = match ($school->code) {
            'VIS-BIN' => [
                "Jl. Bintaro Utama {$no} Blok A",
                "Jl. Boulevard Bintaro Jaya No. {$no}",
                "Jl. Taman Bintaro Sektor 9 No. {$no}",
                "Jl. Maleo Sektor 7 No. {$no}",
            ],
            'VIS-KG' => [
                "Jl. Boulevard Raya Blok RA No. {$no}",
                "Jl. Boulevard Timur No. {$no}",
                "Jl. Pegangsaan Dua No. {$no}",
                "Jl. Raya Kelapa Gading No. {$no}",
            ],
            'VIS-BALI' => [
                "Jl. Danau Tamblingan No. {$no}, Sanur",
                "Jl. Bypass Ngurah Rai No. {$no}",
                "Jl. Pantai Karang No. {$no}, Sanur",
                "Jl. Mertasari No. {$no}, Sidakarya",
            ],
            default => ["Jl. Example No. {$no}"],
        };

        return $addresses[array_rand($addresses)];
    }

    private function getPreviousSchoolName(Level $level): string
    {
        if (in_array($level->code, ['EP', 'PS'])) {
            $schools = [
                'Sunshine Preschool', 'Little Stars Kindergarten',
                'Happy Kids Daycare', 'Bright Beginnings Early Learning', 'Rainbow Nursery School',
            ];
        } elseif (in_array($level->code, ['PK', 'G1', 'G2'])) {
            $schools = [
                'International Kindergarten Jakarta', 'Global Preschool',
                'Worldwide Kids School', 'Premier Early Years', 'Elite Kindergarten',
            ];
        } else {
            $schools = [
                'Jakarta International School', 'Singapore International School',
                'British International School', 'Australian International School',
                'Canadian International School', 'Local Public School Jakarta',
            ];
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
            'Google Indonesia', 'Microsoft Asia Pacific', 'Citibank Jakarta',
            'Unilever Indonesia', 'Deloitte Consulting', 'McKinsey & Company',
            'PT Astra International', 'Bank Mandiri', 'Telkom Indonesia',
            'Self-employed / Own Business', 'Samsung Electronics', 'Shell Indonesia',
            'Ministry of Finance', 'Siemens Indonesia', 'ExxonMobil Indonesia',
        ];
        return $workplaces[array_rand($workplaces)];
    }

    private function getRandomPaymentMethod(): string
    {
        $methods = ['bank_transfer', 'virtual_account', 'credit_card', 'debit_card'];
        return $methods[array_rand($methods)];
    }

    private function getHomroomTeacher(): string
    {
        $teachers = [
            'Ms. Rachel Green', 'Mr. Tom Bradley', 'Ms. Lisa Chen',
            'Mr. David Park', 'Ms. Sarah Mills', 'Mr. John Rivera',
            'Ms. Amy Johnson', 'Mr. Chris Thompson', 'Ms. Kate Wilson',
            'Ms. Diana Lee', 'Mr. James Murphy', 'Ms. Helen Zhang',
        ];
        return $teachers[array_rand($teachers)];
    }

    private function getStatusNotes(string $status): ?string
    {
        return match ($status) {
            'submitted'          => 'Application received and awaiting initial review.',
            'under_review'       => 'Documents being verified by admission team.',
            'documents_verified' => 'All documents verified successfully.',
            'interview_scheduled'=> 'Interview appointment has been scheduled.',
            'interview_completed'=> 'Interview completed, awaiting final decision.',
            'payment_pending'    => 'Waiting for post-acceptance payment confirmation.',
            'payment_verified'   => 'Payment confirmed, preparing enrollment documents.',
            'accepted'           => 'Congratulations! Student has been accepted to VIS.',
            'enrolled'           => 'Enrollment completed. Welcome to the VIS family!',
            'rejected'           => 'We regret to inform that the application was not successful at this time.',
            'waitlisted'         => 'Application placed on waitlist. We will contact you if a place becomes available.',
            'withdrawn'          => 'Application withdrawn by parent/guardian.',
            default              => null,
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
