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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_type_id')->constrained()->cascadeOnDelete();
            
            // Transaction Info
            $table->string('transaction_code')->unique(); // Auto generated: 202307170001
            $table->decimal('amount', 12, 2); // Jumlah yang dibayar
            $table->date('payment_date'); // Tanggal bayar
            $table->string('payment_method')->nullable(); // Transfer, Cash, dll
            
            // Proof of Payment
            $table->string('proof_file')->nullable(); // Bukti pembayaran (foto)
            
            // Verification
            $table->enum('status', [
                'pending', // Belum dibayar
                'waiting_verification', // Proses cek
                'verified', // Sudah lunas/verified
                'rejected' // Ditolak
            ])->default('pending');
            
            $table->text('rejection_reason')->nullable();
            $table->text('notes')->nullable();
            
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('transaction_code');
            $table->index(['registration_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
