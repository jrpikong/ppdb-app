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
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_type_id')->constrained()->cascadeOnDelete();

            // Transaction Information
            $table->string('transaction_code')->unique(); // VIS-PAY-20240101-0001
            $table->decimal('amount', 15, 2); // Amount paid
            $table->string('currency', 3)->default('IDR');

            // Payment Details
            $table->date('payment_date'); // Date of payment
            $table->string('payment_method')->nullable(); // Bank Transfer, Credit Card, etc.
            $table->string('bank_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('reference_number')->nullable(); // Bank reference number

            // Proof of Payment
            $table->string('proof_file')->nullable(); // Uploaded proof image/PDF

            // Status
            $table->enum('status', [
                'pending',              // Payment not yet made
                'submitted',            // Proof uploaded, awaiting verification
                'verified',             // Verified by finance admin
                'rejected',             // Rejected (wrong amount, unclear proof, etc.)
                'refunded'              // Payment refunded
            ])->default('pending');

            $table->text('rejection_reason')->nullable();
            $table->text('notes')->nullable(); // SuperAdmin notes

            // Verification
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();

            // Refund (if applicable)
            $table->decimal('refund_amount', 15, 2)->nullable();
            $table->date('refund_date')->nullable();
            $table->text('refund_reason')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('transaction_code');
            $table->index(['application_id', 'status']);
            $table->index(['application_id', 'payment_type_id']);
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
