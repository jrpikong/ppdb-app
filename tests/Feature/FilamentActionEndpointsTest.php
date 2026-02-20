<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\My\Resources\Applications\Pages\ViewApplication as MyViewApplicationPage;
use App\Models\AcademicYear;
use App\Models\AdmissionPeriod;
use App\Models\Application;
use App\Models\Document;
use App\Models\Level;
use App\Models\ParentGuardian;
use App\Models\Role;
use App\Models\School;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class FilamentActionEndpointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_parent_can_submit_application_via_filament_action_endpoint(): void
    {
        $fixture = $this->seedApplicationFixture();
        $parent = $fixture['parent_owner'];
        $application = $fixture['application'];

        $this->actingAs($parent);
        $this->setMyPanelContext();

        Livewire::test(MyViewApplicationPage::class, [
            'record' => $application->getKey(),
        ])->callAction('submitApplication');

        $this->assertSame('submitted', $application->fresh()->status);
        $this->assertNotNull($application->fresh()->submitted_at);
    }

    public function test_parent_cannot_submit_other_parent_application_via_filament_page_endpoint(): void
    {
        $fixture = $this->seedApplicationFixture();
        $application = $fixture['application'];
        $otherParent = $fixture['parent_other'];

        $this->actingAs($otherParent);
        $this->setMyPanelContext();

        $this->expectException(ModelNotFoundException::class);

        Livewire::test(MyViewApplicationPage::class, [
            'record' => $application->getKey(),
        ]);
    }

    public function test_parent_cannot_submit_incomplete_application_via_filament_action_endpoint(): void
    {
        $fixture = $this->seedApplicationFixture(withRequiredSubmitData: false);
        $parent = $fixture['parent_owner'];
        $application = $fixture['application'];

        $this->actingAs($parent);
        $this->setMyPanelContext();

        Livewire::test(MyViewApplicationPage::class, [
            'record' => $application->getKey(),
        ])->callAction('submitApplication');

        $this->assertSame('draft', $application->fresh()->status);
    }

    public function test_parent_submit_application_action_is_hidden_after_submit(): void
    {
        $fixture = $this->seedApplicationFixture();
        $parent = $fixture['parent_owner'];
        $application = $fixture['application'];

        $this->actingAs($parent);
        $this->setMyPanelContext();

        Livewire::test(MyViewApplicationPage::class, [
            'record' => $application->getKey(),
        ])->callAction('submitApplication');

        $this->assertSame('submitted', $application->fresh()->status);
        Livewire::test(MyViewApplicationPage::class, [
            'record' => $application->getKey(),
        ])->assertActionHidden('submitApplication');
    }

    /**
     * @return array{
     *     school: School,
     *     parent_owner: User,
     *     parent_other: User,
     *     application: Application
     * }
     */
    private function seedApplicationFixture(bool $withRequiredSubmitData = true): array
    {
        $school = School::create([
            'code' => 'SCH-FEAT',
            'name' => 'School Feature',
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

        $period = AdmissionPeriod::create([
            'school_id' => $school->id,
            'academic_year_id' => $academicYear->id,
            'name' => 'Main Intake',
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

        $parentOwner = $this->createUserWithRole('parent', 0, 0);
        $parentOther = $this->createUserWithRole('parent', 0, 0);

        $application = Application::create([
            'user_id' => $parentOwner->id,
            'school_id' => $school->id,
            'academic_year_id' => $academicYear->id,
            'admission_period_id' => $period->id,
            'level_id' => $level->id,
            'application_number' => 'SCH-FEAT-2602-0001',
            'status' => 'draft',
            'student_first_name' => 'Action',
            'student_last_name' => 'Tester',
            'birth_date' => '2017-01-01',
            'nationality' => 'Indonesian',
            'gender' => 'male',
            'current_address' => 'Jl. Action',
            'current_city' => 'Jakarta',
            'current_country' => 'Indonesia',
        ]);

        if ($withRequiredSubmitData) {
            ParentGuardian::create([
                'application_id' => $application->id,
                'type' => 'father',
                'first_name' => 'Parent',
                'last_name' => 'Owner',
                'relationship' => 'father',
                'email' => 'parent.owner@example.test',
                'phone' => '081234567890',
                'is_primary_contact' => true,
                'is_emergency_contact' => true,
            ]);

            Document::create([
                'application_id' => $application->id,
                'type' => 'birth_certificate',
                'name' => 'birth-certificate.pdf',
                'file_path' => 'documents/birth-certificate.pdf',
                'file_type' => 'application/pdf',
                'file_size' => 1024,
                'status' => 'pending',
            ]);
        }

        return [
            'school' => $school,
            'parent_owner' => $parentOwner,
            'parent_other' => $parentOther,
            'application' => $application,
        ];
    }

    private function setMyPanelContext(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('my'));
        Filament::setTenant(null, isQuiet: true);
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
