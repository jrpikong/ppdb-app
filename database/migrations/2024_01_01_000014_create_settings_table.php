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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            
            // System Information
            $table->string('app_name')->default('VIS Admission System');
            $table->string('app_version')->default('1.0.0');
            
            // Multi-School Settings
            $table->boolean('multi_school_enabled')->default(false);
            $table->foreignId('default_school_id')->nullable()->constrained('schools')->nullOnDelete();
            
            // Admission Settings
            $table->boolean('online_admission_enabled')->default(true);
            $table->boolean('require_payment_before_submission')->default(true);
            $table->integer('application_review_days')->default(5); // Average review time
            
            // Email Settings
            $table->boolean('email_notifications_enabled')->default(true);
            $table->string('email_from_address')->nullable();
            $table->string('email_from_name')->default('VIS Admissions');
            $table->boolean('send_submission_confirmation')->default(true);
            $table->boolean('send_status_updates')->default(true);
            $table->boolean('send_interview_reminders')->default(true);
            $table->boolean('send_acceptance_letters')->default(true);
            
            // Payment Settings
            $table->string('default_currency', 3)->default('IDR');
            $table->text('payment_instructions')->nullable();
            
            // Document Requirements
            $table->json('required_documents')->nullable(); // JSON array of required document types
            $table->integer('max_file_size_mb')->default(10); // Max upload size per file
            $table->json('allowed_file_types')->nullable(); // ['pdf', 'jpg', 'png']
            
            // Interview Settings
            $table->boolean('auto_schedule_interviews')->default(false);
            $table->integer('interview_duration_minutes')->default(60);
            $table->integer('interview_buffer_minutes')->default(15);
            
            // Maintenance
            $table->boolean('maintenance_mode')->default(false);
            $table->text('maintenance_message')->nullable();
            
            // Additional Settings
            $table->json('extra_settings')->nullable(); // For future extensibility
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
