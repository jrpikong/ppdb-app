<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\AdmissionPeriod;
use App\Models\Application;
use App\Models\Document;
use App\Models\Level;
use App\Models\Payment;
use App\Models\PaymentType;
use App\Models\Role;
use App\Models\School;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PolicyAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_parent_can_only_submit_own_draft_application(): void
    {
        $fixture = $this->seedFixture();
        $parentOwner = $fixture['parent_owner'];
        $parentOther = $fixture['parent_other'];
        $application = $fixture['application'];

        $this->assertTrue(
            Gate::forUser($parentOwner)->allows('transitionStatus', [$application, 'submitted'])
        );

        $this->assertFalse(
            Gate::forUser($parentOther)->allows('transitionStatus', [$application, 'submitted'])
        );
    }

    public function test_only_school_admin_and_admission_admin_can_transition_application_status(): void
    {
        $fixture = $this->seedFixture();
        $application = $fixture['application']->fresh();
        $application->update(['status' => 'submitted']);

        $schoolAdmin = $this->createUserWithRole('school_admin', $fixture['school_a']->id, $fixture['school_a']->id);
        $admissionAdmin = $this->createUserWithRole('admission_admin', $fixture['school_a']->id, $fixture['school_a']->id);
        $financeAdmin = $this->createUserWithRole('finance_admin', $fixture['school_a']->id, $fixture['school_a']->id);

        $this->assertTrue(
            Gate::forUser($schoolAdmin)->allows('transitionStatus', [$application, 'under_review'])
        );
        $this->assertTrue(
            Gate::forUser($admissionAdmin)->allows('transitionStatus', [$application, 'under_review'])
        );
        $this->assertFalse(
            Gate::forUser($financeAdmin)->allows('transitionStatus', [$application, 'under_review'])
        );
    }

    public function test_only_school_admin_or_finance_admin_can_verify_payment_in_same_school(): void
    {
        $fixture = $this->seedFixture();
        $payment = $fixture['payment'];
        $payment->transitionStatus('submitted', [
            'proof_file' => 'payments/test-proof.pdf',
            'payment_method' => 'bank_transfer',
            'payment_date' => now()->toDateString(),
        ], $fixture['parent_owner']->id);

        $schoolAdmin = $this->createUserWithRole('school_admin', $fixture['school_a']->id, $fixture['school_a']->id);
        $financeAdmin = $this->createUserWithRole('finance_admin', $fixture['school_a']->id, $fixture['school_a']->id);
        $admissionAdmin = $this->createUserWithRole('admission_admin', $fixture['school_a']->id, $fixture['school_a']->id);
        $otherSchoolFinance = $this->createUserWithRole('finance_admin', $fixture['school_b']->id, $fixture['school_b']->id);

        $this->assertTrue(Gate::forUser($schoolAdmin)->allows('transitionStatus', [$payment->fresh(), 'verified']));
        $this->assertTrue(Gate::forUser($financeAdmin)->allows('transitionStatus', [$payment->fresh(), 'verified']));
        $this->assertFalse(Gate::forUser($admissionAdmin)->allows('transitionStatus', [$payment->fresh(), 'verified']));
        $this->assertFalse(Gate::forUser($otherSchoolFinance)->allows('transitionStatus', [$payment->fresh(), 'verified']));
    }

    public function test_document_and_schedule_update_policy_is_restricted_by_role_and_tenant(): void
    {
        $fixture = $this->seedFixture();
        $document = $fixture['document'];
        $schedule = $fixture['schedule'];

        $schoolAdmin = $this->createUserWithRole('school_admin', $fixture['school_a']->id, $fixture['school_a']->id);
        $admissionAdmin = $this->createUserWithRole('admission_admin', $fixture['school_a']->id, $fixture['school_a']->id);
        $financeAdmin = $this->createUserWithRole('finance_admin', $fixture['school_a']->id, $fixture['school_a']->id);
        $otherSchoolAdmin = $this->createUserWithRole('school_admin', $fixture['school_b']->id, $fixture['school_b']->id);

        $this->assertTrue(Gate::forUser($schoolAdmin)->allows('update', $document));
        $this->assertTrue(Gate::forUser($admissionAdmin)->allows('update', $document));
        $this->assertFalse(Gate::forUser($financeAdmin)->allows('update', $document));
        $this->assertFalse(Gate::forUser($otherSchoolAdmin)->allows('update', $document));

        $this->assertTrue(Gate::forUser($schoolAdmin)->allows('update', $schedule));
        $this->assertTrue(Gate::forUser($admissionAdmin)->allows('update', $schedule));
        $this->assertFalse(Gate::forUser($financeAdmin)->allows('update', $schedule));
        $this->assertFalse(Gate::forUser($otherSchoolAdmin)->allows('update', $schedule));
    }

    public function test_domain_transition_throws_for_unauthorized_actor(): void
    {
        $fixture = $this->seedFixture();
        $application = $fixture['application']->fresh();
        $application->update(['status' => 'submitted']);

        $financeAdmin = $this->createUserWithRole('finance_admin', $fixture['school_a']->id, $fixture['school_a']->id);

        $this->expectException(AuthorizationException::class);
        $application->transitionStatus('under_review', null, $financeAdmin->id);
    }

    /**
     * @return array{
     *     school_a: School,
     *     school_b: School,
     *     application: Application,
     *     payment: Payment,
     *     document: Document,
     *     schedule: Schedule,
     *     parent_owner: User,
     *     parent_other: User
     * }
     */
    private function seedFixture(): array
    {
        $schoolA = School::create([
            'code' => 'SCH-POL-A',
            'name' => 'School Policy A',
            'type' => 'branch',
            'city' => 'Jakarta',
        ]);

        $schoolB = School::create([
            'code' => 'SCH-POL-B',
            'name' => 'School Policy B',
            'type' => 'branch',
            'city' => 'Bandung',
        ]);

        $academicYear = AcademicYear::create([
            'school_id' => $schoolA->id,
            'name' => '2026-2027',
            'start_year' => 2026,
            'end_year' => 2027,
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'is_active' => true,
        ]);

        $period = AdmissionPeriod::create([
            'school_id' => $schoolA->id,
            'academic_year_id' => $academicYear->id,
            'name' => 'Main Intake',
            'start_date' => now()->subMonth()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
            'is_active' => true,
            'allow_applications' => true,
            'is_rolling' => false,
        ]);

        $level = Level::create([
            'school_id' => $schoolA->id,
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
            'school_id' => $schoolA->id,
            'code' => 'REG',
            'name' => 'Registration',
            'amount' => 1500000,
            'payment_stage' => 'pre_submission',
            'is_active' => true,
        ]);

        $parentOwner = $this->createUserWithRole('parent', 0, 0);
        $parentOther = $this->createUserWithRole('parent', 0, 0);

        $application = Application::create([
            'user_id' => $parentOwner->id,
            'school_id' => $schoolA->id,
            'academic_year_id' => $academicYear->id,
            'admission_period_id' => $period->id,
            'level_id' => $level->id,
            'application_number' => 'SCH-POL-A-2602-0001',
            'status' => 'draft',
            'student_first_name' => 'Policy',
            'student_last_name' => 'Tester',
            'birth_date' => '2017-01-01',
            'nationality' => 'Indonesian',
            'gender' => 'male',
            'current_address' => 'Address',
            'current_city' => 'Jakarta',
            'current_country' => 'Indonesia',
        ]);

        $payment = Payment::create([
            'application_id' => $application->id,
            'payment_type_id' => $paymentType->id,
            'transaction_code' => 'SCH-POL-A-PAY-20260220-0001',
            'amount' => 1500000,
            'currency' => 'IDR',
            'payment_date' => now()->toDateString(),
            'status' => 'pending',
        ]);

        $document = Document::create([
            'application_id' => $application->id,
            'type' => 'birth_certificate',
            'name' => 'birth.pdf',
            'file_path' => 'documents/birth.pdf',
            'file_type' => 'application/pdf',
            'file_size' => 1200,
            'status' => 'pending',
        ]);

        $schedule = Schedule::create([
            'application_id' => $application->id,
            'type' => 'interview',
            'scheduled_date' => now()->addDays(3)->toDateString(),
            'scheduled_time' => '09:00:00',
            'duration_minutes' => 60,
            'status' => 'scheduled',
        ]);

        return [
            'school_a' => $schoolA,
            'school_b' => $schoolB,
            'application' => $application,
            'payment' => $payment,
            'document' => $document,
            'schedule' => $schedule,
            'parent_owner' => $parentOwner,
            'parent_other' => $parentOther,
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

        return $user->refresh();
    }
}
