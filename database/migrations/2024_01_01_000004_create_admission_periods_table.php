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
        Schema::create('admission_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();

            // Period Information
            $table->string('name'); // "2024-2025 Intake 1", "Rolling Admission"
            $table->date('start_date'); // Application opening date
            $table->date('end_date'); // Application closing date

            // Important Dates
            $table->date('decision_date')->nullable(); // When decisions are sent
            $table->date('enrollment_deadline')->nullable(); // Deadline for enrollment

            // Settings
            $table->boolean('is_active')->default(false); // Only one active per school
            $table->boolean('allow_applications')->default(false); // Only one active per school
            $table->boolean('is_rolling')->default(false); // Rolling admission (no fixed end date)
            $table->json('settings')->nullable();
            $table->text('description')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['school_id', 'academic_year_id']);
            $table->index(['school_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admission_periods');
    }
};
