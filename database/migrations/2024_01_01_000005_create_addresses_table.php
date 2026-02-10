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
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->constrained()->cascadeOnDelete();
            
            // Address Components
            $table->string('province'); // Bengkulu
            $table->string('province_code', 10)->nullable();
            $table->string('regency'); // Seluma
            $table->string('regency_code', 10)->nullable();
            $table->string('district'); // Air Periukan
            $table->string('district_code', 10)->nullable();
            $table->string('village'); // Kembang Seri
            $table->string('village_code', 10)->nullable();
            
            // Detail Address
            $table->text('street_address'); // Jl Seluma Barat
            $table->string('rt', 5)->nullable();
            $table->string('rw', 5)->nullable();
            $table->string('postal_code', 10)->nullable(); // 39988
            $table->string('coordinates')->nullable(); // lat,long: 2412312412,1231231234
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
