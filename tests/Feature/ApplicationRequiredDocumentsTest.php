<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\AdmissionPeriod;
use App\Models\Application;
use App\Models\Document;
use App\Models\Level;
use App\Models\School;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplicationRequiredDocumentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_required_document_types_use_school_setting_with_safe_fallback(): void
    {
        $school = School::create([
            'code' => 'SCH-RD-01',
            'name' => 'School Required Docs',
            'type' => 'branch',
            'city' => 'Jakarta',
        ]);

        $this->assertSame(
            Application::REQUIRED_DOCUMENT_TYPES,
            Application::getRequiredDocumentTypesForSchool($school->id),
        );

        Setting::create([
            'default_school_id' => $school->id,
            'required_documents' => ['passport', 'birth_certificate', 'invalid_type'],
        ]);

        $this->assertSame(
            ['passport', 'birth_certificate'],
            Application::getRequiredDocumentTypesForSchool($school->id),
        );
    }

    public function test_has_all_required_documents_follows_school_specific_rules(): void
    {
        [$school, $academicYear, $admissionPeriod, $level, $parent] = $this->createBaseFixture();

        Setting::create([
            'default_school_id' => $school->id,
            'required_documents' => ['passport'],
        ]);

        $application = Application::create([
            'user_id' => $parent->id,
            'school_id' => $school->id,
            'academic_year_id' => $academicYear->id,
            'admission_period_id' => $admissionPeriod->id,
            'level_id' => $level->id,
            'application_number' => 'SCH-RD-01-2602-0001',
            'status' => 'draft',
            'student_first_name' => 'Dina',
            'student_last_name' => 'Putri',
            'birth_date' => '2017-01-15',
            'nationality' => 'Indonesian',
            'gender' => 'female',
            'current_address' => 'Jl. Test',
            'current_city' => 'Jakarta',
            'current_country' => 'Indonesia',
        ]);

        $this->assertFalse($application->hasAllRequiredDocuments());

        Document::create([
            'application_id' => $application->id,
            'type' => 'passport',
            'name' => 'passport.pdf',
            'file_path' => 'documents/passport.pdf',
            'file_type' => 'application/pdf',
            'file_size' => 2048,
            'status' => 'pending',
        ]);

        $this->assertTrue($application->fresh()->hasAllRequiredDocuments());

        Setting::query()
            ->where('default_school_id', $school->id)
            ->update(['required_documents' => ['passport', 'family_card']]);

        $this->assertFalse($application->fresh()->hasAllRequiredDocuments());
    }

    /**
     * @return array{School, AcademicYear, AdmissionPeriod, Level, User}
     */
    private function createBaseFixture(): array
    {
        $school = School::create([
            'code' => 'SCH-RD-01',
            'name' => 'School Required Docs',
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
            'quota' => 50,
            'annual_tuition_fee' => 15000000,
            'current_enrollment' => 0,
            'is_active' => true,
            'is_accepting_applications' => true,
        ]);

        $parent = User::factory()->create([
            'school_id' => 0,
            'is_active' => true,
        ]);

        return [$school, $academicYear, $admissionPeriod, $level, $parent];
    }
}
