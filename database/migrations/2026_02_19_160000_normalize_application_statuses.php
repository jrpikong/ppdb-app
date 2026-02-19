<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE applications MODIFY status VARCHAR(50) NOT NULL DEFAULT 'draft'");
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE applications ALTER COLUMN status TYPE VARCHAR(50)');
            DB::statement("ALTER TABLE applications ALTER COLUMN status SET DEFAULT 'draft'");
        }

        DB::table('applications')->where('status', 'observation_scheduled')->update(['status' => 'interview_scheduled']);
        DB::table('applications')->where('status', 'test_scheduled')->update(['status' => 'interview_scheduled']);
        DB::table('applications')->where('status', 'processing')->update(['status' => 'under_review']);
        DB::table('applications')->where('status', 'waitlist')->update(['status' => 'waitlisted']);
        DB::table('applications')->where('status', 'cancelled')->update(['status' => 'withdrawn']);
    }

    public function down(): void
    {
        DB::table('applications')->where('status', 'waitlisted')->update(['status' => 'waitlist']);
    }
};
