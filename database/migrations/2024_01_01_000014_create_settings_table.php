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
            
            // School Information
            $table->string('school_name')->default('MTS NEGERI 1 WONOGIRI');
            $table->string('school_nsm')->nullable(); // NSM: 121133120001
            $table->string('school_npsn')->nullable(); // NPSN: 20363813
            $table->string('school_level')->default('SMP/MTS'); // Jenjang sekolah
            $table->enum('school_status', ['negeri', 'swasta'])->default('negeri');
            
            // Contact
            $table->string('school_phone')->nullable();
            $table->string('school_email')->nullable();
            $table->string('school_website')->nullable();
            
            // Address
            $table->text('school_address')->nullable();
            $table->string('school_province')->nullable();
            $table->string('school_regency')->nullable();
            $table->string('school_district')->nullable();
            $table->string('school_village')->nullable();
            $table->string('school_postal_code')->nullable();
            
            // Branding
            $table->string('school_logo')->nullable(); // Path to logo
            $table->string('school_header_image')->nullable(); // Header/banner
            $table->text('school_description')->nullable();
            $table->text('school_vision')->nullable();
            $table->text('school_mission')->nullable();
            
            // Head of School
            $table->string('principal_name')->nullable();
            $table->string('principal_nip')->nullable();
            $table->string('principal_signature')->nullable(); // Path to signature image
            
            // PPDB Settings
            $table->boolean('registration_open')->default(true);
            $table->text('registration_info')->nullable(); // Info di halaman pendaftaran
            $table->integer('min_age')->default(12); // Minimal umur
            $table->integer('max_age')->default(18); // Maksimal umur
            
            // Email Settings
            $table->boolean('email_notification_enabled')->default(true);
            $table->string('email_from_address')->nullable();
            $table->string('email_from_name')->nullable();
            
            // Other Settings
            $table->json('extra_settings')->nullable(); // For future settings
            
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
