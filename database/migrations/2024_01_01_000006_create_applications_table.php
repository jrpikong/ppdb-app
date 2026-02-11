<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();

            // Relations
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // Parent/Guardian
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('admission_period_id')->constrained()->cascadeOnDelete();
            $table->foreignId('level_id')->constrained()->cascadeOnDelete(); // Applied level

            // Application Number (Auto-generated)
            $table->string('application_number')->unique(); // VIS-BIN-2024-0001

            // Student Information
            $table->string('student_first_name');
            $table->string('student_middle_name')->nullable();
            $table->string('student_last_name');
            $table->string('student_preferred_name')->nullable();

            $table->enum('gender', ['male', 'female'])->nullable();
            $table->string('birth_place')->nullable();
            $table->date('birth_date');
            $table->string('nationality');
            $table->string('passport_number')->nullable();

            // Contact Information
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();

            // Current Address
            $table->text('current_address')->nullable();
            $table->string('current_city')->nullable();
            $table->string('current_country')->nullable();
            $table->string('current_postal_code', 10)->nullable();

            // Previous School Information
            $table->string('previous_school_name')->nullable();
            $table->string('previous_school_country')->nullable();
            $table->string('current_grade_level')->nullable(); // Current level at previous school
            $table->date('previous_school_start_date')->nullable();
            $table->date('previous_school_end_date')->nullable();

            // Special Information
            $table->text('special_needs')->nullable();
            $table->text('learning_support_required')->nullable();
            $table->text('languages_spoken')->nullable(); // JSON array
            $table->text('interests_hobbies')->nullable();

            // Application Status
            $table->enum('status', [
                'draft',                    // Not yet submitted
                'submitted',                // Submitted, awaiting review
                'under_review',             // Being reviewed by admission team
                'observation_scheduled',    // Observation day scheduled
                'test_scheduled',           // Test scheduled
                'interview_scheduled',      // Interview scheduled
                'processing',               // Final decision processing
                'accepted',                 // Accepted
                'rejected',                 // Not accepted
                'waitlist',                 // On waitlist
                'enrolled',                 // Enrolled (paid full payment)
                'withdrawn',                // Withdrawn by parent
                'cancelled'                 // Cancelled by school
            ])->default('draft');

            $table->text('status_notes')->nullable(); // SuperAdmin notes for status changes

            // Important Dates
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('decision_made_at')->nullable();
            $table->timestamp('enrolled_at')->nullable();

            // Decision
            $table->text('decision_letter')->nullable(); // Generated letter content
            $table->string('decision_letter_file')->nullable(); // PDF file path

            // SuperAdmin Assignment
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();

            // Additional flags
            $table->boolean('requires_observation')->default(false);
            $table->boolean('requires_test')->default(false);
            $table->boolean('requires_interview')->default(true); // Default: all need interview

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('application_number');
            $table->index(['school_id', 'status']);
            $table->index(['school_id', 'academic_year_id', 'level_id']);
            $table->index(['user_id', 'status']);
            $table->fullText(
                ['student_first_name', 'student_last_name', 'application_number'],
                'applications_search_fulltext'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
