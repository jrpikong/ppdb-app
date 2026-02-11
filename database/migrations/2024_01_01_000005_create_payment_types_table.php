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
        Schema::create('payment_types', function (Blueprint $table) {
            $table->id();

            $table->foreignId('school_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('code'); // SAVING_SEAT, REGISTRATION, etc
            $table->string('name');
            $table->text('description')->nullable();

            $table->integer('amount');

            $table->enum('payment_stage', [
                'pre_submission',
                'post_acceptance',
                'enrollment',
                'installment',
            ]);

            $table->boolean('is_mandatory')->default(false);
            $table->boolean('is_refundable')->default(false);
            $table->boolean('is_active')->default(true);

            $table->json('bank_info')->nullable();
            $table->longText('payment_instructions')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // ✅ correct uniqueness
            $table->unique(['school_id', 'code']);

            // indexes
            $table->index(['school_id', 'payment_stage']);
            $table->index(['school_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_types');
    }
};
