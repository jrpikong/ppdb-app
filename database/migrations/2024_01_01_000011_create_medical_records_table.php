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
        Schema::create('medical_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            
            // Basic Medical Information
            $table->enum('blood_type', ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-', 'unknown'])->nullable();
            $table->decimal('height', 5, 2)->nullable(); // in cm
            $table->decimal('weight', 5, 2)->nullable(); // in kg
            
            // Allergies
            $table->text('allergies')->nullable(); // Food, medicine, environmental
            $table->boolean('has_food_allergies')->default(false);
            $table->text('food_allergies_details')->nullable();
            
            // Medical Conditions
            $table->boolean('has_medical_conditions')->default(false);
            $table->text('medical_conditions')->nullable(); // Asthma, diabetes, etc.
            $table->boolean('requires_daily_medication')->default(false);
            $table->text('daily_medications')->nullable();
            
            // Dietary Requirements
            $table->boolean('has_dietary_restrictions')->default(false);
            $table->text('dietary_restrictions')->nullable(); // Vegetarian, halal, etc.
            
            // Special Needs
            $table->boolean('has_special_needs')->default(false);
            $table->text('special_needs_description')->nullable();
            $table->boolean('requires_learning_support')->default(false);
            $table->text('learning_support_details')->nullable();
            
            // Immunizations
            $table->boolean('immunizations_up_to_date')->default(false);
            $table->text('immunization_records')->nullable(); // JSON or text
            
            // Emergency Information
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_relationship')->nullable();
            $table->string('emergency_contact_phone', 20)->nullable();
            $table->string('emergency_contact_email')->nullable();
            
            $table->string('doctor_name')->nullable();
            $table->string('doctor_phone', 20)->nullable();
            $table->string('hospital_preference')->nullable();
            $table->string('health_insurance_provider')->nullable();
            $table->string('health_insurance_number')->nullable();
            
            // Additional Notes
            $table->text('additional_notes')->nullable();
            
            // Document Reference
            $table->foreignId('medical_form_document_id')->nullable()->constrained('documents')->nullOnDelete();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('application_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_records');
    }
};
