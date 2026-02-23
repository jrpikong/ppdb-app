<?php

declare(strict_types=1);

namespace App\Filament\My\Resources\Applications\Pages;

use App\Filament\My\Resources\Applications\ApplicationResource;
use App\Filament\My\Resources\Applications\Pages\Concerns\CanSubmitApplication;
use App\Models\Application;
use App\Models\Document;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewApplication extends ViewRecord
{
    use CanSubmitApplication;

    protected static string $resource = ApplicationResource::class;

    protected string $view = 'filament.my.resources.applications.pages.view-application';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadAcceptanceLetter')
                ->label('Download Acceptance Letter')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->url(fn () => route('secure-files.applications.acceptance-letter', $this->getRecord()))
                ->openUrlInNewTab()
                ->visible(fn () => in_array($this->getRecord()->status, ['accepted', 'enrolled'], true)),
            EditAction::make()
                ->visible(fn () => $this->getRecord()->status === 'draft'),
            Action::make('submitApplication')
                ->label('Submit Application')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn () => $this->getRecord()->status === 'draft')
                ->action(function (): void {
                    $record = $this->getRecord();
                    $this->submitApplication($record);

                    $record->refresh();
                    $this->refreshFormData(['status', 'submitted_at']);
                }),
        ];
    }

    public function getRecordForView(): Application
    {
        /** @var Application $record */
        $record = $this->getRecord();

        return $record->loadMissing([
            'school',
            'admissionPeriod',
            'level',
            'parentGuardians',
            'documents',
            'payments.paymentType',
            'schedules.interviewer',
            'medicalRecord',
            'enrollment',
        ]);
    }

    /**
     * @return array<int, string>
     */
    public function getReadinessErrors(): array
    {
        return $this->validateApplicationForSubmit($this->getRecordForView());
    }

    /**
     * @return array<int, string>
     */
    public function getRequiredDocumentTypes(): array
    {
        return [
            'student_photo_1',
            'student_photo_2',
            'father_photo',
            'mother_photo',
            'father_id_card',
            'mother_id_card',
            'birth_certificate',
            'family_card',
            'latest_report_book',
        ];
    }

    /**
     * @return array{required:int, uploaded:int}
     */
    public function getRequiredDocumentStats(): array
    {
        $record = $this->getRecordForView();
        $required = $this->getRequiredDocumentTypes();
        $uploadedTypes = $record->documents->pluck('type')->toArray();
        $uploaded = 0;

        foreach ($required as $type) {
            if (in_array($type, $uploadedTypes, true)) {
                $uploaded++;
            }
        }

        return [
            'required' => count($required),
            'uploaded' => $uploaded,
        ];
    }

    /**
     * @return array{label:string,bg:string,text:string,border:string}
     */
    public function getStatusBadgeUi(string $status): array
    {
        return match ($status) {
            'draft' => ['label' => 'Draft', 'bg' => '#f8fafc', 'text' => '#475569', 'border' => '#cbd5e1'],
            'submitted' => ['label' => 'Submitted', 'bg' => '#eff6ff', 'text' => '#1d4ed8', 'border' => '#bfdbfe'],
            'under_review' => ['label' => 'Under Review', 'bg' => '#fffbeb', 'text' => '#b45309', 'border' => '#fde68a'],
            'documents_verified' => ['label' => 'Documents Verified', 'bg' => '#f0fdf4', 'text' => '#166534', 'border' => '#bbf7d0'],
            'interview_scheduled' => ['label' => 'Interview Scheduled', 'bg' => '#f5f3ff', 'text' => '#6d28d9', 'border' => '#ddd6fe'],
            'interview_completed' => ['label' => 'Interview Completed', 'bg' => '#f5f3ff', 'text' => '#7c3aed', 'border' => '#ddd6fe'],
            'payment_pending' => ['label' => 'Payment Pending', 'bg' => '#fff7ed', 'text' => '#c2410c', 'border' => '#fed7aa'],
            'payment_verified' => ['label' => 'Payment Verified', 'bg' => '#ecfeff', 'text' => '#0f766e', 'border' => '#99f6e4'],
            'accepted' => ['label' => 'Accepted', 'bg' => '#ecfdf5', 'text' => '#047857', 'border' => '#a7f3d0'],
            'rejected' => ['label' => 'Rejected', 'bg' => '#fef2f2', 'text' => '#b91c1c', 'border' => '#fecaca'],
            'waitlisted' => ['label' => 'Waitlisted', 'bg' => '#fff7ed', 'text' => '#9a3412', 'border' => '#fed7aa'],
            'enrolled' => ['label' => 'Enrolled', 'bg' => '#ecfdf5', 'text' => '#065f46', 'border' => '#a7f3d0'],
            'withdrawn' => ['label' => 'Withdrawn', 'bg' => '#f8fafc', 'text' => '#334155', 'border' => '#cbd5e1'],
            default => ['label' => (string) str($status)->replace('_', ' ')->title(), 'bg' => '#f8fafc', 'text' => '#334155', 'border' => '#cbd5e1'],
        };
    }

    public function getDocumentTypeLabel(string $type): string
    {
        return Document::DOCUMENT_TYPES[$type] ?? (string) str($type)->replace('_', ' ')->title();
    }

    /**
     * @return array<int, array{status:string,label:string}>
     */
    public function getStatusTimeline(): array
    {
        return [
            ['status' => 'draft', 'label' => 'Draft'],
            ['status' => 'submitted', 'label' => 'Submitted'],
            ['status' => 'under_review', 'label' => 'Under Review'],
            ['status' => 'documents_verified', 'label' => 'Documents Verified'],
            ['status' => 'interview_scheduled', 'label' => 'Interview Scheduled'],
            ['status' => 'interview_completed', 'label' => 'Interview Completed'],
            ['status' => 'payment_pending', 'label' => 'Payment Pending'],
            ['status' => 'payment_verified', 'label' => 'Payment Verified'],
            ['status' => 'accepted', 'label' => 'Accepted'],
            ['status' => 'enrolled', 'label' => 'Enrolled'],
        ];
    }
}
