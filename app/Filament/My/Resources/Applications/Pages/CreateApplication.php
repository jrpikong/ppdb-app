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

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        $data['status'] = 'draft';

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $schoolId = (int) ($data['school_id'] ?? 0);

        if ($schoolId <= 0) {
            throw new RuntimeException('School is required to create an application.');
        }

        $maxAttempts = 5;
        $attempt = 0;

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
                $sqlState === '23000' ||
                $sqlState === '23505' ||
                $driverCode === '1062' ||
                $driverCode === '19'
            );
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->getRecord()]);
    }
}
