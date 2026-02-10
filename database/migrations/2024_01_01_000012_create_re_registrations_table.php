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
        Schema::create('re_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->constrained()->cascadeOnDelete();
            $table->foreignId('announcement_id')->constrained()->cascadeOnDelete();
            
            // Re-registration Info
            $table->string('re_registration_number')->unique(); // Nomor daftar ulang
            $table->date('re_registration_date'); // Tanggal daftar ulang
            
            // Payment Confirmation
            $table->decimal('total_payment', 12, 2); // Total yang dibayar
            $table->string('payment_proof')->nullable(); // Bukti pembayaran daftar ulang
            
            // Status
            $table->enum('status', [
                'pending', // Belum lengkap
                'completed', // Sudah lengkap
                'verified', // Sudah diverifikasi
                'rejected' // Ditolak
            ])->default('pending');
            
            $table->text('notes')->nullable();
            
            // Documents for re-registration
            $table->boolean('original_documents_submitted')->default(false);
            $table->text('submitted_documents')->nullable(); // JSON list
            
            // Verification
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Index
            $table->index(['registration_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('re_registrations');
    }
};
