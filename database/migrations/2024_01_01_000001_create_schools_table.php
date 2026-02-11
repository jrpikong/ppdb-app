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
        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            
            // Basic Information
            $table->string('code')->unique(); // VIS-BIN, VIS-KG, VIS-BALI
            $table->string('name'); // VIS Bintaro, VIS Kelapa Gading, VIS Bali
            $table->string('full_name')->nullable(); // Veritas Intercultural School - Bintaro
            $table->enum('type', ['main', 'branch'])->default('branch');
            
            // Contact Information
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('website')->nullable();
            
            // Location
            $table->string('city'); // Jakarta, Bali
            $table->string('country')->default('Indonesia');
            $table->text('address')->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->string('timezone')->default('Asia/Jakarta');
            
            // Branding
            $table->string('logo')->nullable();
            $table->string('banner')->nullable();
            $table->text('description')->nullable();
            
            // Principal/Head of School
            $table->string('principal_name')->nullable();
            $table->string('principal_email')->nullable();
            $table->string('principal_signature')->nullable(); // Path to signature image
            
            // Settings
            $table->boolean('is_active')->default(true);
            $table->boolean('allow_online_admission')->default(true);
            $table->json('settings')->nullable(); // Extra settings
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('code');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schools');
    }
};
