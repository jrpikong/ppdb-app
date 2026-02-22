<?php

declare(strict_types=1);

namespace App\Filament\My\Resources\Applications\Pages;

use App\Filament\My\Resources\Applications\ApplicationResource;
use App\Models\Application;
use App\Models\School;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CreateApplication extends CreateRecord
{
    protected static string $resource = ApplicationResource::class;

    // ──────────────────────────────────────────────────────────────────────────
    // After saving the draft, redirect straight into the edit wizard so the
    // parent can continue filling in student biodata, guardians, documents, etc.
    // ──────────────────────────────────────────────────────────────────────────
    protected function getRedirectUrl(): string
    {
        $resource = $this->getResource();
        $record = $this->getRecord();

        if ($record && $resource::canEdit($record)) {
            return $resource::getUrl('edit', ['record' => $record]);
        }

        if ($record && $resource::canView($record)) {
            return $resource::getUrl('view', ['record' => $record]);
        }

        return $resource::getUrl('index');
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Inject user_id and status before Eloquent::create()
    // ──────────────────────────────────────────────────────────────────────────
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        $data['status']  = 'draft';

        // These fields are required by the DB schema (NOT NULL).
        // We fill them with safe placeholder values so the draft row can be
        // inserted. The parent will overwrite them in the edit wizard.
        // NOTE: if you run the companion migration that makes them nullable,
        //       these fallbacks become redundant — but keeping them is harmless.
        $data['student_first_name'] = $data['student_first_name'] ?? '';
        $data['student_last_name']  = $data['student_last_name']  ?? '';
        $data['birth_date']         = $data['birth_date']         ?? null;
        $data['nationality']        = $data['nationality']         ?? '';

        return $data;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Custom creation handler: generates a unique application number with
    // optimistic concurrency / retry to prevent duplicate key collisions.
    // ──────────────────────────────────────────────────────────────────────────
    protected function handleRecordCreation(array $data): Model
    {
        $schoolId = (int) ($data['school_id'] ?? 0);

        if ($schoolId <= 0) {
            throw new RuntimeException('School is required to create an application.');
        }

        $maxAttempts = 5;
        $attempt     = 0;

        do {
            try {
                return DB::transaction(function () use ($data, $schoolId): Model {
                    $school = School::query()
                        ->whereKey($schoolId)
                        ->lockForUpdate()
                        ->firstOrFail();

                    $data['application_number'] = $this->generateNextApplicationNumber($school);

                    return Application::query()->create($data);
                }, 3);
            } catch (QueryException $e) {
                if (! $this->isDuplicateApplicationNumberException($e) || ++$attempt >= $maxAttempts) {
                    throw $e;
                }

                usleep(random_int(10_000, 50_000));
            }
        } while (true);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Application number format: {SCHOOL-CODE}-{YY}{MM}-{SEQUENCE:04d}
    // e.g.  VIS-BALI-2602-0005
    // ──────────────────────────────────────────────────────────────────────────
    private function generateNextApplicationNumber(School $school): string
    {
        $year  = now()->format('y');
        $month = now()->format('m');

        $lastApplication = Application::query()
            ->where('school_id', $school->id)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->latest('id')
            ->first();

        $lastSequence = 0;

        if (
            $lastApplication !== null
            && preg_match('/(\d{4})$/', (string) $lastApplication->application_number, $matches) === 1
        ) {
            $lastSequence = (int) $matches[1];
        }

        return sprintf('%s-%s%s-%04d', $school->code, $year, $month, $lastSequence + 1);
    }

    private function isDuplicateApplicationNumberException(QueryException $e): bool
    {
        $message  = strtolower($e->getMessage());
        $sqlState = (string) ($e->errorInfo[0] ?? '');
        $code     = (int)    ($e->errorInfo[1] ?? 0);

        // MySQL: 1062 / SQLSTATE 23000 — duplicate entry
        // PostgreSQL: 23505 — unique violation
        return $code === 1062
            || $sqlState === '23000'
            || $sqlState === '23505'
            || str_contains($message, 'application_number');
    }

    // ──────────────────────────────────────────────────────────────────────────
    // UX: show a friendly success notification before redirect
    // ──────────────────────────────────────────────────────────────────────────
    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Draft application created — please complete the form below.';
    }
}
