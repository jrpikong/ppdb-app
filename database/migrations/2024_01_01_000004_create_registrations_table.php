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
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('registration_period_id')->constrained()->cascadeOnDelete();
            $table->foreignId('major_id')->constrained()->cascadeOnDelete(); // Pilihan 1
            $table->foreignId('major_id_second')->nullable()->constrained('majors')->nullOnDelete(); // Pilihan 2

            // Registration Info
            $table->string('registration_number')->unique(); // Auto generated
            $table->enum('registration_type', ['siswa_baru', 'pindahan'])->default('siswa_baru');

            // Personal Data
            $table->string('nisn', 20)->nullable(); // Changed from 10 to 20
            $table->string('nik', 20)->nullable();  // Changed from 16 to 20
            $table->string('nis_lokal', 20)->nullable();
            $table->string('full_name');
            $table->enum('gender', ['laki-laki', 'perempuan']);
            $table->string('birth_place');
            $table->date('birth_date');
            $table->enum('religion', ['islam', 'kristen', 'katolik', 'hindu', 'buddha', 'konghucu']);
            $table->enum('citizenship', ['wni', 'wna'])->default('wni');
            $table->integer('child_number')->nullable(); // Anak ke-
            $table->integer('siblings_count')->nullable(); // Jumlah saudara
            $table->string('phone', 15)->nullable();
            $table->string('email')->nullable();
            $table->string('hobby')->nullable();

            // Previous School Data
            $table->string('previous_school')->nullable();
            $table->string('npsn_previous_school', 20)->nullable();

            // Living Status
            $table->enum('living_status', ['dengan_orang_tua', 'dengan_wali', 'kos', 'asrama'])->nullable();

            // Transportation & Distance
            $table->enum('transportation', ['jalan_kaki', 'sepeda', 'motor', 'mobil', 'angkutan_umum'])->nullable();
            $table->decimal('distance_to_school', 5, 2)->nullable(); // in KM
            $table->integer('travel_time')->nullable(); // in minutes

            // Financial Assistance
            $table->boolean('has_kip')->default(false); // Kartu Indonesia Pintar
            $table->string('kip_number')->nullable();
            $table->boolean('has_kks')->default(false); // Kartu Keluarga Sejahtera
            $table->string('kks_number')->nullable();
            $table->boolean('has_pkh')->default(false); // Program Keluarga Harapan
            $table->string('pkh_number')->nullable();

            // Special Needs
            $table->text('special_needs')->nullable(); // Kebutuhan khusus/disabilitas

            // Pre-School Experience
            $table->boolean('has_tk_ra')->default(false);
            $table->boolean('has_paud')->default(false);

            // Immunization
            $table->boolean('immunization_hepatitis_b')->default(false);
            $table->boolean('immunization_bcg')->default(false);
            $table->boolean('immunization_dpt')->default(false);
            $table->boolean('immunization_polio')->default(false);
            $table->boolean('immunization_campak')->default(false);
            $table->boolean('immunization_covid')->default(false);

            // Status Tracking
            $table->enum('status', [
                'draft', // Belum submit
                'submitted', // Sudah submit, belum verifikasi
                'verified', // Sudah diverifikasi
                'rejected', // Ditolak
                'passed', // Lulus seleksi
                'failed', // Tidak lulus
                're_registered', // Sudah daftar ulang
                'cancelled' // Dibatalkan
            ])->default('draft');

            $table->text('rejection_reason')->nullable(); // Alasan ditolak
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('nisn');
            $table->index('nik');
            $table->index('registration_number');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
