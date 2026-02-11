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
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();

            // Schedule Type
            $table->enum('type', [
                'observation',  // Observation day/trial
                'test',         // Assessment test
                'interview'     // Parent & student interview
            ]);

            // Schedule Details
            $table->date('scheduled_date');
            $table->time('scheduled_time');
            $table->integer('duration_minutes')->default(60); // Duration in minutes

            // Location
            $table->string('location')->nullable(); // Room, building, or online link
            $table->text('location_details')->nullable();
            $table->boolean('is_online')->default(false);
            $table->string('online_meeting_link')->nullable();

            // Assigned Staff
            $table->foreignId('interviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('assigned_staff')->nullable(); // JSON: multiple staff if needed

            // Status
            $table->enum('status', [
                'scheduled',    // Scheduled
                'confirmed',    // Confirmed by parent
                'completed',    // Completed
                'cancelled',    // Cancelled
                'rescheduled',  // Rescheduled (new schedule created)
                'no_show'       // Student didn't show up
            ])->default('scheduled');

            // Results/Notes
            $table->text('notes')->nullable(); // SuperAdmin notes before event
            $table->text('result')->nullable(); // Assessment result/interview notes
            $table->integer('score')->nullable(); // Numeric score (if applicable)
            $table->enum('recommendation', ['recommended', 'not_recommended', 'pending'])->nullable();

            // Notification
            $table->boolean('notification_sent')->default(false);
            $table->timestamp('notification_sent_at')->nullable();
            $table->boolean('reminder_sent')->default(false);
            $table->timestamp('reminder_sent_at')->nullable();

            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['application_id', 'type']);
            $table->index(['application_id', 'status']);
            $table->index(['scheduled_date', 'scheduled_time']);
            $table->index('interviewer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
