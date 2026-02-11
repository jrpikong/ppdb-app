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
        Schema::table('users', function (Blueprint $table) {
            // Add school relationship for staff users
            $table->unsignedBigInteger('school_id')
                ->default(0)
                ->after('id')
                ->index();
            // Additional fields for international school
            $table->string('phone', 20)->nullable()->after('email');
            $table->string('avatar')->nullable()->after('phone');
            $table->boolean('is_active')->default(true)->after('avatar');

            // Staff-specific fields
            $table->string('employee_id')->nullable()->after('is_active');
            $table->string('department')->nullable();

            // Soft deletes
            $table->softDeletes();

            // Indexes
            $table->index(['school_id', 'is_active']);
            $table->index('employee_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['school_id']);
            $table->dropIndex(['school_id', 'is_active']);
            $table->dropIndex(['employee_id']);

            $table->dropColumn([
                'school_id',
                'phone',
                'avatar',
                'is_active',
                'employee_id',
                'department',
                'deleted_at'
            ]);
        });
    }
};
