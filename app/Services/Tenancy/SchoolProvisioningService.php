<?php

declare(strict_types=1);

namespace App\Services\Tenancy;

use App\Models\AcademicYear;
use App\Models\AdmissionPeriod;
use App\Models\Permission;
use App\Models\PaymentType;
use App\Models\Role;
use App\Models\School;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\TenantSuperAdminWelcomeNotification;
use App\Models\Level;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;
use Throwable;

class SchoolProvisioningService
{
    /**
     * @var array<int, array{code:string,name:string,program_category:string,age_min:float,age_max:float,quota:int,annual_tuition_fee:int,sort_order:int}>
     */
    private const DEFAULT_LEVELS = [
        ['code' => 'EP', 'name' => 'Early Preschool', 'program_category' => 'early_years', 'age_min' => 1.6, 'age_max' => 2.6, 'quota' => 20, 'annual_tuition_fee' => 45000000, 'sort_order' => 1],
        ['code' => 'PS', 'name' => 'Preschool', 'program_category' => 'early_years', 'age_min' => 2.6, 'age_max' => 4.0, 'quota' => 25, 'annual_tuition_fee' => 48000000, 'sort_order' => 2],
        ['code' => 'PK', 'name' => 'Pre-Kindy', 'program_category' => 'early_years', 'age_min' => 4.0, 'age_max' => 5.0, 'quota' => 30, 'annual_tuition_fee' => 52000000, 'sort_order' => 3],
        ['code' => 'G1', 'name' => 'Grade 1', 'program_category' => 'primary_years', 'age_min' => 6.0, 'age_max' => 7.0, 'quota' => 35, 'annual_tuition_fee' => 65000000, 'sort_order' => 4],
        ['code' => 'G2', 'name' => 'Grade 2', 'program_category' => 'primary_years', 'age_min' => 7.0, 'age_max' => 8.0, 'quota' => 35, 'annual_tuition_fee' => 65000000, 'sort_order' => 5],
        ['code' => 'G3', 'name' => 'Grade 3', 'program_category' => 'primary_years', 'age_min' => 8.0, 'age_max' => 9.0, 'quota' => 35, 'annual_tuition_fee' => 68000000, 'sort_order' => 6],
        ['code' => 'G4', 'name' => 'Grade 4', 'program_category' => 'primary_years', 'age_min' => 9.0, 'age_max' => 10.0, 'quota' => 35, 'annual_tuition_fee' => 68000000, 'sort_order' => 7],
        ['code' => 'G5', 'name' => 'Grade 5', 'program_category' => 'primary_years', 'age_min' => 10.0, 'age_max' => 11.0, 'quota' => 35, 'annual_tuition_fee' => 70000000, 'sort_order' => 8],
        ['code' => 'G6', 'name' => 'Grade 6', 'program_category' => 'middle_years', 'age_min' => 11.0, 'age_max' => 12.0, 'quota' => 40, 'annual_tuition_fee' => 75000000, 'sort_order' => 9],
        ['code' => 'G7', 'name' => 'Grade 7', 'program_category' => 'middle_years', 'age_min' => 12.0, 'age_max' => 13.0, 'quota' => 40, 'annual_tuition_fee' => 75000000, 'sort_order' => 10],
        ['code' => 'G8', 'name' => 'Grade 8', 'program_category' => 'middle_years', 'age_min' => 13.0, 'age_max' => 14.0, 'quota' => 40, 'annual_tuition_fee' => 78000000, 'sort_order' => 11],
        ['code' => 'G9', 'name' => 'Grade 9', 'program_category' => 'middle_years', 'age_min' => 14.0, 'age_max' => 15.0, 'quota' => 40, 'annual_tuition_fee' => 78000000, 'sort_order' => 12],
    ];

    /**
     * @var array<int, array{code:string,name:string,description:string,amount:int,payment_stage:string,is_mandatory:bool,is_refundable:bool}>
     */
    private const DEFAULT_PAYMENT_TYPES = [
        ['code' => 'SAVING_SEAT', 'name' => 'Saving Seat Payment', 'description' => 'Required to secure admission process slot before submission.', 'amount' => 2500000, 'payment_stage' => 'pre_submission', 'is_mandatory' => true, 'is_refundable' => false],
        ['code' => 'REGISTRATION', 'name' => 'Registration Fee', 'description' => 'One-time registration fee after acceptance.', 'amount' => 5000000, 'payment_stage' => 'post_acceptance', 'is_mandatory' => true, 'is_refundable' => false],
        ['code' => 'DEVELOPMENT', 'name' => 'Development Fee', 'description' => 'Annual development and facilities contribution.', 'amount' => 10000000, 'payment_stage' => 'post_acceptance', 'is_mandatory' => true, 'is_refundable' => false],
        ['code' => 'UNIFORM', 'name' => 'Uniform Package', 'description' => 'Complete uniform package for the academic year.', 'amount' => 3500000, 'payment_stage' => 'enrollment', 'is_mandatory' => true, 'is_refundable' => false],
        ['code' => 'BOOKS', 'name' => 'Book Package', 'description' => 'Textbooks and learning materials package.', 'amount' => 4000000, 'payment_stage' => 'enrollment', 'is_mandatory' => true, 'is_refundable' => false],
        ['code' => 'TECHNOLOGY', 'name' => 'Technology Fee', 'description' => 'Technology infrastructure and LMS access.', 'amount' => 2000000, 'payment_stage' => 'enrollment', 'is_mandatory' => false, 'is_refundable' => false],
    ];

    /**
     * @param array{name:string,email:string,password:string,employee_id?:string,occupation?:string,department?:string,phone?:string|null} $superAdminData
     */
    public function provisionSchoolTenant(School $school, array $superAdminData): User
    {
        $user = DB::transaction(function () use ($school, $superAdminData): User {
            $tenantRole = $this->ensureTenantSuperAdminRole($school->id);

            $user = User::query()->create([
                'school_id' => $school->id,
                'name' => $superAdminData['name'],
                'email' => $superAdminData['email'],
                'password' => $superAdminData['password'],
                'employee_id' => $superAdminData['employee_id'] ?? strtoupper($school->code) . '-ADMIN-001',
                'occupation' => $superAdminData['occupation'] ?? 'School Principal',
                'department' => $superAdminData['department'] ?? 'Leadership & Management',
                'phone' => $superAdminData['phone'] ?? null,
                'is_active' => true,
                'email_verified_at' => now(),
            ]);

            $permissionRegistrar = app(PermissionRegistrar::class);
            $originalTeamId = (int) (getPermissionsTeamId() ?? 0);
            $permissionRegistrar->setPermissionsTeamId($school->id);

            try {
                $user->assignRole($tenantRole);
            } finally {
                $permissionRegistrar->setPermissionsTeamId($originalTeamId);
            }

            $this->provisionBaselineData($school);

            return $user->refresh();
        }, 3);

        $this->sendWelcomeNotification(
            user: $user,
            school: $school,
            plainPassword: (string) ($superAdminData['password'] ?? '')
        );

        return $user;
    }

    private function ensureTenantSuperAdminRole(int $schoolId): Role
    {
        /** @var Role $role */
        $role = Role::query()->firstOrCreate([
            'name' => 'super_admin',
            'guard_name' => 'web',
            'school_id' => $schoolId,
        ]);

        $templateRole = Role::query()
            ->where('name', 'super_admin')
            ->where('guard_name', 'web')
            ->where('school_id', 0)
            ->first();

        $permissions = $templateRole?->permissions
            ?? Permission::query()->get();

        $role->syncPermissions($permissions);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $role;
    }

    private function provisionBaselineData(School $school): void
    {
        $academicYear = $this->ensureActiveAcademicYear($school);

        $this->ensureDefaultLevels($school);
        $this->ensureDefaultAdmissionPeriod($school, $academicYear);
        $this->ensureDefaultPaymentTypes($school);
        $this->ensureDefaultSetting($school);
    }

    private function ensureActiveAcademicYear(School $school): AcademicYear
    {
        $startYear = (int) now()->format('Y');
        $endYear = $startYear + 1;
        $name = "{$startYear}-{$endYear}";

        /** @var AcademicYear $academicYear */
        $academicYear = AcademicYear::query()
            ->withTrashed()
            ->firstOrNew([
                'school_id' => $school->id,
                'name' => $name,
            ]);

        $academicYear->fill([
            'start_year' => $startYear,
            'end_year' => $endYear,
            'start_date' => Carbon::create($startYear, 7, 1),
            'end_date' => Carbon::create($endYear, 6, 30),
            'is_active' => (bool) $school->is_active,
            'description' => "Academic Year {$name} for {$school->name}",
        ]);
        $academicYear->save();

        if ($academicYear->trashed()) {
            $academicYear->restore();
        }

        return $academicYear->refresh();
    }

    private function ensureDefaultLevels(School $school): void
    {
        foreach (self::DEFAULT_LEVELS as $levelData) {
            /** @var Level $level */
            $level = Level::query()
                ->withTrashed()
                ->firstOrNew([
                    'school_id' => $school->id,
                    'code' => $levelData['code'],
                ]);

            $level->fill([
                'name' => $levelData['name'],
                'program_category' => $levelData['program_category'],
                'age_min' => $levelData['age_min'],
                'age_max' => $levelData['age_max'],
                'quota' => $levelData['quota'],
                'annual_tuition_fee' => $levelData['annual_tuition_fee'],
                'sort_order' => $levelData['sort_order'],
                'is_active' => (bool) $school->is_active,
                'is_accepting_applications' => (bool) $school->is_active,
            ]);
            $level->save();

            if ($level->trashed()) {
                $level->restore();
            }
        }
    }

    private function ensureDefaultAdmissionPeriod(School $school, AcademicYear $academicYear): void
    {
        $name = "{$academicYear->name} Main Intake";

        $startDate = Carbon::today()->subMonth();
        $endDate = Carbon::today()->addMonths(4);
        $decisionDate = $endDate->copy()->addWeeks(2);
        $enrollmentDeadline = $decisionDate->copy()->addWeeks(2);

        /** @var AdmissionPeriod $period */
        $period = AdmissionPeriod::query()
            ->withTrashed()
            ->firstOrNew([
                'school_id' => $school->id,
                'academic_year_id' => $academicYear->id,
                'name' => $name,
            ]);

        $period->fill([
            'start_date' => $startDate,
            'end_date' => $endDate,
            'decision_date' => $decisionDate,
            'enrollment_deadline' => $enrollmentDeadline,
            'is_active' => (bool) $school->is_active,
            'allow_applications' => (bool) $school->is_active,
            'is_rolling' => false,
            'description' => "Main admission period for {$academicYear->name}.",
            'settings' => [
                'max_applications' => 500,
                'interview_required' => true,
                'observation_required' => true,
                'entrance_test_required' => true,
                'early_decision_available' => true,
            ],
        ]);
        $period->save();

        if ($period->trashed()) {
            $period->restore();
        }
    }

    private function ensureDefaultPaymentTypes(School $school): void
    {
        $settings = is_array($school->settings) ? $school->settings : [];

        $bankInfo = $settings['bank_account'] ?? [
            'bank_name' => 'Bank Mandiri',
            'account_number' => '137-00-1234567-8',
            'account_holder' => "PT Veritas Intercultural School {$school->name}",
            'swift_code' => 'BMRIIDJA',
            'branch' => $school->city,
        ];

        foreach (self::DEFAULT_PAYMENT_TYPES as $paymentTypeData) {
            /** @var PaymentType $paymentType */
            $paymentType = PaymentType::query()
                ->withTrashed()
                ->firstOrNew([
                    'school_id' => $school->id,
                    'code' => $paymentTypeData['code'],
                ]);

            $paymentType->fill([
                'name' => $paymentTypeData['name'],
                'description' => $paymentTypeData['description'],
                'amount' => $paymentTypeData['amount'],
                'payment_stage' => $paymentTypeData['payment_stage'],
                'is_mandatory' => $paymentTypeData['is_mandatory'],
                'is_refundable' => $paymentTypeData['is_refundable'],
                'is_active' => (bool) $school->is_active,
                'bank_info' => $bankInfo,
                'payment_instructions' => "Use application number as transfer reference for {$paymentTypeData['name']}.",
            ]);
            $paymentType->save();

            if ($paymentType->trashed()) {
                $paymentType->restore();
            }
        }
    }

    private function ensureDefaultSetting(School $school): void
    {
        /** @var Setting $setting */
        $setting = Setting::query()->firstOrNew([
            'default_school_id' => $school->id,
        ]);

        $setting->fill([
            'app_name' => "{$school->name} Admission Portal",
            'app_version' => '1.0.0',
            'multi_school_enabled' => true,
            'online_admission_enabled' => (bool) $school->allow_online_admission,
            'require_payment_before_submission' => true,
            'application_review_days' => 5,
            'email_notifications_enabled' => true,
            'email_from_address' => $school->email ?: null,
            'email_from_name' => "{$school->name} Admissions",
            'send_submission_confirmation' => true,
            'send_status_updates' => true,
            'send_interview_reminders' => true,
            'send_acceptance_letters' => true,
            'default_currency' => 'IDR',
            'payment_instructions' => 'Please use application number as transfer reference.',
            'required_documents' => [
                'student_photo_1',
                'student_photo_2',
                'father_photo',
                'mother_photo',
                'father_id_card',
                'mother_id_card',
                'birth_certificate',
                'family_card',
                'latest_report_book',
            ],
            'max_file_size_mb' => 10,
            'allowed_file_types' => ['pdf', 'jpg', 'jpeg', 'png', 'webp'],
            'auto_schedule_interviews' => false,
            'interview_duration_minutes' => 60,
            'interview_buffer_minutes' => 15,
            'maintenance_mode' => false,
            'maintenance_message' => null,
            'extra_settings' => [
                'school_code' => $school->code,
                'school_city' => $school->city,
                'default_timezone' => $school->timezone,
                'support_email' => $school->email,
                'require_email_verification' => true,
            ],
        ]);

        $setting->save();
    }

    private function sendWelcomeNotification(User $user, School $school, string $plainPassword): void
    {
        if (blank($user->email) || blank($plainPassword)) {
            return;
        }

        try {
            $user->notify(new TenantSuperAdminWelcomeNotification($school, $plainPassword));
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
