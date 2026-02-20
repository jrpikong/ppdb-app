<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Payment Model
 *
 * @property int $id
 * @property int $application_id
 * @property int $payment_type_id
 * @property string $transaction_code
 * @property float $amount
 * @property string $currency
 * @property Carbon $payment_date
 * @property string|null $payment_method
 * @property string|null $bank_name
 * @property string|null $account_number
 * @property string|null $reference_number
 * @property string|null $proof_file
 * @property string $status
 * @property string|null $rejection_reason
 * @property string|null $notes
 * @property int|null $verified_by
 * @property Carbon|null $verified_at
 * @property float|null $refund_amount
 * @property Carbon|null $refund_date
 * @property string|null $refund_reason
 */
class Payment extends Model
{
    use SoftDeletes;

    public const STATUS_TRANSITIONS = [
        'pending' => ['submitted'],
        'submitted' => ['verified', 'rejected'],
        'verified' => ['refunded'],
        'rejected' => ['submitted'],
        'refunded' => [],
    ];

    protected $table = 'payments';

    protected $fillable = [
        'application_id',
        'payment_type_id',
        'transaction_code',
        'amount',
        'currency',
        'payment_date',
        'payment_method',
        'bank_name',
        'account_number',
        'reference_number',
        'proof_file',
        'status',
        'rejection_reason',
        'notes',
        'verified_by',
        'verified_at',
        'refund_amount',
        'refund_date',
        'refund_reason',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'payment_date' => 'date',
            'verified_at' => 'datetime',
            'refund_amount' => 'decimal:2',
            'refund_date' => 'date',
        ];
    }

    // ==================== RELATIONSHIPS ====================

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function paymentType(): BelongsTo
    {
        return $this->belongsTo(PaymentType::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    // ==================== SCOPES ====================

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeSubmitted(Builder $query): Builder
    {
        return $query->where('status', 'submitted');
    }

    public function scopeVerified(Builder $query): Builder
    {
        return $query->where('status', 'verified');
    }

    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', 'rejected');
    }

    public function scopeRefunded(Builder $query): Builder
    {
        return $query->where('status', 'refunded');
    }

    // ==================== ACCESSORS ====================

    protected function formattedAmount(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->currency . ' ' . number_format($this->amount, 2, '.', ',')
        );
    }

    protected function proofUrl(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->proof_file && auth()->check()
                ? route('secure-files.payments.proof', ['payment' => $this->id])
                : null
        );
    }

    protected function statusLabel(): Attribute
    {
        return Attribute::make(
            get: fn() => match($this->status) {
                'pending' => 'Pending',
                'submitted' => 'Awaiting Verification',
                'verified' => 'Verified',
                'rejected' => 'Rejected',
                'refunded' => 'Refunded',
                default => ucfirst($this->status),
            }
        );
    }

    protected function statusColor(): Attribute
    {
        return Attribute::make(
            get: fn() => match($this->status) {
                'pending' => 'gray',
                'submitted' => 'yellow',
                'verified' => 'green',
                'rejected' => 'red',
                'refunded' => 'blue',
                default => 'gray',
            }
        );
    }

    // ==================== HELPER METHODS ====================

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }

    public function isVerified(): bool
    {
        return $this->status === 'verified';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isRefunded(): bool
    {
        return $this->status === 'refunded';
    }

    public function canBeVerified(): bool
    {
        return $this->isSubmitted() && $this->proof_file;
    }

    public function canTransitionTo(string $newStatus): bool
    {
        if ($newStatus === $this->status) {
            return true;
        }

        return in_array($newStatus, self::STATUS_TRANSITIONS[$this->status] ?? [], true);
    }

    public function transitionStatus(string $toStatus, array $attributes = [], ?int $actorId = null): bool
    {
        $changed = false;
        $fromStatus = null;

        DB::transaction(function () use ($toStatus, $attributes, $actorId, &$changed, &$fromStatus): void {
            /** @var self $locked */
            $locked = self::query()->whereKey($this->getKey())->lockForUpdate()->first();

            if (! $locked) {
                throw new ModelNotFoundException('Payment not found.');
            }

            $fromStatus = (string) $locked->status;

            if ($fromStatus === $toStatus) {
                $changed = false;
                return;
            }

            if ($actorId !== null) {
                $actor = User::query()->find($actorId);

                if (! $actor) {
                    throw new RuntimeException('Status transition actor not found.');
                }

                Gate::forUser($actor)->authorize('transitionStatus', [$locked, $toStatus]);
            }

            if (! $locked->canTransitionTo($toStatus)) {
                throw new RuntimeException(sprintf(
                    'Invalid payment status transition: %s -> %s',
                    $fromStatus,
                    $toStatus
                ));
            }

            if ($toStatus === 'submitted' && empty($attributes['proof_file']) && empty($locked->proof_file)) {
                throw new RuntimeException('Payment proof is required before submitting payment.');
            }

            $payload = array_merge($attributes, ['status' => $toStatus]);

            if ($toStatus === 'submitted') {
                $payload['rejection_reason'] = null;
                $payload['verified_at'] = null;
                $payload['verified_by'] = null;
            }

            if (in_array($toStatus, ['verified', 'rejected'], true)) {
                $payload['verified_at'] = now();
            }

            $locked->fill($payload);
            $locked->save();

            $this->forceFill($locked->fresh()->getAttributes())->syncOriginal();
            $changed = true;
        }, 3);

        if ($changed) {
            ActivityLog::logActivity(
                description: sprintf('Payment status changed: %s -> %s', (string) $fromStatus, $toStatus),
                subject: $this,
                logName: 'payment',
                event: 'status_changed',
                properties: [
                    'from_status' => $fromStatus,
                    'to_status' => $toStatus,
                    'attributes' => $attributes,
                ],
                userId: $actorId
            );
        }

        return $changed;
    }

    public function submitProof(array $attributes, ?int $actorId = null): bool
    {
        return $this->transitionStatus('submitted', $attributes, $actorId);
    }

    public function verify(int $userId, ?string $notes = null): bool
    {
        return $this->transitionStatus('verified', [
            'notes' => $notes ?? $this->notes,
            'verified_by' => $userId,
            'rejection_reason' => null,
        ], $userId);
    }

    public function reject(int $userId, string $reason): bool
    {
        return $this->transitionStatus('rejected', [
            'verified_by' => $userId,
            'rejection_reason' => $reason,
        ], $userId);
    }

    public function refund(float $amount, string $reason): bool
    {
        return $this->transitionStatus('refunded', [
            'refund_amount' => $amount,
            'refund_date' => now(),
            'refund_reason' => $reason,
        ], auth()->id());
    }

    // ==================== BOOT METHOD ====================

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Payment $payment) {
            if (empty($payment->transaction_code)) {
                $payment->transaction_code = $payment->generateTransactionCode();
            }
        });

        static::deleting(function (Payment $payment) {
            if (! $payment->proof_file) {
                return;
            }

            if (Storage::disk('local')->exists($payment->proof_file)) {
                Storage::disk('local')->delete($payment->proof_file);
            } elseif (Storage::disk('public')->exists($payment->proof_file)) {
                Storage::disk('public')->delete($payment->proof_file);
            }
        });
    }

    public function generateTransactionCode(): string
    {
        $date = now()->format('Ymd');
        $school = $this->application?->school ?? School::find($this->application?->school_id);
        $schoolCode = $school?->code ?? 'VIS';

        $lastPayment = static::whereDate('created_at', today())->latest('id')->first();
        $sequence = $lastPayment ? ((int) substr($lastPayment->transaction_code, -4)) + 1 : 1;

        return "{$schoolCode}-PAY-{$date}-" . str_pad((string)$sequence, 4, '0', STR_PAD_LEFT);
    }
}
