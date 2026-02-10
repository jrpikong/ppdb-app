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
        Schema::create('scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->constrained()->cascadeOnDelete();
            
            // Nilai Rapor (Semester 1-5)
            $table->decimal('rapor_semester_1', 5, 2)->nullable();
            $table->decimal('rapor_semester_2', 5, 2)->nullable();
            $table->decimal('rapor_semester_3', 5, 2)->nullable();
            $table->decimal('rapor_semester_4', 5, 2)->nullable();
            $table->decimal('rapor_semester_5', 5, 2)->nullable();
            $table->decimal('rapor_average', 5, 2)->nullable(); // Rata-rata otomatis
            
            // Nilai Ujian (jika ada)
            $table->decimal('exam_math', 5, 2)->nullable();
            $table->decimal('exam_science', 5, 2)->nullable();
            $table->decimal('exam_indonesian', 5, 2)->nullable();
            $table->decimal('exam_english', 5, 2)->nullable();
            $table->decimal('exam_religion', 5, 2)->nullable();
            $table->decimal('exam_average', 5, 2)->nullable();
            
            // Nilai Prestasi/Portofolio
            $table->decimal('achievement_score', 5, 2)->default(0); // Bonus poin
            $table->text('achievement_description')->nullable();
            
            // Total & Ranking
            $table->decimal('total_score', 5, 2)->nullable(); // Total semua nilai
            $table->integer('rank')->nullable(); // Peringkat
            $table->integer('rank_in_major')->nullable(); // Peringkat per jurusan
            
            // Passing Status
            $table->boolean('is_passed')->default(false);
            $table->text('notes')->nullable();
            
            $table->foreignId('inputted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('inputted_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Index
            $table->index('total_score');
            $table->index('rank');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scores');
    }
};
