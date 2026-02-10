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
            $table->string('code')->unique(); // 123
            $table->string('name'); // Seragam, Topi, dll
            $table->text('description')->nullable();
            $table->decimal('amount', 12, 2); // Nominal biaya
            $table->boolean('is_mandatory')->default(true); // Wajib atau tidak
            $table->boolean('is_active')->default(true);
            
            // Payment Instructions (WYSIWYG content)
            $table->longText('payment_instructions')->nullable();
            
            // Bank Account Info
            $table->string('bank_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('account_holder')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
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
