<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table): void {
            $table->string('student_first_name_key', 120)->nullable()->after('student_first_name');
            $table->string('student_last_name_key', 120)->nullable()->after('student_last_name');
            $table->unsignedTinyInteger('duplicate_guard')->nullable()->after('status');

            $table->index(['school_id', 'admission_period_id', 'birth_date'], 'applications_child_scope_idx');
        });

        $activeStatuses = [
            'draft',
            'submitted',
            'under_review',
            'documents_verified',
            'interview_scheduled',
            'interview_completed',
            'payment_pending',
            'payment_verified',
            'accepted',
            'waitlisted',
            'enrolled',
        ];

        DB::table('applications')
            ->select(['id', 'school_id', 'admission_period_id', 'birth_date', 'student_first_name', 'student_last_name', 'status'])
            ->orderBy('id')
            ->chunkById(500, function ($rows) use ($activeStatuses): void {
                foreach ($rows as $row) {
                    $firstNameKey = $this->normalizeName($row->student_first_name);
                    $lastNameKey = $this->normalizeName($row->student_last_name);

                    $isActive = in_array((string) $row->status, $activeStatuses, true);
                    $hasScope = filled($row->school_id)
                        && filled($row->admission_period_id)
                        && filled($row->birth_date)
                        && filled($firstNameKey)
                        && filled($lastNameKey);

                    DB::table('applications')
                        ->where('id', $row->id)
                        ->update([
                            'student_first_name_key' => $firstNameKey,
                            'student_last_name_key' => $lastNameKey,
                            'duplicate_guard' => ($isActive && $hasScope) ? 1 : null,
                        ]);
                }
            });

        $duplicates = DB::table('applications')
            ->select([
                'school_id',
                'admission_period_id',
                'birth_date',
                'student_first_name_key',
                'student_last_name_key',
                DB::raw('COUNT(*) as total'),
            ])
            ->where('duplicate_guard', 1)
            ->groupBy([
                'school_id',
                'admission_period_id',
                'birth_date',
                'student_first_name_key',
                'student_last_name_key',
            ])
            ->havingRaw('COUNT(*) > 1')
            ->get();

        if ($duplicates->isNotEmpty()) {
            $sample = $duplicates
                ->take(3)
                ->map(fn ($dup): string => sprintf(
                    '[school:%s period:%s birth:%s name:%s %s count:%s]',
                    $dup->school_id,
                    $dup->admission_period_id,
                    $dup->birth_date,
                    $dup->student_first_name_key,
                    $dup->student_last_name_key,
                    $dup->total
                ))
                ->implode(', ');

            throw new RuntimeException(
                'Duplicate active child applications detected. Resolve duplicates first, then rerun migration. Sample: '.$sample
            );
        }

        Schema::table('applications', function (Blueprint $table): void {
            $table->unique(
                [
                    'school_id',
                    'admission_period_id',
                    'birth_date',
                    'student_first_name_key',
                    'student_last_name_key',
                    'duplicate_guard',
                ],
                'applications_unique_active_child'
            );
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table): void {
            $table->dropUnique('applications_unique_active_child');
            $table->dropIndex('applications_child_scope_idx');
            $table->dropColumn([
                'student_first_name_key',
                'student_last_name_key',
                'duplicate_guard',
            ]);
        });
    }

    private function normalizeName(?string $name): ?string
    {
        if ($name === null) {
            return null;
        }

        $normalized = preg_replace('/\s+/', ' ', trim((string) $name));

        if ($normalized === '' || $normalized === null) {
            return null;
        }

        return mb_strtolower($normalized);
    }
};

