<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Make student biodata fields nullable so a parent can create a draft
     * application with only school / period / level, then complete the rest
     * via the edit wizard in subsequent steps.
     */
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table): void {
            // These were NOT NULL — relax them so the initial create (draft)
            // only requires school_id, admission_period_id, level_id.
            $table->string('student_first_name')->nullable()->change();
            $table->string('student_last_name')->nullable()->change();
            $table->date('birth_date')->nullable()->change();
            $table->string('nationality')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table): void {
            // Revert — WARNING: will fail if any row has NULL in these columns.
            $table->string('student_first_name')->nullable(false)->change();
            $table->string('student_last_name')->nullable(false)->change();
            $table->date('birth_date')->nullable(false)->change();
            $table->string('nationality')->nullable(false)->change();
        });
    }
};
