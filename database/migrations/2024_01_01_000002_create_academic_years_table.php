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
        Schema::create('academic_years', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            
            $table->string('name'); // 2024-2025
            $table->year('start_year'); // 2024
            $table->year('end_year'); // 2025
            $table->date('start_date'); // Flexible start date (e.g., August 1, 2024)
            $table->date('end_date'); // Flexible end date (e.g., July 31, 2025)
            
            $table->boolean('is_active')->default(false); // Only one active per school
            $table->text('description')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['school_id', 'is_active']);
            $table->unique(['school_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_years');
    }
};
