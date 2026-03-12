<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Application;
use App\Models\Payment;
use App\Models\PaymentType;
use App\Notifications\ParentInAppNotification;

class PaymentService
{
    /**
     * Create pre-submission payment (Saving Seat) for an application.
     */
    public function createPreSubmissionPayment(Application $application): ?Payment
    {
        $paymentType = PaymentType::query()
            ->where('school_id', $application->school_id)
            ->where('payment_stage', 'pre_submission')
            ->where('is_active', true)
            ->first();

        if (! $paymentType) {
            return null;
        }

        // Check if payment already exists
        $existing = $application->payments()
            ->where('payment_type_id', $paymentType->id)
            ->first();

        if ($existing) {
            return $existing;
        }

        return Payment::create([
            'application_id' => $application->id,
            'payment_type_id' => $paymentType->id,
            'transaction_code' => $this->generateTransactionCode($application),
            'amount' => $paymentType->amount,
            'currency' => 'IDR',
            'status' => 'pending',
        ]);
    }

    /**
     * Create post-acceptance payments (Registration + Development).
     *
     * @return Payment[]
     */
    public function createPostAcceptancePayments(Application $application): array
    {
        $paymentTypes = PaymentType::query()
            ->where('school_id', $application->school_id)
            ->where('payment_stage', 'post_acceptance')
            ->where('is_active', true)
            ->get();

        $payments = [];

        foreach ($paymentTypes as $paymentType) {
            // Check if payment already exists
            $existing = $application->payments()
                ->where('payment_type_id', $paymentType->id)
                ->first();

            if ($existing) {
                $payments[] = $existing;
                continue;
            }

            $payments[] = Payment::create([
                'application_id' => $application->id,
                'payment_type_id' => $paymentType->id,
                'transaction_code' => $this->generateTransactionCode($application),
                'amount' => $paymentType->amount,
                'currency' => 'IDR',
                'status' => 'pending',
            ]);
        }

        return $payments;
    }

    /**
     * Create enrollment payments (Uniform + Books + Technology).
     *
     * @return Payment[]
     */
    public function createEnrollmentPayments(Application $application): array
    {
        $paymentTypes = PaymentType::query()
            ->where('school_id', $application->school_id)
            ->where('payment_stage', 'enrollment')
            ->where('is_active', true)
            ->get();

        $payments = [];

        foreach ($paymentTypes as $paymentType) {
            // Check if payment already exists
            $existing = $application->payments()
                ->where('payment_type_id', $paymentType->id)
                ->first();

            if ($existing) {
                $payments[] = $existing;
                continue;
            }

            $payments[] = Payment::create([
                'application_id' => $application->id,
                'payment_type_id' => $paymentType->id,
                'transaction_code' => $this->generateTransactionCode($application),
                'amount' => $paymentType->amount,
                'currency' => 'IDR',
                'status' => 'pending',
            ]);
        }

        return $payments;
    }

    /**
     * Notify parent about new payment invoice.
     */
    public function notifyParentAboutPayment(Payment $payment): void
    {
        $payment->loadMissing(['application.user', 'paymentType']);
        $application = $payment->application;
        $parent = $application?->user;
        $paymentType = $payment->paymentType;

        if (! $parent || ! $paymentType) {
            return;
        }

        $formattedAmount = 'Rp ' . number_format($payment->amount, 0, ',', '.');

        $parent->notify(new ParentInAppNotification(
            title: 'Invoice Pembayaran Baru',
            body: "Invoice {$paymentType->name} sebesar {$formattedAmount} telah dibuat untuk aplikasi {$application->application_number}. Silakan lakukan pembayaran dan upload bukti bayar.",
            url: route('filament.my.resources.payments.view', ['record' => $payment->id]),
            level: 'info',
        ));
    }

    /**
     * Generate unique transaction code for payment.
     */
    private function generateTransactionCode(Application $application): string
    {
        $date = now()->format('Ymd');
        $sequence = $application->payments()->withTrashed()->count() + 1;

        return sprintf(
            '%s-PAY-%s-%04d-%02d',
            $application->school->code,
            $date,
            $application->id,
            $sequence
        );
    }
}
