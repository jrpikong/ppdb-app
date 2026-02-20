<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\ActivityLog;
use App\Models\AdmissionPeriod;
use App\Models\Application;
use App\Models\Level;
use App\Models\Payment;
use App\Models\PaymentType;
use App\Models\Role;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class WorkflowHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_application_submit_is_idempotent_and_logged(): void
    {
        $fixture = $this->seedBaseFixture();
        $application = $fixture['application'];
        $parent = $fixture['parent'];

        $firstSubmit = $application->submit($parent->id);
        $secondSubmit = $application->fresh()->submit($parent->id);

        $this->assertTrue($firstSubmit);
        $this->assertFalse($secondSubmit);
        $this->assertSame('submitted', $application->fresh()->status);

        $this->assertEquals(1, ActivityLog::query()
            ->where('log_name', 'application')
            ->where('event', 'status_changed')
            ->where('subject_type', Application::class)
            ->where('subject_id', $application->id)
            ->count());
    }

    public function test_application_immutable_fields_are_locked_after_submit(): void
    {
        $fixture = $this->seedBaseFixture();
        $application = $fixture['application'];
        $parent = $fixture['parent'];

        $application->submit($parent->id);

        $this->expectException(RuntimeException::class);
        $application->update([
            'student_first_name' => 'Tampered',
        ]);
    }

    public function test_payment_state_machine_and_audit_log(): void
    {
        $fixture = $this->seedBaseFixture();
        $payment = $fixture['payment'];
        $parent = $fixture['parent'];
        $finance = $this->createUserWithRole('finance_admin', $fixture['school']->id, $fixture['school']->id);

        $submitted = $payment->submitProof([
            'payment_date' => now()->toDateString(),
            'payment_method' => 'bank_transfer',
            'proof_file' => 'payments/proof-1.pdf',
            'notes' => 'Payment submitted',
        ], $parent->id);

        $verified = $payment->fresh()->verify($finance->id, 'Verified by finance');
        $verifiedAgain = $payment->fresh()->verify($finance->id, 'Duplicate click');

        $this->assertTrue($submitted);
        $this->assertTrue($verified);
        $this->assertFalse($verifiedAgain);
        $this->assertSame('verified', $payment->fresh()->status);

        $this->assertEquals(2, ActivityLog::query()
            ->where('log_name', 'payment')
            ->where('event', 'status_changed')
            ->where('subject_type', Payment::class)
            ->where('subject_id', $payment->id)
            ->count());
    }

    public function test_payment_invalid_transition_is_rejected(): void
    {
        $fixture = $this->seedBaseFixture();
        $payment = $fixture['payment'];

        $this->expectException(RuntimeException::class);
        $payment->transitionStatus('verified', [], auth()->id());
    }

    /**
     * @return array{
     *     school: School,
     *     parent: User,
     *     application: Application,
     *     payment: Payment
     * }
     */
    private function seedBaseFixture(): array
    {
        $school = School::create([
            'code' => 'SCH-HARDEN',
            'name' => 'School Harden',
            'type' => 'branch',
            'city' => 'Jakarta',
        ]);

        $academicYear = AcademicYear::create([
            'school_id' => $school->id,
            'name' => '2026-2027',
            'start_year' => 2026,
            'end_year' => 2027,
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'is_active' => true,
        ]);

        $admissionPeriod = AdmissionPeriod::create([
            'school_id' => $school->id,
            'academic_year_id' => $academicYear->id,
            'name' => 'Intake 1',
            'start_date' => now()->subMonth()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
            'is_active' => true,
            'allow_applications' => true,
            'is_rolling' => false,
        ]);

        $level = Level::create([
            'school_id' => $school->id,
            'code' => 'G1',
            'name' => 'Grade 1',
            'program_category' => 'primary_years',
            'age_min' => 6.0,
            'age_max' => 8.0,
            'quota' => 100,
            'annual_tuition_fee' => 15000000,
            'current_enrollment' => 0,
            'is_active' => true,
            'is_accepting_applications' => true,
        ]);

        $paymentType = PaymentType::create([
            'school_id' => $school->id,
            'code' => 'REG',
            'name' => 'Registration',
            'amount' => 1500000,
            'payment_stage' => 'pre_submission',
            'is_active' => true,
        ]);

        $parent = $this->createUserWithRole('parent', 0, 0);

        $application = Application::create([
            'user_id' => $parent->id,
            'school_id' => $school->id,
            'academic_year_id' => $academicYear->id,
            'admission_period_id' => $admissionPeriod->id,
            'level_id' => $level->id,
            'application_number' => 'SCH-HARDEN-2602-0001',
            'status' => 'draft',
            'student_first_name' => 'Daniel',
            'student_last_name' => 'Garcia',
            'birth_date' => '2017-01-15',
            'nationality' => 'Indonesian',
            'gender' => 'male',
            'current_address' => 'Jl. Test',
            'current_city' => 'Jakarta',
            'current_country' => 'Indonesia',
        ]);

        $payment = Payment::create([
            'application_id' => $application->id,
            'payment_type_id' => $paymentType->id,
            'transaction_code' => 'SCH-HARDEN-PAY-20260220-0001',
            'amount' => 1500000,
            'currency' => 'IDR',
            'payment_date' => now()->toDateString(),
            'status' => 'pending',
        ]);

        return [
            'school' => $school,
            'parent' => $parent,
            'application' => $application,
            'payment' => $payment,
        ];
    }

    private function createUserWithRole(string $roleName, int $roleTeamId, int $userSchoolId): User
    {
        app(PermissionRegistrar::class)->setPermissionsTeamId($roleTeamId);

        $role = Role::firstOrCreate([
            'name' => $roleName,
            'guard_name' => 'web',
            'school_id' => $roleTeamId,
        ]);

        $user = User::factory()->create([
            'school_id' => $userSchoolId,
            'is_active' => true,
        ]);

        $user->assignRole($role);

        return $user;
    }
}
