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
        Schema::create('registration_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // Gelombang 1, Gelombang 2
            $table->date('start_date'); // Tanggal buka pendaftaran
            $table->date('end_date'); // Tanggal tutup pendaftaran
            $table->date('announcement_date')->nullable(); // Tanggal pengumuman
            $table->date('re_registration_start')->nullable(); // Mulai daftar ulang
            $table->date('re_registration_end')->nullable(); // Akhir daftar ulang
            $table->boolean('is_active')->default(false);
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registration_periods');
    }
};
