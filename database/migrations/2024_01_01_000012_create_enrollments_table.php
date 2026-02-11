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
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->unique()->constrained()->cascadeOnDelete();
            
            // Student ID (Generated upon enrollment)
            $table->string('student_id')->unique(); // VIS-BIN-2024-S-0001
            
            // Enrollment Information
            $table->string('enrollment_number')->unique(); // ENR-2024-0001
            $table->date('enrollment_date');
            $table->date('start_date'); // Expected start date (first day of school)
            
            // Class Assignment (can be updated)
            $table->string('class_name')->nullable(); // e.g., "Grade 1A", "Preschool Sunshine"
            $table->string('homeroom_teacher')->nullable();
            
            // Total Payment Summary
            $table->decimal('total_amount_due', 15, 2);
            $table->decimal('total_amount_paid', 15, 2);
            $table->decimal('balance', 15, 2); // Remaining balance
            $table->string('payment_status')->default('pending'); // pending, partial, paid
            
            // Status
            $table->enum('status', [
                'enrolled',         // Successfully enrolled
                'active',           // Currently active student
                'completed',        // Completed the level
                'transferred',      // Transferred to another school
                'withdrawn',        // Withdrawn
                'expelled',         // Expelled
                'graduated'         // Graduated from school
            ])->default('enrolled');
            
            // Important Dates
            $table->date('withdrawal_date')->nullable();
            $table->text('withdrawal_reason')->nullable();
            
            // Notes
            $table->text('notes')->nullable();
            
            // Audit
            $table->foreignId('enrolled_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('student_id');
            $table->index('enrollment_number');
            $table->index(['application_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};
