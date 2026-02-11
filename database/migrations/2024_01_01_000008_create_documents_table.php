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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            
            // Document Type
            $table->enum('type', [
                // Student Photos
                'student_photo_1',          // First student photo (3x4 cm)
                'student_photo_2',          // Second student photo (3x4 cm)
                
                // Parent Photos
                'father_photo',             // Father photo (3x4 cm)
                'mother_photo',             // Mother photo (3x4 cm)
                'guardian_photo',           // Guardian photo (if applicable)
                
                // Identification Documents
                'father_id_card',           // Father's ID card/Passport
                'mother_id_card',           // Mother's ID card/Passport
                'guardian_id_card',         // Guardian's ID card (if applicable)
                
                // Student Documents
                'birth_certificate',        // Student's birth certificate
                'family_card',              // Family registration card
                'passport',                 // Student's passport (if applicable)
                
                // Academic Documents
                'latest_report_book',       // Latest school report
                'previous_report_books',    // Previous reports (can be multiple)
                'recommendation_letter',    // Teacher recommendation
                'transcript',               // Academic transcript
                
                // Medical & Special Needs
                'medical_history',          // Medical history form (PDF)
                'special_needs_form',       // Special needs assessment
                'immunization_record',      // Vaccination records
                
                // Other
                'other'                     // Other supporting documents
            ]);
            
            // File Information
            $table->string('name'); // Original filename
            $table->string('file_path'); // Storage path
            $table->string('file_type'); // MIME type
            $table->integer('file_size'); // Size in bytes
            $table->text('description')->nullable();
            
            // Verification
            $table->enum('status', [
                'pending',      // Awaiting verification
                'approved',     // Verified and approved
                'rejected',     // Rejected
                'resubmit'      // Needs resubmission
            ])->default('pending');
            
            $table->text('rejection_reason')->nullable();
            $table->text('verification_notes')->nullable();
            
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['application_id', 'type']);
            $table->index(['application_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
