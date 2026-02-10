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
        Schema::create('parents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['ayah', 'ibu', 'wali']); // Father, Mother, Guardian
            
            // Personal Info
            $table->string('name')->nullable();
            $table->string('nik', 16)->nullable();
            $table->string('birth_place')->nullable();
            $table->date('birth_date')->nullable();
            $table->enum('status', ['masih_hidup', 'sudah_meninggal', 'tidak_diketahui'])->nullable();
            $table->enum('citizenship', ['wni', 'wna'])->nullable();
            
            // Education
            $table->enum('education', [
                'tidak_sekolah',
                'sd',
                'smp',
                'sma',
                'diploma',
                'sarjana',
                'magister',
                'doktor'
            ])->nullable();
            
            // Occupation
            $table->string('occupation')->nullable();
            $table->decimal('monthly_income', 12, 2)->nullable();
            
            // Contact
            $table->string('phone', 15)->nullable();
            $table->string('email')->nullable();
            
            // Address (if different from student)
            $table->enum('living_location', ['dalam_negeri', 'luar_negeri'])->nullable();
            $table->text('address')->nullable();
            $table->string('province')->nullable();
            $table->string('regency')->nullable();
            $table->string('district')->nullable();
            $table->string('village')->nullable();
            $table->string('postal_code', 10)->nullable();
            
            // Guardian Specific (if type = wali)
            $table->string('relationship')->nullable(); // Hubungan dengan siswa
            $table->string('kk_number', 20)->nullable(); // No. KK Kepala Keluarga
            
            $table->timestamps();
            $table->softDeletes();
            
            // Index
            $table->index(['registration_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parents');
    }
};
