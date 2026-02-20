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
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class SecureFileAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_secure_files(): void
    {
        $fixture = $this->seedSecureFileFixture();

        $documentResponse = $this->getJson(route('secure-files.documents.download', ['document' => $fixture['document']->id]));
        $documentResponse->assertUnauthorized();

        $paymentResponse = $this->getJson(route('secure-files.payments.proof', ['payment' => $fixture['payment']->id]));
        $paymentResponse->assertUnauthorized();
    }

    public function test_parent_can_access_own_application_files_only(): void
    {
        $fixture = $this->seedSecureFileFixture();

        $this->actingAs($fixture['parent_owner']);
        $this->get(route('secure-files.documents.download', ['document' => $fixture['document']->id]))->assertOk();
        $this->get(route('secure-files.payments.proof', ['payment' => $fixture['payment']->id]))->assertOk();

        $this->actingAs($fixture['parent_other']);
        $this->get(route('secure-files.documents.download', ['document' => $fixture['document']->id]))->assertForbidden();
        $this->get(route('secure-files.payments.proof', ['payment' => $fixture['payment']->id]))->assertForbidden();
    }

    public function test_school_staff_can_only_access_same_tenant_files(): void
    {
        $fixture = $this->seedSecureFileFixture();

        $sameTenantStaff = $this->createUserWithRole(
            roleName: 'school_admin',
            roleTeamId: $fixture['school_a']->id,
            userSchoolId: $fixture['school_a']->id
        );

        $otherTenantStaff = $this->createUserWithRole(
            roleName: 'school_admin',
            roleTeamId: $fixture['school_b']->id,
            userSchoolId: $fixture['school_b']->id
        );

        $this->actingAs($sameTenantStaff);
        $this->get(route('secure-files.documents.download', ['document' => $fixture['document']->id]))->assertOk();
        $this->get(route('secure-files.payments.proof', ['payment' => $fixture['payment']->id]))->assertOk();

        $this->actingAs($otherTenantStaff);
        $this->get(route('secure-files.documents.download', ['document' => $fixture['document']->id]))->assertForbidden();
        $this->get(route('secure-files.payments.proof', ['payment' => $fixture['payment']->id]))->assertForbidden();
    }

    public function test_super_admin_can_access_secure_files(): void
    {
        $fixture = $this->seedSecureFileFixture();

        $superAdmin = $this->createUserWithRole(
            roleName: 'super_admin',
            roleTeamId: 0,
            userSchoolId: 0
        );

        $this->actingAs($superAdmin);
        $this->get(route('secure-files.documents.download', ['document' => $fixture['document']->id]))->assertOk();
        $this->get(route('secure-files.payments.proof', ['payment' => $fixture['payment']->id]))->assertOk();
    }

    /**
     * @return array{
     *     school_a: School,
     *     school_b: School,
     *     parent_owner: User,
     *     parent_other: User,
     *     document: Document,
     *     payment: Payment
     * }
     */
    private function seedSecureFileFixture(): array
    {
        Storage::fake('local');
        Storage::fake('public');

        $schoolA = School::create([
            'code' => 'SCH-A',
            'name' => 'School A',
            'type' => 'branch',
            'city' => 'Jakarta',
        ]);

        $schoolB = School::create([
            'code' => 'SCH-B',
            'name' => 'School B',
            'type' => 'branch',
            'city' => 'Bandung',
        ]);

        $academicYearA = AcademicYear::create([
            'school_id' => $schoolA->id,
            'name' => '2026-2027',
            'start_year' => 2026,
            'end_year' => 2027,
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'is_active' => true,
        ]);

        $levelA = Level::create([
            'school_id' => $schoolA->id,
            'code' => 'G1',
            'name' => 'Grade 1',
            'program_category' => 'primary_years',
            'age_min' => 6.0,
            'age_max' => 8.0,
            'quota' => 100,
            'annual_tuition_fee' => 10000000,
            'current_enrollment' => 0,
            'is_active' => true,
            'is_accepting_applications' => true,
        ]);

        $admissionPeriodA = AdmissionPeriod::create([
            'school_id' => $schoolA->id,
            'academic_year_id' => $academicYearA->id,
            'name' => 'Main Intake',
            'start_date' => now()->subMonth()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
            'is_active' => true,
            'allow_applications' => true,
            'is_rolling' => false,
        ]);

        $paymentTypeA = PaymentType::create([
            'school_id' => $schoolA->id,
            'code' => 'REG',
            'name' => 'Registration Fee',
            'amount' => 1500000,
            'payment_stage' => 'pre_submission',
            'is_active' => true,
        ]);

        $parentOwner = $this->createUserWithRole('parent', 0, 0);
        $parentOther = $this->createUserWithRole('parent', 0, 0);

        $application = Application::create([
            'user_id' => $parentOwner->id,
            'school_id' => $schoolA->id,
            'academic_year_id' => $academicYearA->id,
            'admission_period_id' => $admissionPeriodA->id,
            'level_id' => $levelA->id,
            'application_number' => 'SCH-A-2026-0001',
            'status' => 'submitted',
            'student_first_name' => 'John',
            'student_last_name' => 'Doe',
            'birth_date' => '2016-01-01',
            'nationality' => 'Indonesian',
        ]);

        $documentPath = 'documents/secure-document.pdf';
        $paymentPath = 'payments/secure-proof.pdf';

        Storage::disk('local')->put($documentPath, 'document-content');
        Storage::disk('local')->put($paymentPath, 'payment-content');

        $document = Document::create([
            'application_id' => $application->id,
            'type' => 'birth_certificate',
            'name' => 'birth-certificate.pdf',
            'file_path' => $documentPath,
            'file_type' => 'application/pdf',
            'file_size' => 16,
            'status' => 'pending',
        ]);

        $payment = Payment::create([
            'application_id' => $application->id,
            'payment_type_id' => $paymentTypeA->id,
            'transaction_code' => 'SCH-A-PAY-20260220-0001',
            'amount' => 1500000,
            'currency' => 'IDR',
            'payment_date' => now()->toDateString(),
            'proof_file' => $paymentPath,
            'status' => 'submitted',
        ]);

        return [
            'school_a' => $schoolA,
            'school_b' => $schoolB,
            'parent_owner' => $parentOwner,
            'parent_other' => $parentOther,
            'document' => $document,
            'payment' => $payment,
        ];
    }

    private function createUserWithRole(string $roleName, int $roleTeamId, int $userSchoolId): User
    {
        $permissionRegistrar = app(PermissionRegistrar::class);
        $permissionRegistrar->setPermissionsTeamId($roleTeamId);

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
