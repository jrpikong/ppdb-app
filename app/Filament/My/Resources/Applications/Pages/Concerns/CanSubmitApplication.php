<?php

declare(strict_types=1);

namespace App\Filament\My\Resources\Applications\Pages\Concerns;

use App\Models\Application;
use App\Models\Document;
use Filament\Notifications\Notification;
use RuntimeException;

trait CanSubmitApplication
{
    /**
     * @return array<int, string>
     */
    protected function getRequiredDocumentTypes(?Application $record = null): array
    {
        if ($record) {
            return $record->getRequiredDocumentTypes();
        }

        return Application::REQUIRED_DOCUMENT_TYPES;
    }

    /**
     * @return array<int, string>
     */
    protected function getMissingRequiredDocuments(Application $record): array
    {
        $uploaded = $record->documents()->pluck('type')->toArray();

        return array_values(array_diff($this->getRequiredDocumentTypes($record), $uploaded));
    }

    protected function submitApplication(Application $record): void
    {
        if ($record->status !== 'draft') {
            Notification::make()
                ->title('Application cannot be submitted')
                ->body('Only draft applications can be submitted.')
                ->danger()
                ->send();

            return;
        }

        $errors = $this->validateApplicationForSubmit($record);

        if (! empty($errors)) {
            Notification::make()
                ->title('Application is incomplete')
                ->body(implode("\n", $errors))
                ->danger()
                ->persistent()
                ->send();

            return;
        }

        try {
            $changed = $record->submit(auth()->id());
        } catch (RuntimeException $e) {
            Notification::make()
                ->title('Application cannot be submitted')
                ->body($e->getMessage())
                ->danger()
                ->send();

            return;
        }

        if (! $changed) {
            Notification::make()
                ->title('Application already submitted')
                ->body('This application has already been submitted.')
                ->warning()
                ->send();

            return;
        }

        Notification::make()
            ->title('Application submitted')
            ->body('Your application has been submitted for school review.')
            ->success()
            ->send();
    }

    /**
     * @return array<int, string>
     */
    protected function validateApplicationForSubmit(Application $record): array
    {
        $errors = [];

        $requiredFields = [
            'school_id' => 'School',
            'admission_period_id' => 'Admission Period',
            'level_id' => 'Applying Level/Grade',
            'student_first_name' => 'Student First Name',
            'student_last_name' => 'Student Last Name',
            'gender' => 'Gender',
            'birth_date' => 'Birth Date',
            'nationality' => 'Nationality',
            'email' => 'Student Email',
            'phone' => 'Student Phone',
            'current_address' => 'Current Address',
            'current_city' => 'Current City',
            'current_country' => 'Current Country',
        ];

        foreach ($requiredFields as $field => $label) {
            if (blank($record->{$field})) {
                $errors[] = "- {$label} is required.";
            }
        }

        if ($record->parentGuardians()->count() < 2) {
            $errors[] = '- Add at least two parent/guardian contacts.';
        }

        $missingRequiredDocs = $this->getMissingRequiredDocuments($record);

        if ($missingRequiredDocs !== []) {
            $requiredCount = count($this->getRequiredDocumentTypes($record));

            $missingLabels = collect($missingRequiredDocs)
                ->map(fn (string $type): string => Document::DOCUMENT_TYPES[$type] ?? (string) str($type)->replace('_', ' ')->title())
                ->implode(', ');

            $errors[] = "- Upload all {$requiredCount} required documents before submitting.";
            $errors[] = "- Missing documents: {$missingLabels}.";
        }

        if (! $record->hasVerifiedPreSubmissionPayment()) {
            $errors[] = '- Saving Seat payment must be verified before submitting.';
        }

        if ($record->getCompletionPercentage() < 100) {
            $errors[] = '- Application completeness must reach 100% before submitting.';
        }

        if (
            filled($record->student_first_name) &&
            filled($record->student_last_name) &&
            filled($record->birth_date) &&
            filled($record->school_id) &&
            filled($record->admission_period_id)
        ) {
            $duplicateExists = Application::query()
                ->whereKeyNot($record->id)
                ->whereIn('status', Application::activeStatuses())
                ->where('school_id', $record->school_id)
                ->where('admission_period_id', $record->admission_period_id)
                ->whereDate('birth_date', $record->birth_date)
                ->whereRaw('LOWER(student_first_name) = ?', [strtolower(trim((string) $record->student_first_name))])
                ->whereRaw('LOWER(student_last_name) = ?', [strtolower(trim((string) $record->student_last_name))])
                ->exists();

            if ($duplicateExists) {
                $errors[] = '- This child already has an active application in the same school and admission period.';
            }
        }

        if (! $record->canBeSubmitted()) {
            $errors[] = '- Application is not yet eligible for submission.';
        }

        return $errors;
    }
}
