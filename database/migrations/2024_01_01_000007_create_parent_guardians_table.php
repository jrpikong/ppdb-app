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
        Schema::create('parent_guardians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            
            // Parent Type
            $table->enum('type', ['father', 'mother', 'guardian']);
            
            // Personal Information
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('relationship')->nullable(); // For guardians: Uncle, Aunt, etc.
            
            // Contact Information
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('mobile', 20)->nullable();
            
            // Identification
            $table->string('id_type')->nullable(); // Passport, National ID, etc.
            $table->string('id_number')->nullable();
            $table->string('nationality')->nullable();
            
            // Employment Information
            $table->string('occupation')->nullable();
            $table->string('company_name')->nullable();
            $table->string('company_address')->nullable();
            $table->string('work_phone', 20)->nullable();
            
            // Address (if different from student)
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->string('postal_code', 10)->nullable();
            
            // Emergency Contact
            $table->boolean('is_emergency_contact')->default(false);
            $table->boolean('is_primary_contact')->default(false);
            
            // Additional
            $table->text('notes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['application_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parent_guardians');
    }
};
