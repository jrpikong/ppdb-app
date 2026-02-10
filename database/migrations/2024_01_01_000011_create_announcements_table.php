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
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->constrained()->cascadeOnDelete();
            $table->foreignId('major_id')->constrained()->cascadeOnDelete(); // Lulus di jurusan mana
            
            // Announcement Details
            $table->string('announcement_number')->unique(); // Nomor pengumuman
            $table->enum('status', [
                'lulus', // Lulus pilihan 1
                'lulus_cadangan', // Lulus sebagai cadangan
                'lulus_pilihan_2', // Lulus pilihan 2 (realokasi)
                'tidak_lulus' // Tidak lulus
            ]);
            
            $table->integer('rank')->nullable(); // Peringkat kelulusan
            $table->decimal('final_score', 5, 2)->nullable(); // Nilai akhir
            
            // Announcement Dates
            $table->date('announced_at'); // Tanggal pengumuman
            $table->date('re_registration_deadline')->nullable(); // Deadline daftar ulang
            
            // Additional Info
            $table->text('notes')->nullable();
            $table->longText('announcement_letter')->nullable(); // Surat kelulusan (HTML/Text)
            
            // Notification
            $table->boolean('email_sent')->default(false);
            $table->timestamp('email_sent_at')->nullable();
            
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            
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
        Schema::dropIfExists('announcements');
    }
};
