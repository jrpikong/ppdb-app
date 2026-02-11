<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('levels', function (Blueprint $table) {
            $table->id();

            $table->foreignId('school_id')
                ->constrained()
                ->cascadeOnDelete();

            // Level Information
            $table->string('code'); // EP, PS, PK, G1, G2, ..., G9
            $table->string('name');

            $table->enum('program_category', [
                'early_years',
                'primary_years',
                'middle_years',
            ]);

            // Age Range (decimal allows months like 1.6 = 1 year 6 months)
            $table->decimal('age_min', 4, 1);
            $table->decimal('age_max', 4, 1);

            // Capacity & Fees
            $table->integer('quota')->default(0);
            $table->integer('annual_tuition_fee')->default(0);
            $table->integer('current_enrollment')->default(0);

            // Additional Info
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);

            // Status
            $table->boolean('is_active')->default(true);
            $table->boolean('is_accepting_applications')->default(true);

            $table->timestamps();
            $table->softDeletes();

            // ✅ Correct unique rule for multi-school system
            $table->unique(['school_id', 'code']);

            // Indexes for performance
            $table->index(['school_id', 'program_category']);
            $table->index(['school_id', 'is_active']);
            $table->index('code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('levels');
    }
};
