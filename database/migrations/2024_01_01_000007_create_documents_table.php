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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->constrained()->cascadeOnDelete();
            
            // Document Info
            $table->enum('type', [
                'foto_siswa', // Foto 3x4
                'kartu_keluarga', // KK
                'akta_kelahiran', // Akta
                'ijazah', // Ijazah/SKHUN
                'kartu_indonesia_pintar', // KIP
                'rapor_semester_1',
                'rapor_semester_2',
                'rapor_semester_3',
                'rapor_semester_4',
                'rapor_semester_5',
                'surat_keterangan_lulus',
                'sertifikat_prestasi',
                'other' // Dokumen lainnya
            ]);
            
            $table->string('name'); // Original filename
            $table->string('file_path'); // Storage path
            $table->string('file_type'); // mime type
            $table->integer('file_size'); // in bytes
            $table->text('description')->nullable();
            
            // Verification
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            
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
        Schema::dropIfExists('documents');
    }
};
