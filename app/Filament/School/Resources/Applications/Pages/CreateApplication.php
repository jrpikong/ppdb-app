<?php

namespace App\Filament\School\Resources\Applications\Pages;

use App\Filament\School\Resources\Applications\ApplicationResource;
use App\Models\Application;
use App\Models\School;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CreateApplication extends CreateRecord
{
    protected static string $resource = ApplicationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $tenantId = Filament::getTenant()?->id;

        if (! $tenantId) {
            throw new RuntimeException('Tenant context is not available.');
        }

        // Auto-set school_id from current tenant
        $data['school_id'] = $tenantId;

        // Set default status
        $data['status'] = $data['status'] ?? 'draft';

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $maxAttempts = 5;
        $attempt = 0;

        do {
            try {
                return DB::transaction(function () use ($data): Model {
                    $school = Filament::getTenant();

                    if (! $school) {
                        throw new RuntimeException('Tenant context is not available.');
                    }

                    // Serialize number generation per school to prevent concurrent duplicates.
                    School::query()
                        ->whereKey($school->id)
                        ->lockForUpdate()
                        ->firstOrFail();

                    $data['application_number'] = $this->generateNextApplicationNumber($school);

                    return Application::create($data);
                }, 3);
            } catch (QueryException $exception) {
                if (! $this->isDuplicateApplicationNumberException($exception) || ++$attempt >= $maxAttempts) {
                    throw $exception;
                }

                usleep(random_int(10_000, 50_000));
            }
        } while (true);
    }

    protected function generateNextApplicationNumber(School $school): string
    {
        $year = now()->format('y');
        $month = now()->format('m');

        $lastApplication = Application::query()
            ->where('school_id', $school->id)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->latest('id')
            ->first();

        $lastSequence = 0;

        if ($lastApplication !== null && preg_match('/(\d{4})$/', $lastApplication->application_number, $matches) === 1) {
            $lastSequence = (int) $matches[1];
        }

        return sprintf('%s-%s%s-%04d', $school->code, $year, $month, $lastSequence + 1);
    }

    protected function isDuplicateApplicationNumberException(QueryException $exception): bool
    {
        $message = strtolower($exception->getMessage());
        $sqlState = (string) ($exception->errorInfo[0] ?? '');
        $driverCode = (string) ($exception->errorInfo[1] ?? '');

        return str_contains($message, 'application_number')
            && (
                $sqlState === '23000' || // MySQL/MariaDB/SQL Server integrity violation
                $sqlState === '23505' || // PostgreSQL unique violation
                $driverCode === '1062' || // MySQL duplicate entry
                $driverCode === '19' // SQLite constraint violation
            );
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Application created successfully';
    }
}
