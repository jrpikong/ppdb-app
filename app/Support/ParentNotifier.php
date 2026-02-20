<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Application;
use App\Models\Document;
use App\Models\Payment;
use App\Models\Schedule;
use App\Notifications\ParentInAppNotification;

class ParentNotifier
{
    public static function applicationStatusChanged(
        Application $application,
        string $fromStatus,
        string $toStatus,
        ?string $notes = null
    ): void {
        $parent = $application->user;

        if (! $parent) {
            return;
        }

        $title = 'Application Status Updated';
        $body = sprintf(
            'Application %s berubah dari %s ke %s.%s',
            $application->application_number,
            Application::statusLabelFor($fromStatus),
            Application::statusLabelFor($toStatus),
            $notes ? ' Catatan: '.trim($notes) : ''
        );

        $parent->notify(new ParentInAppNotification(
            title: $title,
            body: $body,
            url: route('filament.my.resources.applications.view', ['record' => $application->id]),
            level: 'info',
        ));
    }

    public static function paymentStatusChanged(Payment $payment, string $event, ?string $notes = null): void
    {
        $payment->loadMissing('application.user');
        $application = $payment->application;
        $parent = $application?->user;

        if (! $parent || ! $application) {
            return;
        }

        $title = match ($event) {
            'verified' => 'Payment Verified',
            'rejected' => 'Payment Rejected',
            'refunded' => 'Payment Refunded',
            default => 'Payment Updated',
        };

        $body = sprintf(
            'Pembayaran %s untuk application %s sekarang berstatus %s.%s',
            $payment->transaction_code,
            $application->application_number,
            $payment->status_label,
            $notes ? ' Catatan: '.trim($notes) : ''
        );

        $parent->notify(new ParentInAppNotification(
            title: $title,
            body: $body,
            url: route('filament.my.resources.payments.view', ['record' => $payment->id]),
            level: $event === 'rejected' ? 'danger' : 'success',
        ));
    }

    public static function documentVerificationChanged(Document $document, string $event, ?string $notes = null): void
    {
        $document->loadMissing('application.user');
        $application = $document->application;
        $parent = $application?->user;

        if (! $parent || ! $application) {
            return;
        }

        $title = match ($event) {
            'verified' => 'Document Verified',
            'rejected' => 'Document Rejected',
            default => 'Document Updated',
        };

        $body = sprintf(
            'Your document %s for application %s has been %s.%s',
            $document->type_label,
            $application->application_number,
            $event,
            $notes ? ' Notes: ' . trim($notes) : ''
        );

        $parent->notify(new ParentInAppNotification(
            title: $title,
            body: $body,
            url: route('filament.my.resources.applications.view', ['record' => $application->id]),
            level: $event === 'verified' ? 'success' : 'danger',
        ));
    }

    public static function scheduleUpdated(Schedule $schedule, string $event, ?string $details = null): void
    {
        $schedule->loadMissing('application.user');
        $application = $schedule->application;
        $parent = $application?->user;

        if (! $parent || ! $application) {
            return;
        }

        $title = match ($event) {
            'created' => 'New Schedule',
            'updated' => 'Schedule Updated',
            'completed' => 'Schedule Completed',
            'cancelled' => 'Schedule Cancelled',
            default => 'Schedule Update',
        };

        $body = sprintf(
            'Jadwal %s untuk application %s pada %s %s. Status: %s.%s',
            strtolower($schedule->type_label),
            $application->application_number,
            $schedule->scheduled_date?->format('d M Y'),
            substr((string) $schedule->scheduled_time, 0, 5),
            $schedule->status_label,
            $details ? ' '.$details : ''
        );

        $parent->notify(new ParentInAppNotification(
            title: $title,
            body: $body,
            url: route('filament.my.resources.schedules.view', ['record' => $schedule->id]),
            level: $event === 'cancelled' ? 'danger' : 'info',
        ));
    }
}

