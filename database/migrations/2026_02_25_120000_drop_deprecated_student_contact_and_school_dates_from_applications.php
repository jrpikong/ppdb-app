<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $columns = array_values(array_filter([
            'email',
            'phone',
            'previous_school_start_date',
            'previous_school_end_date',
        ], static fn (string $column): bool => Schema::hasColumn('applications', $column)));

        if ($columns === []) {
            return;
        }

        Schema::table('applications', function (Blueprint $table) use ($columns): void {
            $table->dropColumn($columns);
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table): void {
            if (! Schema::hasColumn('applications', 'email')) {
                $table->string('email')->nullable()->after('passport_number');
            }

            if (! Schema::hasColumn('applications', 'phone')) {
                $table->string('phone', 20)->nullable()->after('email');
            }

            if (! Schema::hasColumn('applications', 'previous_school_start_date')) {
                $table->date('previous_school_start_date')->nullable()->after('current_grade_level');
            }

            if (! Schema::hasColumn('applications', 'previous_school_end_date')) {
                $table->date('previous_school_end_date')->nullable()->after('previous_school_start_date');
            }
        });
    }
};
